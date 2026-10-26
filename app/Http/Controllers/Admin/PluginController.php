<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use App\Services\PackageZip;
use App\Services\PluginManager;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use ZipArchive;

/**
 * Installed plugins (each folder in /plugins with a plugin.json).
 * Settings pages belong to the plugins themselves (plugin.json "main_route").
 * Built-in plugins ship with GoBiz and can't be removed here.
 * Old Blade-admin versions ("legacy") are never installed from a zip; one
 * already on disk keeps running, but its settings ask for the new version.
 */
class PluginController extends Controller
{
    private const BUILT_IN = [
        'TawkChat', 'WhatsappChatButton', 'WhatsAppChatButton', 'GoogleRecaptcha', 'GoogleOAuth', 'GoogleAnalytics',
        'GoogleAdSense', 'MaintenanceMode', 'SMTP', 'SubDomain', 'OrderNFCSystem', 'ReferralSystem', 'CookieConsent',
        'WebTools', 'AiBuilder',
    ];

    public function __construct(private PluginManager $pluginManager)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->pluginManager->loadPlugins();

        $plugins = collect($this->pluginManager->getPlugins())->map(function ($plugin) {
            $image = ! empty($plugin['img']) && File::exists(public_path('img/plugins/' . $plugin['img'])) ? $plugin['img'] : 'default.png';
            $route = $plugin['main_route'] ?? null;
            $id    = (string) ($plugin['plugin_id'] ?? basename($plugin['path']));

            return [
                'id'          => $id,
                'name'        => __($plugin['name'] ?? basename($plugin['path'])),
                'description' => isset($plugin['description']) ? __($plugin['description']) : null,
                'version'     => $plugin['version'] ?? null,
                'image'       => asset('img/plugins/' . $image),
                'settingsUrl' => $plugin['legacy']
                    ? route('admin.plugin.update-required', $id)
                    : ($route && Route::has($route) ? route($route) : null),
                'builtIn'     => in_array($plugin['plugin_id'] ?? '', self::BUILT_IN, true),
                'legacy'      => $plugin['legacy'],
            ];
        })->sortBy(fn($plugin) => mb_strtolower($plugin['name']))->values();

