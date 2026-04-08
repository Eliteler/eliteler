<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DynamicCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        // If no Origin header, continue normally
        if (!$origin) {
            return $next($request);
        }

        $host = parse_url($origin, PHP_URL_HOST);

        // Check approved custom domains
        $allowed = DB::table('custom_domain_requests')
            ->where('transfer_status', 1)
            ->where('status', 1)
            ->where('current_domain', $host)
            ->exists();

        // Handle preflight request
        if ($request->getMethod() === 'OPTIONS') {
            if ($allowed) {
                return response('', 204)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            return response('', 204);
        }

        $response = $next($request);

        if ($allowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
