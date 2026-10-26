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

use App\BusinessCard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCustomDomainRoot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        // Strip 'www.' to ensure clean hostname matching (e.g. 'domain.com')
        $cleanHost = preg_replace('/^www\./i', '', $host);
        $path = trim($request->path(), '/');

        // Resolve master domain dynamically from config with env fallback
        $mainDomain = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?? env('MAIN_DOMAIN', ''));
        $cleanMainDomain = preg_replace('/^www\./i', '', $mainDomain);

        // Fetch and sanitize all customer custom domains from database
        $customDomains = BusinessCard::whereNotNull('custom_domain')
            ->where('custom_domain', '!=', '')
            ->pluck('custom_domain')
            ->map(function ($domain) {
                // Remove protocols, www prefix, and trailing slashes for accurate comparison
                $domain = strtolower(trim($domain));
                $domain = preg_replace('#^https?://#i', '', $domain);
                $domain = preg_replace('/^www\./i', '', $domain);
                return rtrim($domain, '/');
            })
            ->toArray();

        // Check if host is master domain, a subdomain of it, or an authorized custom domain
        $isAllowedHost =
            $cleanHost === $cleanMainDomain ||
            str_ends_with($cleanHost, '.' . $cleanMainDomain) ||
            in_array($cleanHost, $customDomains) ||
            in_array($host, $customDomains);

        // If URL has a path and host is neither main domain, subdomain, nor registered custom domain
        if ($path !== '' && ! $isAllowedHost) {
            return redirect()->to('https://' . $cleanMainDomain);
        }

        return $next($request);
    }
}