        return Inertia::render('plugins/index', [
            'plugins'     => $plugins,
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    /**
     * Settings of a legacy plugin: explains that a new version is needed.
     * Also served at the plugin's old admin URLs (see routes/web.php).
     */
    public function updateRequired(Request $request, ?string $id = null)
    {
        $id = $id ?? (string) $request->route('plugin');

        $this->pluginManager->loadPlugins();
        $plugin = collect($this->pluginManager->getPlugins())->firstWhere('plugin_id', $id);

        abort_if(! $plugin, 404);

        // Already updated: go to the real settings page
        if (! $plugin['legacy'] && ! empty($plugin['main_route']) && Route::has($plugin['main_route'])) {
            return redirect()->route($plugin['main_route']);
        }

        return Inertia::render('plugins/unavailable', [
            'name'    => __($plugin['name'] ?? $id),
            'title'   => __('A new version is available'),
            'message' => __(':name :version is an older version, so its settings can’t be edited here. Download the latest version of this plugin and upload it on the Plugins page.', [
                'name'    => __($plugin['name'] ?? $id),
                'version' => $plugin['version'] ?? '',
            ]),
            'update'  => true,
        ]);
    }

    public function deletePlugin($pluginName)
    {
        if (in_array($pluginName, self::BUILT_IN, true)) {
            return back()->with('failed', __('Built-in plugins can’t be removed.'));
        }

        if (Schema::hasTable('recurring_payment_gateways')) {
            $gateway = DB::table('recurring_payment_gateways')->where('plugin_id', $pluginName)->first();

            if ($gateway && Transaction::where('payment_gateway_name', $gateway->payment_gateway_name)->where('payment_status', 'SUCCESS')->exists()) {
                return back()->with('failed', __('This plugin can’t be removed because customers have active payments through it.'));
            }
        }

        // The manager only knows plugins after loading them
        $this->pluginManager->loadPlugins();

        return $this->pluginManager->deletePlugin($pluginName)
            ? back()->with('success', __('Plugin removed.'))
            : back()->with('failed', __('Plugin not found or could not be removed.'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:' . env('SIZE_LIMIT', 2048),
        ], [
            'zip_file.max' => __('The file must be smaller than :limit KB. You can raise the upload limit in Settings.', ['limit' => env('SIZE_LIMIT', 2048)]),
        ], [
            'zip_file' => __('plugin file'),
        ]);

        File::ensureDirectoryExists(storage_path('app/plugins'));
        $zipPath = storage_path('app/plugins/' . uniqid() . '.zip');
        File::put($zipPath, $request->file('zip_file')->get());

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                return back()->with('failed', __('Installation failed. The file is damaged.'));
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (str_contains($name, '..') || str_starts_with($name, '/')) {
                    $zip->close();

                    return back()->with('failed', __('Installation failed. The file contains unsafe paths.'));
                }
            }

            // The zip's file and folder names don't matter: the plugin is the folder holding
            // the shallowest plugin.json, with routes.php, Controllers/ and an admin page:
            // Blade views (Views/) or a prebuilt React page (dist/manifest.json)
            $manifestPath = PackageZip::manifestPath($zip, 'plugin.json');
            $root         = $manifestPath === null ? '' : substr($manifestPath, 0, -strlen(basename($manifestPath)));

            $found = [];
            foreach ($manifestPath === null ? [] : PackageZip::files($zip, $root) as $name) {
                foreach ([
                    'routes'     => '#^routes\.php$#i',
                    'controller' => '#^Controllers/#i',
                    'adminPage'  => '#^(Views/.+\.blade\.php|dist/manifest\.json)$#i',
                ] as $part => $pattern) {
                    if (preg_match($pattern, $name)) {
                        $found[$part] = true;
                    }
                }
            }

            if (count($found) < 3) {
                $zip->close();

                return back()->with('failed', __('Installation failed. This doesn’t look like a GoBiz plugin; some files are missing.'));
            }

            $pluginJson = json_decode((string) $zip->getFromName($manifestPath), true);

            if (! is_array($pluginJson)) {
                $zip->close();

                return back()->with('failed', __('The plugin.json file contains invalid JSON.'));
            }

            // Never install an old Blade-admin version, or an older version over a newer one
            if ($notice = $this->versionNotice($zip, $manifestPath, $pluginJson)) {
                $zip->close();

                return back()->with('plugin_notice', $notice);
            }

            // Access the JSON keys
            $platform    = $pluginJson['platform'] ?? null;
            $min_version = $pluginJson['min_version'] ?? null;

            $version = GoBizCommonService::singleConfig('app_version');

            // check platform
            if ($platform !== 'gobiz-saas') {
                $zip->close();

                return back()->with('failed', __('This plugin is not compatible with your platform.'));
            }

            // check min_version
            if (version_compare($version, $min_version, '<')) {
                $zip->close();

                return back()->with('failed', __('This plugin requires GoBiz version :min_version.', ['min_version' => $min_version]));
            }

            // Installed under the folder its namespace expects (Plugins\<Folder>\...)
            $folder = PackageZip::folder($zip, $root, 'Plugins', $pluginJson['plugin_id'] ?? null);

            if ($folder === null) {
                $zip->close();

                return back()->with('failed', __('Installation failed. This doesn’t look like a GoBiz plugin; some files are missing.'));
            }

            PackageZip::extract($zip, $root, base_path('plugins/' . $folder));
            $zip->close();
        } finally {
            File::delete($zipPath);
        }

        return back()->with('success', __('Plugin installed.'));
    }

    /**
     * Why this zip shouldn't be installed, read from its plugin.json: an old
     * Blade-admin version, or older than the installed one. Null when fine.
     *
     * @return array{kind: string, name: string, version: string, installed?: string}|null
     */
    private function versionNotice(ZipArchive $zip, string $manifestPath, array $manifest): ?array
    {
        $dir     = substr($manifestPath, 0, -strlen(basename($manifestPath)));
        $name    = __($manifest['name'] ?? $manifest['plugin_id'] ?? __('This plugin'));
        $version = (string) ($manifest['version'] ?? '');

        if (PluginManager::isLegacy($manifest, null, $zip->locateName($dir . 'dist/manifest.json') !== false)) {
            return ['kind' => 'legacy', 'name' => $name, 'version' => $version];
        }

        $this->pluginManager->loadPlugins();
        $installed = collect($this->pluginManager->getPlugins())->firstWhere('plugin_id', $manifest['plugin_id'] ?? null);

        if ($installed && ! $installed['legacy'] && $version !== '' && version_compare($version, (string) ($installed['version'] ?? '0'), '<')) {
            return ['kind' => 'older', 'name' => $name, 'version' => $version, 'installed' => (string) $installed['version']];
        }

        return null;
    }
}
