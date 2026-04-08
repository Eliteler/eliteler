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
                $templateData['path'] = $directory;

                // add templates to the list
                $this->templates[] = $templateData;
            }
        }
    }

    public function getTemplates()
    {
        return $this->templates;
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
