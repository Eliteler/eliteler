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
use Throwable;

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
        $user = null;

        // Safely resolve the authenticated user to prevent SessionGuard hash_equals TypeErrors on broken recaller cookies
        try {
            $user = auth()->user();
        } catch (Throwable $e) {
            $user = null;

            // Invalidate and remove the corrupted remember-me cookie from the client
            try {
                if (method_exists(auth()->guard(), 'getRecallerName')) {
                    cookie()->queue(cookie()->forget(auth()->guard()->getRecallerName()));
                }
            } catch (Throwable $cookieException) {
                // Silently ignore cookie queue exceptions
            }
        }

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
