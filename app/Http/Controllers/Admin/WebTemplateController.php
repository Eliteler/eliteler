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
use App\Services\TemplateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use ZipArchive;

/**
 * Website front-end templates (each folder in /templates with a template.json).
 * The active one is config "web_template". Each template ships its own admin
 * pages (template.json "main_route" for pages, "config_route" for settings).
 * Old Blade-admin versions ("legacy") are never installed from a zip; one
 * already on disk keeps serving the website, but its admin pages ask for
 * the new version and it can't be activated.
 */
class WebTemplateController extends Controller
{
    /** Shipped with GoBiz; always kept as a fallback. */
    private const BUILT_IN = 'GoBizOriginal';

    public function __construct(private TemplateManager $templateManager)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $active = $this->activeId();

        $templates = collect($this->templates())->map(function ($template) use ($active) {
            $id = (string) ($template['template_id'] ?? basename($template['path']));

            return [
                'id'          => $id,
                'name'        => __($template['name'] ?? $id),
                'version'     => $template['version'] ?? null,
                'image'       => ! empty($template['template_image']) ? asset($template['template_image']) : null,
                'active'      => $id === $active,
                'builtIn'     => $id === self::BUILT_IN,
                'pagesUrl'    => $template['legacy']
                    ? route('admin.web-template.update-required', $id)
                    : $this->routeUrl($template['main_route'] ?? null),
                'settingsUrl' => $template['legacy'] ? null : $this->routeUrl($template['config_route'] ?? null),
                'legacy'      => $template['legacy'],
            ];
        })->sortByDesc('active')->values();

        return Inertia::render('website/templates', [
            'templates'   => $templates,
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    public function activateTemplate($template_id)
    {
        $template = $this->find((string) $template_id);
        if (! $template) {
            return back()->with('failed', __('Template not found.'));
        }
        if ($template['legacy']) {
            return back()->with('failed', __(':name is an older version that doesn’t work with this version of GoBiz. Upload its latest version first.', ['name' => __($template['name'] ?? $template['template_id'])]));
        }

        DB::table('config')->where('config_key', 'web_template')->update(['config_value' => $template['template_id']]);

        return back()->with('success', __(':name is now your website template.', ['name' => __($template['name'] ?? $template['template_id'])]));
    }

    /**
     * Admin pages of a legacy template: explains that a new version is needed.
     * Also served at the template's old admin URLs (see routes/web.php).
     */
    public function updateRequired(Request $request, ?string $id = null)
    {
        // An old admin URL carries the template as a route default; its own {id} is a page id
        $template = $this->find((string) ($request->route('template') ?? $id));

        abort_if(! $template, 404);

        // Already updated: go to the real pages screen
        if (! $template['legacy'] && ($url = $this->routeUrl($template['main_route'] ?? null))) {
            return redirect($url);
        }

        $name = __($template['name'] ?? $template['template_id']);

        return Inertia::render('plugins/unavailable', [
            'name'    => $name,
            'title'   => __('A new version is available'),
            'message' => __(':name :version is an older version, so its pages and settings can’t be edited here. Download the latest version of this template and upload it on the Website templates page.', [
                'name'    => $name,
                'version' => $template['version'] ?? '',
            ]),
            'update'  => true,
            'section' => 'templates',
        ]);
    }

    public function deleteTemplate($templateName)
    {
        $template = $this->find((string) $templateName);

        if (! $template) {
            return back()->with('failed', __('Template not found.'));
        }
        if ($template['template_id'] === $this->activeId()) {
            return back()->with('failed', __('This template is in use. Activate another template first.'));
        }
        if ($template['template_id'] === self::BUILT_IN) {
            return back()->with('failed', __('The built-in template can’t be removed.'));
        }

        // The manager only knows templates after loading them
        $this->templateManager->loadTemplates();
        $this->templateManager->deleteTemplate($template['template_id']);

        return back()->with('success', __(':name removed.', ['name' => __($template['name'] ?? $template['template_id'])]));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:' . env('SIZE_LIMIT', 2048),
        ], [
            'zip_file.max' => __('The file must be smaller than :limit KB. You can raise the upload limit in Settings.', ['limit' => env('SIZE_LIMIT', 2048)]),
        ], [
            'zip_file' => __('template file'),
        ]);

        File::ensureDirectoryExists(storage_path('app/templates'));
        $zipPath = storage_path('app/templates/' . uniqid() . '.zip');
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

            // The zip's file and folder names don't matter: the template is the folder holding
            // the shallowest template.json, with routes.php, Views/ and Controllers/
            $manifestPath = PackageZip::manifestPath($zip, 'template.json');
            $root         = $manifestPath === null ? '' : substr($manifestPath, 0, -strlen(basename($manifestPath)));

            $found = [];
            foreach ($manifestPath === null ? [] : PackageZip::files($zip, $root) as $name) {
                foreach ([
                    'views'      => '#^Views/#i',
                    'controller' => '#^Controllers/#i',
                    'routes'     => '#^routes\.php$#i',
                ] as $part => $pattern) {
                    if (preg_match($pattern, $name)) {
                        $found[$part] = true;
                    }
                }
            }

            if (count($found) < 3) {
                $zip->close();

                return back()->with('failed', __('Installation failed. This doesn’t look like a GoBiz template; some files are missing.'));
            }

            $templateJson = json_decode((string) $zip->getFromName($manifestPath), true);

            if (! is_array($templateJson)) {
                $zip->close();

                return back()->with('failed', __('The template.json file contains invalid JSON.'));
            }

            // Never install an old Blade-admin version, or an older version over a newer one
            if ($notice = $this->versionNotice($zip, $manifestPath, $templateJson)) {
                $zip->close();

                return back()->with('template_notice', $notice);
            }

            // Access the JSON keys
            $platform    = $templateJson['platform'] ?? null;
            $min_version = $templateJson['min_version'] ?? null;

            $version = GoBizCommonService::singleConfig('app_version');

            // check platform
            if ($platform !== 'gobiz-saas') {
                $zip->close();

                return back()->with('failed', __('This template is not compatible with your platform.'));
            }

            // check min_version
            if (version_compare($version, $min_version, '<')) {
                $zip->close();

                return back()->with('failed', __('This template requires GoBiz version :min_version.', ['min_version' => $min_version]));
            }

            // Installed under the folder its namespace expects (Templates\<Folder>\...)
            $folder = PackageZip::folder($zip, $root, 'Templates', $templateJson['template_id'] ?? null);

            if ($folder === null) {
                $zip->close();

                return back()->with('failed', __('Installation failed. This doesn’t look like a GoBiz template; some files are missing.'));
            }

            PackageZip::extract($zip, $root, base_path('templates/' . $folder));
            $zip->close();
        } finally {
            File::delete($zipPath);
        }

