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

use App\Support\Admin\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserPagePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Fetch current user's details
        $user = Auth::user();

        if ($user) {
            // Same list the admin menu uses: stored permissions + defaults for keys never
            // stored (e.g. admins created before a feature existed, fresh installs) +
            // plugin permissions. A key explicitly turned off stays off.
            $allowed = AdminPermissions::for($user);

            // Money areas are closed to admins with revenue hidden
            if (AdminPermissions::hidesRevenue($user)) {
                $permissions = array_diff($permissions, AdminPermissions::REVENUE_AREAS);
            }

            foreach ($permissions as $permission) {
                if (! empty($allowed[$permission])) {
                    return $next($request);
                }
            }
        }

        // Redirect or throw unauthorized error as per your application's logic
        abort(403, 'Unauthorized action.');

        // Alternatively, you can redirect the user to a specific route:
        // return redirect()->route('unauthorized');
    }
}
