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

namespace App\Providers;

use App\Providers\MigrationConfig;
use Illuminate\Support\ServiceProvider;

class MigrationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Providers\MigrationConfig', function ($app) {
            return new MigrationConfig();
          });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
