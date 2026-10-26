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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Plugins\AiBuilder\Observers\AiBuilderObserver;

class AiBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // check ai credits
        if (Auth::check()) {
            // ai credits
            $ai_credits = DB::table('ai_credits')
                ->where('user_id', Auth::user()->user_id)
                ->first();

            // insert new credits
            if (empty($ai_credits)) {
                DB::table('ai_credits')->insert([
                    'user_id' => Auth::user()->user_id,
                    'credits' => 0,
                    'trial_credits' => 0,
                ]);
            }
        }

        // listen for user events
        User::observe(AiBuilderObserver::class);
    }

    public function register()
    {
        // You can register other services if needed
    }
}
