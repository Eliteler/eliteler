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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GoBizUpdater2900
{
    public function runUpdate()
    {
        try {
            // Add advanced_analytics column to plans table
            if (Schema::hasTable('plans') && !Schema::hasColumn('plans', 'advanced_analytics')) {
                DB::statement("ALTER TABLE `plans` ADD COLUMN `advanced_analytics` TINYINT(1) NOT NULL DEFAULT 0 AFTER `hide_branding`");
            }

            // Add missing analytics columns to visitors table
            if (Schema::hasTable('visitors')) {
                $columns = [
                    'browser' => "VARCHAR(64) NOT NULL DEFAULT 'Unknown' AFTER `device`",
                    'browser_version' => "VARCHAR(32) NULL AFTER `browser`",
                    'device_type' => "VARCHAR(16) NOT NULL DEFAULT 'Unknown' AFTER `browser_version`",
                    'country_code' => "CHAR(2) NULL AFTER `language`",
                    'country' => "VARCHAR(100) NOT NULL DEFAULT 'Unknown' AFTER `country_code`",
                    'region' => "VARCHAR(100) NOT NULL DEFAULT 'Unknown' AFTER `country`",
                    'city' => "VARCHAR(100) NOT NULL DEFAULT 'Unknown' AFTER `region`",
                    'latitude' => "DECIMAL(10,7) NULL AFTER `city`",
                    'longitude' => "DECIMAL(10,7) NULL AFTER `latitude`",
                    'timezone' => "VARCHAR(64) NULL AFTER `longitude`",
                    'referrer' => "TEXT NULL AFTER `timezone`",
                    'referrer_domain' => "VARCHAR(191) NULL AFTER `referrer`",
                    'source' => "VARCHAR(32) NOT NULL DEFAULT 'Unknown' AFTER `referrer_domain`",
                    'utm_source' => "VARCHAR(100) NULL AFTER `source`",
                    'utm_medium' => "VARCHAR(100) NULL AFTER `utm_source`",
                    'utm_campaign' => "VARCHAR(100) NULL AFTER `utm_medium`",
                    'visitor_hash' => "CHAR(64) NULL AFTER `user_agent`",
                    'is_unique' => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `visitor_hash`",
                    'is_bot' => "TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_unique`",
                ];

                foreach ($columns as $column => $definition) {
                    if (!Schema::hasColumn('visitors', $column)) {
                        DB::statement("ALTER TABLE `visitors` ADD COLUMN `{$column}` {$definition}");
                    }
                }

                // Normalize existing visitor data
                DB::statement("
                    UPDATE `visitors`
                    SET
                        `browser` = CASE
                            WHEN `user_agent` IS NULL OR `user_agent` = '' THEN 'Unknown'
                            WHEN `user_agent` LIKE '%Edg/%' OR `user_agent` LIKE '%EdgA/%' OR `user_agent` LIKE '%EdgiOS/%' THEN 'Edge'
                            WHEN `user_agent` LIKE '%OPR/%' OR `user_agent` LIKE '%Opera%' THEN 'Opera'
                            WHEN `user_agent` LIKE '%SamsungBrowser/%' THEN 'Samsung Internet'
                            WHEN `user_agent` LIKE '%Firefox/%' OR `user_agent` LIKE '%FxiOS/%' THEN 'Firefox'
                            WHEN `user_agent` LIKE '%Chrome/%' OR `user_agent` LIKE '%CriOS/%' THEN 'Chrome'
                            WHEN `user_agent` LIKE '%Safari/%' THEN 'Safari'
                            ELSE 'Unknown'
                        END,
                        `device_type` = CASE
                            WHEN `user_agent` IS NULL OR `user_agent` = '' THEN 'Unknown'
                            WHEN `user_agent` LIKE '%iPad%' OR `user_agent` LIKE '%Tablet%' THEN 'Tablet'
                            WHEN `user_agent` LIKE '%Mobi%' OR `user_agent` LIKE '%Android%' OR `user_agent` LIKE '%iPhone%' THEN 'Mobile'
                            ELSE 'Desktop'
                        END,
                        `is_bot` = CASE
                            WHEN `user_agent` REGEXP 'bot|crawl|spider|slurp|facebookexternalhit|preview|headless|curl|wget|python-requests' THEN 1
                            ELSE 0
                        END,
                        `device` = CASE WHEN `device` IN ('', '0', 'WebKit') THEN 'Unknown' ELSE `device` END,
                        `platform` = CASE WHEN `platform` IN ('', '0') THEN 'Unknown' ELSE `platform` END,
                        `country` = 'Unknown',
                        `region` = 'Unknown',
                        `city` = 'Unknown',
                        `source` = 'Unknown',
                        `is_unique` = 1
                    WHERE `visitor_hash` IS NULL
                ");

                // Map card URLs to business card IDs when the required columns exist
                if (
                    Schema::hasTable('business_cards') &&
                    Schema::hasColumn('business_cards', 'card_url') &&
                    Schema::hasColumn('business_cards', 'card_id')
                ) {
                    DB::statement("
                        UPDATE `visitors` v
                        JOIN `business_cards` b ON b.`card_url` = v.`card_id`
                        SET v.`card_id` = b.`card_id`
                    ");
                }

                // Add indexes only when they do not exist
                $indexes = [
                    'visitors_card_created_index' => ['card_id', 'created_at'],
                    'visitors_card_country_index' => ['card_id', 'country_code'],
                    'visitors_hash_index' => ['visitor_hash'],
                ];

                foreach ($indexes as $index => $indexColumns) {
                    $indexExists = DB::table('information_schema.statistics')
                        ->where('table_schema', DB::getDatabaseName())
                        ->where('table_name', 'visitors')
                        ->where('index_name', $index)
                        ->exists();

                    $columnsExist = collect($indexColumns)->every(
                        fn($column) => Schema::hasColumn('visitors', $column)
                    );

                    if (!$indexExists && $columnsExist) {
                        $columnList = implode('`, `', $indexColumns);
                        DB::statement("ALTER TABLE `visitors` ADD INDEX `{$index}` (`{$columnList}`)");
                    }
                }

                // Remove status only when it exists
                if (Schema::hasColumn('visitors', 'status')) {
                    Schema::table('visitors', function ($table) {
                        $table->dropColumn('status');
                    });
                }
            }

            // Add MaxMind configuration keys if missing
            if (Schema::hasTable('config')) {
                foreach (['maxmind_account_id', 'maxmind_license_key', 'geoip_last_updated'] as $key) {
                    if (!DB::table('config')->where('config_key', $key)->exists()) {
                        DB::table('config')->insert([
                            'config_key' => $key,
                            'config_value' => '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Create analytics music tracks table if missing
            if (!Schema::hasTable('analytics_music_tracks')) {
                DB::statement("
                    CREATE TABLE `analytics_music_tracks` (
                        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                        `title` VARCHAR(150) NOT NULL,
                        `artist` VARCHAR(150) NULL,
                        `file_url` VARCHAR(2048) NOT NULL,
                        `status` TINYINT(1) NOT NULL DEFAULT 1,
                        `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
                        `created_at` TIMESTAMP NULL DEFAULT NULL,
                        `updated_at` TIMESTAMP NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `analytics_music_tracks_status_sort_index` (`status`, `sort_order`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
                ");
            }
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2900 update failed: ' . $e->getMessage());
        }

        // Check if 588969111171 does NOT exist
        if (!DB::table('themes')->where('theme_id', '588969111171')->exists()) {
            DB::statement("INSERT INTO `themes` (`theme_id`, `theme_code`, `theme_thumbnail`, `theme_name`, `theme_description`, `theme_price`) VALUES ('588969111171', 'golf-vcard', 'golf-card.webp', 'Golf', 'vCard', 'Free')");
        }

        // Check if 588969111172 does NOT exist
        if (!DB::table('themes')->where('theme_id', '588969111172')->exists()) {
            DB::statement("INSERT INTO `themes` (`theme_id`, `theme_code`, `theme_thumbnail`, `theme_name`, `theme_description`, `theme_price`) VALUES ('588969111172', 'astrology-vcard', 'astrology-vcard.webp', 'Astrology', 'vCard', 'Free')");
        }

        // Check if "ticketing_settings" does NOT exist in the records of the config table, and add it if missing
        if (!DB::table('config')->where('config_key', 'ticketing_settings')->exists()) {
            DB::statement("INSERT INTO `config` (`config_key`, `config_value`, `created_at`, `updated_at`) VALUES ('ticketing_settings', '', NOW(), NOW())");
        }
    }
}
