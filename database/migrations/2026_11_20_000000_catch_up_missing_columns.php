<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catch-up for fresh installs (Warnings.md #47).
 *
 * Over many releases these columns were added to existing installs with
 * release SQL files and updater classes, but the Laravel migrations were
 * never changed, so a fresh install (web installer: migrate + db:seed)
 * did not get them. Definitions are copied from an up-to-date install.
 *
 * It also relaxes columns a fresh install creates stricter than an
 * up-to-date install (NOT NULL / shorter types), see MODIFY.
 *
 * Every step checks first: on installs that already have a column or table
 * (or the up-to-date definition) nothing happens, so this is safe everywhere and can run more than once.
 * Tables created by their own plugin or template (settings tables,
 * gobiz_*_config...) are not here; `config` rows come from the GoBiz server.
 */
return new class extends Migration
{
    /** table => [column, definition, after] in the order of an up-to-date install */
    private const COLUMNS = [
        'ai_credits' => [
            ['trial_credits', '`trial_credits` int NOT NULL DEFAULT \'0\'', 'credits'],
        ],
        'ai_credits_plans' => [
            ['in_app_product_id', '`in_app_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'ai_credits_plan_id'],
        ],
        'business_cards' => [
            ['contact_form_title', '`contact_form_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Contact Us\'', 'description'],
            ['is_enable_pwa', '`is_enable_pwa` tinyint(1) NOT NULL DEFAULT \'1\'', 'is_google_wallet_hidden'],
            ['privacy_policy', '`privacy_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'seo_configurations'],
            ['terms_and_conditions', '`terms_and_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'privacy_policy'],
            ['refund_policy', '`refund_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'terms_and_conditions'],
            ['shipping_policy', '`shipping_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'refund_policy'],
            ['cookie_policy', '`cookie_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'shipping_policy'],
            ['customer_support_policy', '`customer_support_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'cookie_policy'],
            ['intro_screen', '`intro_screen` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'directory_listing'],
        ],
        'business_fields' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Social Links\'', 'card_id'],
        ],
        'business_hours' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Business Hours\'', 'card_id'],
        ],
        'card_appointment_times' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Appointment\'', 'card_id'],
        ],
        'currencies' => [
            ['status', '`status` int NOT NULL DEFAULT \'1\'', 'iso_numeric'],
        ],
        'galleries' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Gallery\'', 'card_id'],
        ],
        'gateways' => [
            ['plugin_id', '`plugin_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'updated_at'],
        ],
        'medias' => [
            ['size', '`size` bigint NOT NULL DEFAULT \'0\'', 'media_url'],
        ],
        'pages' => [
            ['template_id', '`template_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'GoBizOriginal\'', 'id'],
        ],
        'payments' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Payment Options\'', 'card_id'],
        ],
        'plans' => [
            ['paypal_plan_id', '`paypal_plan_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'plan_id'],
            ['dodo_payments_plan_price_id', '`dodo_payments_plan_price_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'paypal_plan_id'],
            ['paddle_plan_price_id', '`paddle_plan_price_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'dodo_payments_plan_price_id'],
            ['cashfree_plan_hash', '`cashfree_plan_hash` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'paddle_plan_price_id'],
            ['cashfree_plan_id', '`cashfree_plan_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'cashfree_plan_hash'],
            ['in_app_purchase_id', '`in_app_purchase_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'cashfree_plan_id'],
            ['in_app_product_id', '`in_app_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'in_app_purchase_id'],
            ['in_app_purchase_type', '`in_app_purchase_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'in_app_product_id'],
            ['trial_ai_credits', '`trial_ai_credits` int NOT NULL DEFAULT \'0\'', 'ai_credits'],
            ['google_wallet', '`google_wallet` tinyint(1) NOT NULL DEFAULT \'0\'', 'show_nfc_card_feature'],
            ['no_of_google_wallets', '`no_of_google_wallets` bigint NOT NULL DEFAULT \'0\'', 'google_wallet'],
            ['storage', '`storage` bigint DEFAULT NULL', 'advanced_settings'],
            ['advanced_analytics', '`advanced_analytics` tinyint(1) NOT NULL DEFAULT \'0\'', 'hide_branding'],
        ],
        'service_bookings' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Service Booking\'', 'vcard_id'],
        ],
        'services' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Services\'', 'card_id'],
        ],
        'settings' => [
            ['seo_meta_description', '`seo_meta_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'favicon'],
            ['seo_keywords', '`seo_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'seo_meta_description'],
            ['seo_image', '`seo_image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'seo_keywords'],
            ['name', '`name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'custom_scripts'],
            ['address', '`address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'name'],
            ['driver', '`driver` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'address'],
            ['host', '`host` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'driver'],
            ['port', '`port` int NOT NULL', 'host'],
            ['encryption', '`encryption` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'port'],
            ['username', '`username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'encryption'],
            ['password', '`password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'username'],
        ],
        'store_products' => [
            ['has_variants', '`has_variants` tinyint(1) NOT NULL DEFAULT \'0\'', 'product_status'],
        ],
        'testimonials' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Testimonials\'', 'card_id'],
        ],
        'themes' => [
            ['theme_css', '`theme_css` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'theme_price'],
            ['theme_js', '`theme_js` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'theme_css'],
        ],
        'transactions' => [
            ['payment_id', '`payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'transaction_id'],
        ],
        'users' => [
            ['lang', '`lang` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'en\'', 'choosed_theme'],
            ['auth_token', '`auth_token` json DEFAULT NULL', 'remember_token'],
            ['device_token', '`device_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', 'auth_token'],
            ['otp_number', '`otp_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'device_token'],
        ],
        'vcard_products' => [
            ['title', '`title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Products\'', 'product_id'],
        ],
        'visitors' => [
            ['browser', '`browser` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'device'],
            ['browser_version', '`browser_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'browser'],
            ['device_type', '`device_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'browser_version'],
            ['country_code', '`country_code` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'language'],
            ['country', '`country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'country_code'],
            ['region', '`region` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'country'],
            ['city', '`city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'region'],
            ['latitude', '`latitude` decimal(10,7) DEFAULT NULL', 'city'],
            ['longitude', '`longitude` decimal(10,7) DEFAULT NULL', 'latitude'],
            ['timezone', '`timezone` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'longitude'],
            ['referrer', '`referrer` text COLLATE utf8mb4_unicode_ci', 'timezone'],
            ['referrer_domain', '`referrer_domain` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'referrer'],
            ['source', '`source` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Unknown\'', 'referrer_domain'],
            ['utm_source', '`utm_source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'source'],
            ['utm_medium', '`utm_medium` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'utm_source'],
            ['utm_campaign', '`utm_campaign` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'utm_medium'],
            ['visitor_hash', '`visitor_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'user_agent'],
            ['is_unique', '`is_unique` tinyint(1) NOT NULL DEFAULT \'1\'', 'visitor_hash'],
            ['is_bot', '`is_bot` tinyint(1) NOT NULL DEFAULT \'0\'', 'is_unique'],
        ],
    ];

    /**
     * Columns that exist on a fresh install but stricter or smaller than on an
     * up-to-date install (NOT NULL where NULL is allowed, varchar where longtext,
     * shorter varchar): saving would fail. table => [column, type, nullable, definition].
     * Changed only when the column differs, so up-to-date installs are untouched.
     */
    private const MODIFY = [
        'business_card_intros' => [
            ['created_at', 'timestamp', 'YES', '`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP'],
            ['updated_at', 'timestamp', 'YES', '`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'],
        ],
        'business_cards' => [
            ['cover', 'longtext', 'YES', '`cover` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
            ['cover_type', 'varchar(191)', 'YES', '`cover_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['profile', 'longtext', 'YES', '`profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
            ['sub_title', 'longtext', 'YES', '`sub_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
        ],
        'business_fields' => [
            ['content', 'longtext', 'YES', '`content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
            ['label', 'longtext', 'NO', '`label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL'],
        ],
        'business_hours' => [
            ['friday', 'varchar(191)', 'YES', '`friday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['monday', 'varchar(191)', 'YES', '`monday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['saturday', 'varchar(191)', 'YES', '`saturday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['sunday', 'varchar(191)', 'YES', '`sunday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['thursday', 'varchar(191)', 'YES', '`thursday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['tuesday', 'varchar(191)', 'YES', '`tuesday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['wednesday', 'varchar(191)', 'YES', '`wednesday` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
        ],
        'galleries' => [
            ['gallery_image', 'longtext', 'NO', '`gallery_image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL'],
        ],
        'nfc_card_keys' => [
            ['associated_nfc_card_id', 'varchar(255)', 'YES', '`associated_nfc_card_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['associated_plan_id', 'varchar(255)', 'YES', '`associated_plan_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
            ['key_status', 'varchar(255)', 'NO', '`key_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'expired\''],
        ],
        'payments' => [
            ['content', 'longtext', 'YES', '`content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
        ],
        'plans' => [
            ['recommended', 'tinyint(1)', 'YES', '`recommended` tinyint(1) DEFAULT \'0\''],
            ['trial', 'int', 'YES', '`trial` int DEFAULT \'0\''],
        ],
        'service_bookings' => [
            ['service_booking_receive_email', 'varchar(191)', 'YES', '`service_booking_receive_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
        ],
        'testimonials' => [
            ['review_subtext', 'longtext', 'YES', '`review_subtext` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
        ],
        'transactions' => [
            ['desciption', 'longtext', 'YES', '`desciption` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
            ['invoice_details', 'longtext', 'YES', '`invoice_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'],
        ],
        'users' => [
            ['billing_zipcode', 'varchar(255)', 'YES', '`billing_zipcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL'],
        ],
        'vcard_products' => [
            ['sales_price', 'double(15,2)', 'YES', '`sales_price` double(15,2) DEFAULT NULL'],
        ],
    ];

    /** Core tables that nothing else creates */
    private const TABLES = [
        'store_product_variants' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `store_product_variants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `regular_price` decimal(15,2) DEFAULT NULL,
  `sales_price` decimal(15,2) NOT NULL,
  `stock` int DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

SQL,
        'analytics_music_tracks' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `analytics_music_tracks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_music_tracks_status_sort_index` (`status`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

SQL,
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $sql) {
            if (! Schema::hasTable($table)) {
                DB::statement($sql);
            }
        }

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as [$column, $definition, $after]) {
                if (Schema::hasColumn($table, $column)) {
                    continue;
                }

                $position = $after && Schema::hasColumn($table, $after) ? " AFTER `{$after}`" : '';

                DB::statement("ALTER TABLE `{$table}` ADD {$definition}{$position}");
            }
        }

        foreach (self::MODIFY as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as [$column, $type, $nullable, $definition]) {
                $current = DB::selectOne(
                    'SELECT COLUMN_TYPE AS type, IS_NULLABLE AS nullable FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$table, $column]
                );

                if ($current && ($current->type !== $type || $current->nullable !== $nullable)) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY {$definition}");
                }
            }
        }
    }

    public function down(): void
    {
        // Columns that existing installs rely on; never removed automatically.
    }
};
