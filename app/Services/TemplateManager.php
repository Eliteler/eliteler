<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Website templates installed in /templates (each folder with a template.json).
 *
 * A template whose admin pages are still the old Blade views ("legacy") keeps
 * serving the public website, but its admin pages ask for its new version.
 */
class TemplateManager
{
    protected $templates = [];

    // constructor
    public function __construct()
    {
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        $templateDirectories = File::directories(base_path('templates'));

        //reset the templates array
        $this->templates = [];

        foreach ($templateDirectories as $directory) {
            $templateJsonPath = $directory . '/template.json';


            if (File::exists($templateJsonPath)) {
                $templateData = json_decode(File::get($templateJsonPath), true);
                if (! is_array($templateData)) {
                    continue;
                }
                $templateData['path'] = $directory;
                $templateData['legacy'] = self::isLegacy($templateData, $directory);

                // add templates to the list
                $this->templates[] = $templateData;
            }
        }
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Old Blade-admin version of a template: it has admin pages (main_route)
     * but no prebuilt React pages (dist/manifest.json). Works on a
     * template.json read from disk or from an uploaded zip ($dir null).
     */
    public static function isLegacy(array $manifest, ?string $dir, bool $hasDist = false): bool
    {
        if (empty($manifest['main_route'])) {
            return false;
        }

        return ! ($dir ? File::exists($dir . '/dist/manifest.json') : $hasDist);
    }

    public function deleteTemplate($id)
    {
        //check if the template exists and remove
        for ($i = 0; $i < count($this->templates); $i++) {
            if ($this->templates[$i]['template_id'] == $id) {
                $templatePath = $this->templates[$i]['path'];
                // Delete the template directory
                File::deleteDirectory($templatePath);
                unset($this->templates[$i]);
                return true;
            }
        }
        return false;
    }
}
