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

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $languages = array_keys(config('app.languages'));

        if ($request->change_language && in_array($request->change_language, $languages)) {
            $lang = $request->change_language;

            session()->put('language', $lang);

            if ($user) {
                $user->lang = $lang;
                $user->save();
            }
        } elseif ($user && in_array($user->lang, $languages)) {
            $lang = $user->lang;
        } elseif (session('language') && in_array(session('language'), $languages)) {
            $lang = session('language');
        } else {
            $lang = config('app.locale');
        }

        // Set the locale
        app()->setLocale($lang);

        return $next($request);
    }
}
