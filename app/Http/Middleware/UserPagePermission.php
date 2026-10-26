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

        // Check if user exists and has permissions
        if ($user && $user->permissions) {
            $allowedPermissions = json_decode($user->permissions, true);

            // Money areas are closed to admins with revenue hidden
            if (AdminPermissions::hidesRevenue($user)) {
                $permissions = array_diff($permissions, AdminPermissions::REVENUE_AREAS);
            }

            // Check if any of the provided permissions are allowed
            foreach ($permissions as $permission) {
                if (array_key_exists($permission, $allowedPermissions) && $allowedPermissions[$permission]) {
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
