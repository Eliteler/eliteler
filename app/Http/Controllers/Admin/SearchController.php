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


namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class SearchController extends Controller
{
    // Admin search
    public function search(Request $request)
    {
        $query = strtolower(trim($request->q ?? ''));

        $routes = collect(Route::getRoutes())
            ->filter(function ($route) use ($query) {

                // GET only
                if (! in_array('GET', $route->methods())) {
                    return false;
                }

                // Must have name
                if (! $route->getName()) {
                    return false;
                }

                // Admin routes only
                if (! in_array('admin', $route->middleware())) {
                    return false;
                }

                // Exclude demo.mode
                if (in_array('demo.mode', $route->middleware())) {
                    return false;
                }

                // Exclude routes with parameters
                if (! empty($route->parameterNames())) {
                    return false;
                }

                // Exclude action routes only
                $exclude = [
                    'store',
                    'save',
                    'delete',
                    'destroy',
                    'status',
                    'action',
                    'toggle',
                    'upload',
                    'download',
                    'restore',
                    'process',
                ];

                foreach ($exclude as $word) {
                    if (str_contains(strtolower($route->getName()), $word)) {
                        return false;
                    }
                }

                // Search match
                return $query === '' ||
                    str_contains(strtolower($route->getName()), $query) ||
                    str_contains(strtolower($route->uri()), $query);
            })
            ->map(function ($route) {

                return [
                    'label' => Str::title(
                        str_replace(['admin.', '_', '.', '-'], ' ', $route->getName())
                    ),
                    'url' => route($route->getName()),
                    'uri' => $route->uri(), // useful for debugging
                ];
            })
            ->values();

        return response()->json($routes);
    }
}
