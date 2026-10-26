<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
 */

namespace App\Classes;

use App\Helpers\StorageHelper;
use Illuminate\Support\Str;

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
     */
    private function generateServiceWorkerScript(array $assets)
    {
        $assetsJson = json_encode(array_values(array_filter($assets)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        /*
         * Use application version as cache version.
         */
        $appVersion = config('app.version', env('APP_VERSION', '1.0.0'));

        /*
         * Create a unique cache name based on application version.
         */
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