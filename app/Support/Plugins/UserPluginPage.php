<?php

namespace App\Support\Plugins;

use App\Support\Admin\PluginPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders a plugin's prebuilt React page in the React customer panel (ModernDashboard),
 * the customer counterpart of App\Support\Admin\PluginPage.
 *
 *   plugins/<Name>/resources/js/user/<page>.tsx  →  plugins/<Name>/dist/user/<page>.js
 *   (npm run plugin:build -- <Name>)
 *
 * The page wraps itself in AppLayout from "@gobiz/user". The Inertia component name
 * carries what the browser needs to load it (see loadPluginPage in ModernDashboard's app.tsx):
 * "plugin+public:<Name>@<version>-<build time>/<page>" → /admin-assets/plugins/<Name>/user/<page>.js
 * "plugin+nocopy:…"                                   → /user/plugin-assets/<Name>/user/<page>.js
 *
 * Only for the React panel: a plugin keeps Blade views for the Blade panel and picks
 * one with UserPluginPage::reactPanel().
 */
class UserPluginPage
{
    /** Version of the "@gobiz/user" contract; keep equal to USER_PLUGIN_API in plugin-runtime.ts. */
    public const API = '1';

    /** Whether customers use the React panel (ModernDashboard switched on). */
    public static function reactPanel(): bool
    {
        if (! plugin_active('ModernDashboard')) {
            return false;
        }

        try {
            return (int) DB::table('modern_dashboard_settings')->value('modern_dashboard_enabled') === 1;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function render(string $plugin, string $page, array $props = []): Response
    {
        $dir      = base_path('plugins/' . $plugin);
        $info     = json_decode((string) @file_get_contents($dir . '/plugin.json'), true) ?: [];
        $built    = json_decode((string) @file_get_contents($dir . '/dist/user/manifest.json'), true) ?: [];
        $requires = (string) ($built['api'] ?? '1');

        abort_unless(isset($built['pages'][$page]) && File::exists($dir . '/dist/user/' . $built['pages'][$page]), 404, __('This page is missing. Ask the site owner to reinstall the plugin.'));
        abort_if(version_compare($requires, self::API, '>'), 503, __('This page needs a newer version of the dashboard.'));

        // Cache key: version + build time, so every rebuild loads fresh files
        $version = preg_replace('/[^0-9A-Za-z.\-]/', '', (string) ($info['version'] ?? '0')) . '-' . filemtime($dir . '/dist/user/manifest.json');
        $source  = File::exists($dir . '/dist/manifest.json') && PluginPage::publish('plugin', $plugin, $dir . '/dist') ? '+public' : '+nocopy';

        return Inertia::render("plugin{$source}:{$plugin}@{$version}/{$page}", $props);
    }
}
