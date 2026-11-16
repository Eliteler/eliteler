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

namespace App\Classes;

use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ServiceWorker
{
    /**
     * Generate and store service worker.
     */
    public function generateServiceWorker($id, $url)
    {
        // Fix: Fallback if $id is null or empty to prevent the '/.js' filename bug
        if (empty($id)) {
            $id = 'sw-' . md5($url ?: config('app.url'));
        } else {
            // Sanitize id to be safe for filenames
            $id = Str::slug($id);
        }

        $assets = [
            '/' . ltrim($url ?? '', '/'),

            '/css/tailwind.min.css',
            '/css/swiper-bundle.min.css',
            '/css/fontawesome.min.css',
            '/css/flatpickr.min.css',

            '/js/qrious.min.js',
            '/js/jquery.min.js',
            '/js/smooth-scroll.polyfills.min.js',
            '/js/flatpickr.min.js',
            '/js/swiper-bundle.min.js',
            '/js/swiper-element-bundle.min.js',

            '/app/js/footer.js',
        ];

        $serviceWorkerContent = $this->generateServiceWorkerScript($assets);

        // Upload and store the valid JS file
        $serviceWorkerUrl = StorageHelper::uploadImage(
            $serviceWorkerContent,
            'manifest/service-worker',
            $id . '.js'
        );

        return response()->json([
            'serviceWorkerUrl' => $serviceWorkerUrl,
        ]);
    }


    /**
     * Generate service worker JavaScript.
     *
     * @param array<int|string, mixed> $assets
     */
    private function generateServiceWorkerScript(array $assets): string
    {
        // Filter out non-string/empty values to prevent serialization issues
        $filteredAssets = array_values(array_filter($assets, static fn(mixed $item): bool => is_string($item) && trim($item) !== ''));

        // Prevent JS syntax error if json_encode returns false
        $assetsJson = json_encode($filteredAssets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';

        // Safely retrieve application version from database with exception handling
        try {
            $dbVersion = DB::table('config')->where('config_key', 'app_version')->value('config_value');
        } catch (Throwable) {
            $dbVersion = null;
        }

        // Ensure strictly typed string for PHP 8.4 to prevent TypeError in md5()
        $appVersion = is_string($dbVersion) && trim($dbVersion) !== ''
            ? trim($dbVersion)
            : (string) (config('app.version') ?? env('APP_VERSION') ?? '1.0.0');

        // Create a unique cache name based on application version
        $cacheName = 'pwa-' . md5($appVersion);

        return <<<EOL
const staticCacheName = "{$cacheName}";

const filesToCache = {$assetsJson};

/**
 * Install
 */
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
            .then(() => {
                return self.skipWaiting();
            })
    );
});

/**
 * Activate
 */
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName => {
                            return cacheName.startsWith('pwa-');
                        })
                        .filter(cacheName => {
                            return cacheName !== staticCacheName;
                        })
                        .map(cacheName => {
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                return self.clients.claim();
            })
    );
});

/**
 * Fetch
 */
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('/offline');
            })
    );
});
EOL;
    }
}
