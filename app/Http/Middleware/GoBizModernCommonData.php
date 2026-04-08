<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class GoBizModernCommonData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        GoBizCommonService::referralCookie($request);

        View::share([
            'pages'             => GoBizCommonService::pages(),
            'config'            => GoBizCommonService::config(),
            'plans'             => GoBizCommonService::plans(),
            'settings'          => GoBizCommonService::settings(),
            'blogs'             => GoBizCommonService::blogs(),
            'availableNfcCards' => GoBizCommonService::nfcCards(),
            'themes'            => GoBizCommonService::themes(),
            'directorySettings' => GoBizCommonService::directorySettings(),
            'directoryBusinessCards' => GoBizCommonService::directoryBusinessCards($type, $location, $search, $forceEnableDirectory),
        ]);

        return $next($request);
    }
}
