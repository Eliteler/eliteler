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

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorageRedirectController extends Controller
{
    /**
     * Redirect public asset requests to Cloudflare R2 / AWS S3 CDN.
     * Resolved strictly via config('filesystems...') to ensure full config:cache compatibility.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $path
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, string $path): RedirectResponse
    {
        $driver = config('filesystems.disks.public.driver') ?: config('filesystems.default', 'local');
        $cdnBase = config('filesystems.disks.public.url');

        // Only redirect if public disk is cloud storage (s3 / r2) and a valid CDN URL is configured
        if (!in_array($driver, ['s3', 'r2'], true) || empty($cdnBase)) {
            abort(404);
        }

        // 1. Strict traversal protection: reject dot-segments, null bytes, and leading dots
        if (str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '%00') || str_starts_with($path, '.')) {
            abort(404);
        }

        // 2. Strict character whitelist (alphanumeric, slash, dash, underscore, dot)
        if (!preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $path)) {
            abort(404);
        }

        // 3. 301 Permanent Redirect directly to public S3 / Cloudflare R2 CDN URL
        // Notice: NO File::get(), NO response()->file(), NO local disk interaction!
        $cdnUrl = rtrim($cdnBase, '/') . '/' . ltrim($path, '/');

        return redirect($cdnUrl, 301, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

