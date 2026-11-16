<?php

namespace App\Support\Plugins;

use App\Services\PluginManager;
use App\Support\Admin\AdminPermissions;
use App\Support\Admin\PluginPage;
use App\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * What installed plugins add to the panels, declared in their plugin.json:
 *
 *   "sidebar":     { "admin": { "label", "route", "icon", "permission", "badge" },
 *                    "user":  { "label", "route", "icon", "badge" } }
 *   "header":      { "user": { "label", "route", "icon", "badge", "enabled" } }
 *   "widgets":     { "user": [ { "name", "enabled" } ] }      → dist/widgets/<name>.js (+ .css)
 *   "permissions": { "<key>": "<label>" }                      → staff permissions, "Plugins" group
 *   "email_shortcodes": { "<group>": { ":code": "<sample>" } } → placeholders listed in Email Templates
 *
 * "icon" is an SVG file in the plugin (24×24, stroke="currentColor"). "badge" (a number,
 * shown when above 0) and "enabled" (true/false) are "Class::method" strings, called
 * without arguments for the signed-in user. A plugin that throws is reported and left
 * out, so it never breaks a panel.
 *
 * Sidebar entries always show while the plugin is installed. Customer entries work in
 * both customer panels: the Blade one (user.includes.sidebar, header, layout) and the React one (ModernDashboard, shared props from HandleInertiaRequests).
 */
class PluginExtensions
{
    private const ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3a1 1 0 0 0 1 -1v-1a2 2 0 0 1 4 0v1a1 1 0 0 0 1 1h3a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h1a2 2 0 0 1 0 4h-1a1 1 0 0 0 -1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-1a2 2 0 0 0 -4 0v1a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h1a2 2 0 0 0 0 -4h-1a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1"/></svg>';

    /** Request attribute holding this request's results (never static: Octane and queues reuse the process). */
    private const MEMO = 'plugin_extensions';

    /** @return array<int, array> installed plugins (plugin.json data with "path") */
    public static function plugins(): array
    {
        return app(PluginManager::class)->getPlugins();
    }

    /**
     * Sidebar entries for a panel ("admin" or "user"), in plugin name order.
     *
     * @return array<int, array{plugin: string, title: string, route: string, href: string, svg: string, badge: int, inertia: bool, isActive: bool}>
     */
    public static function sidebarItems(string $area, ?User $user = null): array
    {
        return self::memo("sidebar.$area." . ($user?->id ?? 0), fn () => collect(self::plugins())
            ->filter(fn ($plugin) => isset($plugin['sidebar'][$area]))
            ->map(fn ($plugin) => self::link($plugin, $plugin['sidebar'][$area], $area === 'admin' ? $user : null))
            ->filter()
            ->sortBy('title')
            ->values()
            ->all());
    }

    /** @return array<int, array> header icons of the customer panels */
    public static function headerItems(): array
    {
        return self::memo('header', fn () => collect(self::plugins())
            ->filter(fn ($plugin) => isset($plugin['header']['user']))
            ->map(fn ($plugin) => self::link($plugin, $plugin['header']['user']))
            ->filter()
            ->sortBy('title')
            ->values()
            ->all());
    }

    /**
     * Widget scripts for the customer panels.
     *
     * @return array<int, array{plugin: string, name: string, script: string, style: ?string}>
     */
    public static function widgets(): array
    {
        return self::memo('widgets', fn () => collect(self::plugins())->flatMap(function ($plugin) {
            $widgets = [];

            foreach ((array) ($plugin['widgets']['user'] ?? []) as $widget) {
                $name = (string) ($widget['name'] ?? '');
                if (! preg_match('/^[\w-]+$/', $name) || ! File::exists($plugin['path'] . "/dist/widgets/{$name}.js") || ! self::enabled($widget)) {
                    continue;
                }

                $widgets[] = [
                    'plugin' => $plugin['plugin_id'],
                    'name'   => $name,
                    'script' => self::asset($plugin, "widgets/{$name}.js"),
                    'style'  => File::exists($plugin['path'] . "/dist/widgets/{$name}.css") ? self::asset($plugin, "widgets/{$name}.css") : null,
                ];
            }

            return $widgets;
        })->values()->all());
    }

    /** @return array<string, string> plugin permission keys and labels, for the staff form */
    public static function permissions(): array
    {
        return collect(self::plugins())
            ->flatMap(fn ($plugin) => is_array($plugin['permissions'] ?? null) ? $plugin['permissions'] : [])
            ->filter(fn ($label, $key) => is_string($key) && preg_match('/^[a-z0-9_]+$/', $key))
            ->all();
    }

