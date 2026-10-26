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

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Plugins installed in /plugins (each folder with a plugin.json).
 *
 * A plugin whose admin page is still the old Blade view ("legacy") keeps
 * running (routes, website pages, service provider); only its admin pages
 * ask for the new version.
 */
class PluginManager
{
    protected $plugins = [];

    // constructor
    public function __construct()
    {
        $this->loadPlugins();
    }

    public function loadPlugins()
    {
        $pluginDirectories = File::directories(base_path('plugins'));

        //reset the plugins array
        $this->plugins = [];

        foreach ($pluginDirectories as $directory) {
            $pluginJsonPath = $directory . '/plugin.json';


            if (File::exists($pluginJsonPath)) {
                $pluginData = json_decode(File::get($pluginJsonPath), true);
                if (! is_array($pluginData)) {
                    continue;
                }
                $pluginData['path']   = $directory;
                $pluginData['legacy'] = self::isLegacy($pluginData, $directory);

                // $this->plugins[$pluginData['name']] = $pluginData;

                // add plugin to the list
                $this->plugins[] = $pluginData;
            }
        }
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    /** Installed plugins that run (legacy ones too: only their admin pages are locked). */
    public function active(): array
    {
        return array_values($this->plugins);
    }

    public function isActive(string $id): bool
    {
        foreach ($this->plugins as $plugin) {
            if (($plugin['plugin_id'] ?? null) === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Old Blade-admin version of a plugin: it has a settings page (main_route)
     * but no prebuilt React page (admin_pages + dist/manifest.json). Works
     * on a plugin.json read from disk or from an uploaded zip ($dir null).
     */
    public static function isLegacy(array $manifest, ?string $dir, bool $hasDist = false): bool
    {
        if (empty($manifest['main_route'])) {
            return false;
        }

        return empty($manifest['admin_pages']) || ! ($dir ? File::exists($dir . '/dist/manifest.json') : $hasDist);
    }

    public function deletePlugin($id)
    {
        //check if the plugin exists and remove
        for ($i = 0; $i < count($this->plugins); $i++) {
            if ($this->plugins[$i]['plugin_id'] == $id) {
                $pluginPath = $this->plugins[$i]['path'];
                // Delete the plugin directory
                File::deleteDirectory($pluginPath);
                unset($this->plugins[$i]);
                return true;
            }
        }
        return false;
    }
}
