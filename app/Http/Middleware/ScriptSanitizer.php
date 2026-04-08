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
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;
use Symfony\Component\HttpFoundation\Response;

class ScriptSanitizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        $excludedRoutes = [
            'admin.publish.blog',
            'admin.update.blog',
            'admin.save.page',
            'admin.update.custom.page',
            'admin.update.theme.css',
            'admin.update.theme.js'
        ];

        // Keys to skip even on purified routes
        $excludedKeys = ['custom_js', 'custom_scripts', 'raw_html'];

        // Skip purification for certain routes
        if (!in_array($request->route()->getName(), $excludedRoutes)) {
            array_walk_recursive($input, function (&$value, $key) use ($excludedKeys) {
                if (isset($value) && is_string($value)) {
                    if (in_array($key, $excludedKeys)) {
                        return;
                    }

                    $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleaned = Purifier::clean($decoded);
                    $value = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            });
        }

        $request->merge($input);

        return $next($request);
    }
}