    /**
     * Placeholder groups plugins add to the Email Templates editor (for their own templates).
     *
     * @return array<string, array<string, string>> group => [":code" => sample]
     */
    public static function emailShortcodes(): array
    {
        $groups = [];

        foreach (self::plugins() as $plugin) {
            foreach ((array) ($plugin['email_shortcodes'] ?? []) as $group => $codes) {
                foreach ((array) $codes as $code => $sample) {
                    if (is_string($group) && is_string($code) && preg_match('/^:[a-z0-9]+$/', $code)) {
                        $groups[$group][$code] = (string) $sample;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * URL of a file in a plugin's dist folder: the public copy (public/admin-assets), or the
     * customer asset route when it couldn't be copied. ?v= changes with every build.
     */
    public static function asset(array $plugin, string $file): string
    {
        $dist    = $plugin['path'] . '/dist';
        $name    = basename($plugin['path']);
        $version = preg_replace('/[^0-9A-Za-z.\-]/', '', (string) ($plugin['version'] ?? '0')) . '-' . (int) @filemtime($dist . '/' . $file);

        $public = File::exists($dist . '/manifest.json') && PluginPage::publish('plugin', $name, $dist);

        return ($public
            ? asset('admin-assets/plugins/' . $name . '/' . $file)
            : route('user.plugin.assets', ['plugin' => $name, 'path' => $file])) . '?v=' . $version;
    }

    /** A sidebar or header entry, or null when it can't be shown. */
    private static function link(array $plugin, mixed $entry, ?User $admin = null): ?array
    {
        $route = is_array($entry) ? (string) ($entry['route'] ?? '') : '';
        if ($route === '' || ! Route::has($route) || ! self::enabled($entry)) {
            return null;
        }

        // Admin entries can need a staff permission ("permissions" in plugin.json)
        $permission = $entry['permission'] ?? null;
        if ($admin && $permission && ! (AdminPermissions::for($admin)[$permission] ?? false)) {
            return null;
        }

        try {
            $href = route($route);
        } catch (Throwable $e) {
            // Routes with required parameters can't be sidebar links
            report($e);

            return null;
        }

        return [
            'plugin'   => $plugin['plugin_id'],
            'title'    => __((string) ($entry['label'] ?? $plugin['name'] ?? $plugin['plugin_id'])),
            'route'    => $route,
            'href'     => $href,
            'svg'      => self::icon($plugin, $entry['icon'] ?? null),
            'badge'    => max(0, (int) self::call($entry['badge'] ?? null, 0)),
            // React customer panel: plugins with built customer pages (dist/user) open inside the app
            'inertia'  => File::exists($plugin['path'] . '/dist/user/manifest.json'),
            'isActive' => str_starts_with(rtrim(request()->url(), '/') . '/', rtrim(strtok($href, '?'), '/') . '/'),
        ];
    }

    private static function enabled(array $entry): bool
    {
        return ! isset($entry['enabled']) || (bool) self::call($entry['enabled'], false);
    }

    /** Calls a "Class::method" string from plugin.json; $fallback when it is missing or fails. */
    private static function call(mixed $callable, mixed $fallback): mixed
    {
        if (! is_string($callable) || ! preg_match('/^[\w\\\\]+::\w+$/', $callable) || ! is_callable($callable)) {
            return $fallback;
        }

        try {
            return $callable();
        } catch (Throwable $e) {
            report($e);

            return $fallback;
        }
    }

    /** The plugin's SVG icon (scripts and event handlers removed), or a puzzle piece. */
    private static function icon(array $plugin, mixed $file): string
    {
        $path = is_string($file) ? realpath($plugin['path'] . '/' . ltrim($file, '/')) : false;
        $svg  = $path && str_starts_with($path, realpath($plugin['path']) . DIRECTORY_SEPARATOR) && filesize($path) < 20000
            ? trim((string) file_get_contents($path))
            : '';

        if (! str_starts_with($svg, '<svg')) {
            return self::ICON;
        }

        return preg_replace(['#<script\b.*?</script>#is', '/\son\w+\s*=\s*("[^"]*"|\'[^\']*\')/i'], '', $svg);
    }

    /** $make() once per request and key. */
    private static function memo(string $key, callable $make): mixed
    {
        $memo = request()->attributes->get(self::MEMO, []);

        if (! array_key_exists($key, $memo)) {
            $memo[$key] = $make();
            request()->attributes->set(self::MEMO, $memo);
        }

        return $memo[$key];
    }
}
