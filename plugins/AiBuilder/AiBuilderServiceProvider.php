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

namespace Plugins\AiBuilder;

use App\User;
use Illuminate\Support\ServiceProvider;
use Plugins\AiBuilder\Observers\AiBuilderObserver;

class AiBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        User::observe(AiBuilderObserver::class);
    }

    public function register()
    {
        // You can register other services if needed
    }
}
