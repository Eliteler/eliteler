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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check() && Auth::user()->role_id != 2) {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::guard($guard)->check() && Auth::user()->role_id == 2) {
            // check modern_dashboard_settings table is available
            if (Schema::hasTable('modern_dashboard_settings')) {
                // check modern_dashboard_enabled
                $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;

                // if enabled redirect to react dashboard
                if (is_dir(base_path('plugins/ModernDashboard')) && $is_modern_dashboard_enabled == 1) {
                    return redirect()->route('user.dashboard.overview');
                } else {
                    // if disabled redirect to default dashboard
                    return redirect()->route('user.dashboard');
                }
            } else {
                // if table not available redirect to default dashboard
                return redirect()->route('user.dashboard');
            }
        } else {
            return $next($request);
        }
    }
}
