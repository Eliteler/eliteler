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
namespace App\Providers;

use App\Services\TemplateManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TemplateManager::class, function ($app) {
            $templateManager = new TemplateManager();
            $templateManager->loadTemplates();
            return $templateManager;
        });
    }

    public function boot()
    {
        $templateManager = app(TemplateManager::class);

        foreach ($templateManager->getTemplates() as $template) {

            $templateServiceProviderPath = $template['path'] . "/{$template['template_id']}ServiceProvider.php";

            if (File::exists($templateServiceProviderPath)) {
                // Extract namespace dynamically
                $serviceProviderClass = $this->getNamespaceFromFile($templateServiceProviderPath) . "\\{$template['template_id']}ServiceProvider";

                if (class_exists($serviceProviderClass)) {                    
                    $this->app->register($serviceProviderClass);
                }
            }
        }
    }

    public function getNamespaceFromFile($filePath)
    {
        $lines = file($filePath);
        foreach ($lines as $line) {
            if (strpos($line, 'namespace') !== false) {
                return trim(str_replace(['namespace', ';'], '', $line));
            }
        }
        return null;
    }
}
