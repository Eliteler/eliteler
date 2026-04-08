<?php

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

        if ($request->change_language) {
            $lang = $request->change_language;

            if (in_array($lang, $languages)) {
                session()->put('language', $lang);

                if ($user) {
                    $user->lang = $lang;
                    $user->save();
                }
            }
        } else {
            if ($user && in_array($user->lang, $languages)) {
                $lang = $user->lang;
            } elseif (session('language') && in_array(session('language'), $languages)) {
                $lang = session('language');
            } else {
                $lang = config('app.locale');
            }
        }

        // ✅ ALWAYS apply locale
        app()->setLocale($lang);

        return $next($request);
    }
}
