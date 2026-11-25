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

namespace Modules\TranslationManager\app\Providers;

use Illuminate\Support\ServiceProvider;

class TranslationManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/translation-manager.php', 'translation-manager');

        require_once __DIR__ . '/TranslationPlaceholderProtector.php';
        if (!class_exists('Modules\TranslationManager\App\Providers\TranslationPlaceholderProtector', false)) {
            class_alias(TranslationPlaceholderProtector::class, 'Modules\TranslationManager\App\Providers\TranslationPlaceholderProtector');
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'translationmanager');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/translation-manager.php' => config_path('translation-manager.php'),
            ], 'translation-manager-config');
        }
    }
}
