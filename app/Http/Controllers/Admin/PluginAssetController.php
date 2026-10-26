<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Serves a plugin's prebuilt admin assets from plugins/<Name>/dist only.
 * URLs carry ?v=<plugin version>, so files can be cached for a year.
 */
class PluginAssetController extends Controller
{
    private const TYPES = [
        'js'    => 'text/javascript',
        'css'   => 'text/css',
        'json'  => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'webp'  => 'image/webp',
        'woff2' => 'font/woff2',
    ];

    /** Serves plugins/<Name>/dist, or templates/<Name>/dist when the route sets base=templates. */
    public function __invoke(Request $request, string $plugin, string $path)
    {
        abort_unless(preg_match('/^[A-Za-z0-9_-]+$/', $plugin), 404);

        $base = $request->route('base') === 'templates' ? 'templates' : 'plugins';
        $dist = realpath(base_path("{$base}/{$plugin}/dist"));
        $file = $dist ? realpath($dist . DIRECTORY_SEPARATOR . $path) : false;
        $type = self::TYPES[strtolower(pathinfo((string) $file, PATHINFO_EXTENSION))] ?? null;

        // Must exist, be a whitelisted type, and stay inside dist/
        abort_unless($file && $type && is_file($file) && Str::startsWith($file, $dist . DIRECTORY_SEPARATOR), 404);

        return response()->file($file, [
            'Content-Type'  => $type,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
