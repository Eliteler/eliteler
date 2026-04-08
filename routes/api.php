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

use Illuminate\Support\Facades\File;

// Path to plugins directory
$pluginsPath = base_path('plugins');

if (File::exists($pluginsPath)) {
    foreach (File::directories($pluginsPath) as $plugin) {
        $routeFile = $plugin . '/api.php';
        if (File::exists($routeFile)) {
            require_once $routeFile;
        }
    }
}
