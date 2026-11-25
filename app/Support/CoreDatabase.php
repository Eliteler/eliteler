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

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Whether the core tables exist yet. Template and plugin service providers
 * create and seed their own tables on boot and read core tables (config,
 * settings, pages), so they must not boot on an empty database: the web
 * installer and `php artisan migrate` on a new database would fail before
 * a single migration runs.
 *
 * Installed web requests are not checked (no extra query per request).
 */
class CoreDatabase
{
    private const TABLES = ['config', 'settings', 'pages'];

    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }

        // Installed site serving a web request: trust it
        if (! app()->runningInConsole() && file_exists(storage_path('installed'))) {
            return self::$ready = true;
        }

        try {
            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    return self::$ready = false;
                }
            }

            return self::$ready = true;
        } catch (Throwable) {
            // No database connection yet (installer before the .env step)
            return self::$ready = false;
        }
    }
}
