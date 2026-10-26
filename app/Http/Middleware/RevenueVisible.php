<?php

namespace App\Http\Middleware;

use App\Support\Admin\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages about money inside an otherwise allowed area (e.g. referral
 * withdrawals), closed to admins with revenue hidden.
 */
class RevenueVisible
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user() && AdminPermissions::hidesRevenue($request->user()), 403, 'Unauthorized action.');

        return $next($request);
    }
}
