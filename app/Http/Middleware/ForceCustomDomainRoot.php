<?php

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
        $path = trim($request->path(), '/');
        
        $mainDomain = strtolower(env('MAIN_DOMAIN'));

        // Get all customer custom domains
        $customDomains = BusinessCard::where('custom_domain', '!=', '')->pluck('custom_domain')->toArray();

        // Check if host is main domain, any subdomain of it or any custom domain
        $isAllowedHost =    
            $host === $mainDomain ||
            str_ends_with($host, '.' . $mainDomain) ||
            in_array($host, $customDomains);
        
        // If URL has a path and host is not main domain or its subdomains
        if ($path !== '' && ! $isAllowedHost) {
            return redirect()->to('https://' . $mainDomain);
        }
        
        return $next($request);
    }
}