        return back()->with('success', __('Template installed.'));
    }

    /**
     * Why this zip shouldn't be installed, read from its template.json: an old
     * Blade-admin version, or older than the installed one. Null when fine.
     *
     * @return array{kind: string, name: string, version: string, installed?: string}|null
     */
    private function versionNotice(ZipArchive $zip, string $manifestPath, array $manifest): ?array
    {
        $dir     = substr($manifestPath, 0, -strlen(basename($manifestPath)));
        $name    = __($manifest['name'] ?? $manifest['template_id'] ?? __('This template'));
        $version = (string) ($manifest['version'] ?? '');

        if (TemplateManager::isLegacy($manifest, null, $zip->locateName($dir . 'dist/manifest.json') !== false)) {
            return ['kind' => 'legacy', 'name' => $name, 'version' => $version];
        }

        $installed = $this->find((string) ($manifest['template_id'] ?? ''));

        if ($installed && ! $installed['legacy'] && $version !== '' && version_compare($version, (string) ($installed['version'] ?? '0'), '<')) {
            return ['kind' => 'older', 'name' => $name, 'version' => $version, 'installed' => (string) $installed['version']];
        }

        return null;
    }

    private function templates(): array
    {
        $this->templateManager->loadTemplates();

        return $this->templateManager->getTemplates();
    }

    private function find(string $id): ?array
    {
        return collect($this->templates())->first(fn($template) => ($template['template_id'] ?? null) === $id);
    }

    private function activeId(): string
    {
        return (string) DB::table('config')->where('config_key', 'web_template')->value('config_value');
    }

    private function routeUrl(?string $name): ?string
    {
        return $name && Route::has($name) ? route($name) : null;
    }
}
