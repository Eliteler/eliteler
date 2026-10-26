<?php

namespace App\Support\Admin;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders a plugin's or web template's prebuilt React admin page (<plugins|templates>/<Name>/dist,
 * built with `npm run plugin:build -- <Name>`) inside the core admin layout.
 *
 * The Inertia component name carries everything the browser needs to load it:
 * "plugin:<Name>@<version>-<build time>/<page>"   → /admin/plugin-assets/<Name>/<page>.js?v=<that key>
 * "template:<Name>@<version>-<build time>/<page>" → /admin/template-assets/<Name>/<page>.js?v=<that key>
 */
class PluginPage
{
    public static function render(string $plugin, string $page, array $props = []): Response
    {
        return self::renderFrom('plugin', $plugin, $page, $props);
    }

    /** A web template's page (templates/<Name>, manifest template.json). */
    public static function template(string $template, string $page, array $props = []): Response
    {
        return self::renderFrom('template', $template, $page, $props);
    }

    private static function renderFrom(string $kind, string $name, string $page, array $props): Response
    {
        $dir      = base_path(($kind === 'template' ? 'templates/' : 'plugins/') . $name);
        $info     = json_decode((string) @file_get_contents("{$dir}/{$kind}.json"), true) ?: [];
        $built    = json_decode((string) @file_get_contents($dir . '/dist/manifest.json'), true) ?: [];
        $requires = (string) ($info['requires_admin'] ?? '1');
        $label    = $info['name'] ?? $name;

        if (! isset($built['pages'][$page]) || ! File::exists($dir . '/dist/' . $built['pages'][$page])) {
            return self::problem($kind, $label, $kind === 'template'
                ? __('This template\'s admin page is missing. Reinstall the template.')
                : __('This plugin\'s settings page is missing. Reinstall the plugin.'));
        }

        if (version_compare($requires, AdminSharedProps::PLUGIN_API, '>')) {
            return self::problem($kind, $label, $kind === 'template'
                ? __('This template needs a newer version of GoBiz. Update GoBiz from Software updates.')
                : __('This plugin needs a newer version of GoBiz. Update GoBiz from Software updates.'));
        }

        // Cache key: version + build time, so every rebuild loads fresh files
        // even when the version wasn't bumped (assets are cached for a year)
        $version = preg_replace('/[^0-9A-Za-z.\-]/', '', (string) ($info['version'] ?? '0')) . '-' . filemtime($dir . '/dist/manifest.json');

        // plugin.json / template.json "documentation_url": PageHeader shows a Documentation button (http(s) links only)
        $docs = $info['documentation_url'] ?? null;

        $props['documentationUrl'] = is_string($docs) && preg_match('#^https?://#i', $docs) ? $docs : null;

        return Inertia::render("{$kind}:{$name}@{$version}/{$page}", $props);
    }

    private static function problem(string $kind, string $name, string $message): Response
    {
        return Inertia::render('plugins/unavailable', [
            'name'    => __($name),
            'message' => $message,
            'section' => $kind === 'template' ? 'templates' : 'plugins',
        ]);
    }
}
