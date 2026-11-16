<?php

namespace App\Support\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders a plugin's or web template's prebuilt React admin page (<plugins|templates>/<Name>/dist,
 * built with `npm run plugin:build -- <Name>`) inside the core admin layout.
 *
 * dist is copied to public/admin-assets/<plugins|templates>/<Name> when the page is opened, so any
 * web server serves it as plain static files (nginx static-file rules never reach Laravel for *.js).
 *
 * The Inertia component name carries everything the browser needs to load it:
 * "plugin+public:<Name>@<version>-<build time>/<page>" → /admin-assets/plugins/<Name>/<page>.js?v=<that key>
 * "plugin+nocopy:<Name>@<version>-<build time>/<page>" → /admin/plugin-assets/<Name>/<page>.js?v=<that key>
 *                                                        (the copy failed; the page says why if this doesn't load)
 * "template…" works the same with templates/ and /admin/template-assets.
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

        $source = self::publish($kind, $name, $dir . '/dist') ? '+public' : '+nocopy';

        return Inertia::render("{$kind}{$source}:{$name}@{$version}/{$page}", $props);
    }

    /** Public copy of a plugin's or template's dist folder (see the class comment). */
    public static function publicPath(string $kind, string $name): string
    {
        return public_path('admin-assets/' . ($kind === 'template' ? 'templates' : 'plugins') . '/' . $name);
    }

    /**
     * Copies dist to the public folder when it's missing or from another build.
     * Copies to a temp folder first, so a half-copied build is never served.
     * Also used for customer pages and widgets (App\Support\Plugins\PluginExtensions).
     */
    public static function publish(string $kind, string $name, string $dist): bool
    {
        $target = self::publicPath($kind, $name);

        if (@file_get_contents($target . '/manifest.json') === @file_get_contents($dist . '/manifest.json')
            && @filemtime($target . '/manifest.json') >= filemtime($dist . '/manifest.json')) {
            return true;
        }

        $temp = $target . '.tmp-' . uniqid();

        try {
            File::ensureDirectoryExists(dirname($target));

            if (! File::copyDirectory($dist, $temp)) {
                throw new \RuntimeException("Couldn't copy {$dist} to {$temp}");
            }

            File::deleteDirectory($target);

            if (! @rename($temp, $target)) {
                throw new \RuntimeException("Couldn't move {$temp} to {$target}");
            }

            return true;
        } catch (\Throwable $e) {
            File::deleteDirectory($temp);
            Log::warning("Admin page files of {$kind} {$name} couldn't be published: " . $e->getMessage());

            return false;
        }
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
