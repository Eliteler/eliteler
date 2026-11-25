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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GoBizUpdater2910
{
    /**
     * Run version 29.1.0 update routine.
     */
    public function runUpdate(): void
    {
        try {
            $this->updateEnvironmentFile();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 update failed: ' . $e->getMessage());
        }

        try {
            $this->smartProducts();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 smartProducts failed: ' . $e->getMessage());
        }

        try {
            $this->nfcOrderIsLocked();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 nfcOrderIsLocked failed: ' . $e->getMessage());
        }

        try {
            $this->referralCurrency();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 referralCurrency failed: ' . $e->getMessage());
        }

        try {
            $this->gobizOriginalSmartProducts();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 gobizOriginalSmartProducts failed: ' . $e->getMessage());
        }

        try {
            $this->personalizedLinkExcludes();
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 personalizedLinkExcludes failed: ' . $e->getMessage());
        }

        // Update APP_VERSION in .env to 29.1.0
        try {
            $envPath = base_path('.env');
            if (file_exists($envPath) && is_writable($envPath)) {
                $content = file_get_contents($envPath);
                if ($content !== false) {
                    $content = preg_replace('/^APP_VERSION=.*$/m', 'APP_VERSION=29.1.0', $content);
                    file_put_contents($envPath, $content);
                }
            }
        } catch (Throwable $e) {
            Log::error('GoBizUpdater2910 update APP_VERSION failed: ' . $e->getMessage());
        }
    }

    /**
     * Smart Products (NFC module evolution): smart_product_types table,
     * nfc_card_designs columns, nfc_card_keys columns, nfc_card_orders columns,
     * order timeline table, image assets table, shipping settings, and default products.
     * Direct DB statements from smart_products_migration.sql with guards.
     */
    protected function smartProducts(): void
    {
        // 1. Admin-managed product types
        if (! Schema::hasTable('smart_product_types')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `smart_product_types` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `code` VARCHAR(50) NOT NULL,
                    `name` VARCHAR(100) NOT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY `smart_product_types_code_unique` (`code`), 
                    UNIQUE KEY `smart_product_types_name_unique` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (Schema::hasTable('smart_product_types')) {
            if (! DB::table('smart_product_types')->where('code', 'generic')->exists()) {
                DB::statement("
                    INSERT IGNORE INTO `smart_product_types` (`code`, `name`, `created_at`, `updated_at`)
                    VALUES
                        ('generic', 'Generic', NOW(), NOW()),
                        ('nfc_card', 'NFC Card', NOW(), NOW()),
                        ('nfc_stand', 'NFC Stand', NOW(), NOW()),
                        ('nfc_tag', 'NFC Tag', NOW(), NOW()),
                        ('wristband', 'Wristband', NOW(), NOW()),
                        ('keychain', 'Keychain', NOW(), NOW()),
                        ('sticker', 'Sticker', NOW(), NOW()),
                        ('other', 'Other', NOW(), NOW())
                ");
            }
        }

        // 2. Products: nfc_card_designs
        if (Schema::hasTable('nfc_card_designs')) {
            if (! Schema::hasColumn('nfc_card_designs', 'product_type')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `product_type` VARCHAR(50) NOT NULL DEFAULT 'nfc_card' AFTER `nfc_card_name`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'short_description')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `short_description` TEXT NULL DEFAULT NULL AFTER `product_type`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'gallery_images')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `gallery_images` TEXT NULL DEFAULT NULL AFTER `nfc_card_back_image`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'compare_at_price')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `compare_at_price` DECIMAL(10,2) NULL DEFAULT NULL AFTER `nfc_card_price`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'generates_keys')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `generates_keys` TINYINT(1) NOT NULL DEFAULT 1 AFTER `available_stocks`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'allow_logo_upload')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `allow_logo_upload` TINYINT(1) NOT NULL DEFAULT 1 AFTER `generates_keys`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'default_link_type')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `default_link_type` VARCHAR(20) NOT NULL DEFAULT 'vcard' AFTER `allow_logo_upload`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'image_assets')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `image_assets` TEXT NULL DEFAULT NULL AFTER `allow_logo_upload`");
            }
            if (! Schema::hasColumn('nfc_card_designs', 'sort_order')) {
                DB::statement("ALTER TABLE `nfc_card_designs` ADD `sort_order` INT NOT NULL DEFAULT 0 AFTER `default_link_type`");
            }
        }

        // 3. Chip keys: nfc_card_keys
        if (Schema::hasTable('nfc_card_keys')) {
            if (! Schema::hasColumn('nfc_card_keys', 'associated_plan_id')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `associated_plan_id` VARCHAR(255) NULL DEFAULT NULL AFTER `card_id`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'associated_nfc_card_id')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `associated_nfc_card_id` VARCHAR(255) NULL DEFAULT NULL AFTER `associated_plan_id`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'key_status')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `key_status` VARCHAR(255) NOT NULL DEFAULT 'expired' AFTER `status`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'link_type')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `link_type` VARCHAR(20) NOT NULL DEFAULT 'vcard' AFTER `link_status`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'link_target')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `link_target` TEXT NULL DEFAULT NULL AFTER `link_type`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'label')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `label` VARCHAR(100) NULL DEFAULT NULL AFTER `link_target`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'scan_count')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `scan_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `label`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'last_scanned_at')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `last_scanned_at` DATETIME NULL DEFAULT NULL AFTER `scan_count`");
            }
            if (! Schema::hasColumn('nfc_card_keys', 'user_id')) {
                DB::statement("ALTER TABLE `nfc_card_keys` ADD `user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `nfc_card_key_id`, ADD INDEX `nfc_card_keys_user_id_index` (`user_id`)");
            }
        }

        // 4. Orders: nfc_card_orders
        if (Schema::hasTable('nfc_card_orders')) {
            if (! Schema::hasColumn('nfc_card_orders', 'is_nfc_written')) {
                DB::statement("ALTER TABLE `nfc_card_orders` ADD `is_nfc_written` TINYINT(1) NOT NULL DEFAULT 0 AFTER `order_status`");
            }
            if (! Schema::hasColumn('nfc_card_orders', 'is_locked')) {
                DB::statement("ALTER TABLE `nfc_card_orders` ADD `is_locked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_nfc_written`");
            }
            if (! Schema::hasColumn('nfc_card_orders', 'shipped_at')) {
                DB::statement("ALTER TABLE `nfc_card_orders` ADD `shipped_at` DATETIME NULL DEFAULT NULL AFTER `is_nfc_written`");
            }
            if (! Schema::hasColumn('nfc_card_orders', 'delivered_at')) {
                DB::statement("ALTER TABLE `nfc_card_orders` ADD `delivered_at` DATETIME NULL DEFAULT NULL AFTER `shipped_at`");
            }
        }

        // 5. Order tracking timeline
        if (! Schema::hasTable('nfc_card_order_histories')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `nfc_card_order_histories` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `nfc_card_order_id` VARCHAR(191) NOT NULL,
                    `order_status` VARCHAR(50) NOT NULL,
                    `message` TEXT NULL,
                    `tracking_number` VARCHAR(191) NULL DEFAULT NULL,
                    `courier_partner` VARCHAR(191) NULL DEFAULT NULL,
                    `notified` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `nfc_card_order_histories_nfc_card_order_id_index` (`nfc_card_order_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 6. Order image assets
        if (! Schema::hasTable('nfc_card_order_assets')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `nfc_card_order_assets` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `nfc_card_order_id` VARCHAR(191) NOT NULL,
                    `asset_key` VARCHAR(20) NOT NULL,
                    `file_path` VARCHAR(255) NOT NULL,
                    `file_type` VARCHAR(10) NOT NULL,
                    `original_name` VARCHAR(191) NULL DEFAULT NULL,
                    `size` INT UNSIGNED NOT NULL DEFAULT 0,
                    `uploaded_by` BIGINT UNSIGNED NULL DEFAULT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `nfc_card_order_assets_nfc_card_order_id_index` (`nfc_card_order_id`),
                    UNIQUE KEY `nfc_card_order_assets_order_asset_unique` (`nfc_card_order_id`, `asset_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 7. Plans: show_nfc_card_feature
        if (Schema::hasTable('plans') && ! Schema::hasColumn('plans', 'show_nfc_card_feature')) {
            DB::statement("ALTER TABLE `plans` ADD `show_nfc_card_feature` TINYINT(1) NOT NULL DEFAULT 1 AFTER `nfc_card`");
        }

        // 8. Settings: flat shipping fee and free shipping threshold
        if (Schema::hasTable('config')) {
            foreach (['smart_product_shipping_fee' => '0', 'smart_product_free_shipping_above' => '0'] as $key => $val) {
                if (! DB::table('config')->where('config_key', $key)->exists()) {
                    DB::table('config')->insert([
                        'config_key'   => $key,
                        'config_value' => $val,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }

        // 9. Default catalog products
        if (Schema::hasTable('nfc_card_designs')) {
            $defaults = [
                ['SP-PET-TAG', 'Pet ID NFC Tag', 'nfc_tag', 'Collar tag that shows your pet\'s profile and your contact details with one tap.', 'A durable NFC tag for your pet\'s collar. Anyone who finds your pet can tap it with a phone to see the pet\'s name, your phone number and address, and call you straight away. Update the details any time without replacing the tag.', 'img/smart-products/pet-id-tag.svg', 0, 1, 0, 'vcard', 10],
                ['SP-GOOGLE-REVIEW-STAND', 'Google Review NFC Stand', 'nfc_stand', 'Counter stand that opens your Google review page with a tap or scan.', 'Place it on your counter or table. Customers tap their phone or scan the QR code and land directly on your Google review form. More reviews, less effort. Change the link from your dashboard at any time.', 'img/smart-products/google-review-stand.svg', 0, 1, 1, 'url', 20],
                ['SP-EVENT-WRISTBAND', 'Smart Event Wristband', 'wristband', 'NFC wristband for events, check-ins and instant contact sharing.', 'A comfortable NFC wristband for events, conferences, gyms and resorts. Share your profile, event pass or any link with a single tap. Waterproof and reusable.', 'img/smart-products/event-wristband.svg', 0, 1, 1, 'vcard', 30],
                ['SP-TIPPING-STAND', 'Digital Tipping Stand', 'nfc_stand', 'Let customers tip instantly through your payment link.', 'A tap-to-tip stand for salons, cafés, delivery and service staff. Customers tap or scan to open your UPI, PayPal or other payment link and leave a tip in seconds.', 'img/smart-products/tipping-stand.svg', 0, 1, 1, 'url', 40],
                ['SP-EMERGENCY-KEYCHAIN', 'Emergency Contact Keychain', 'keychain', 'Keychain that shares emergency contacts and medical notes with a tap.', 'Keep your emergency contacts, blood group and important medical notes one tap away. Ideal for children, elderly family members, riders and travellers.', 'img/smart-products/emergency-keychain.svg', 0, 1, 0, 'vcard', 50],
                ['SP-WARRANTY-CARD', 'Digital Warranty Card', 'nfc_card', 'Put a tap-to-open warranty and support page in every product box.', 'An NFC and QR card for your product packaging. Buyers tap it to see warranty terms, register the product or contact your support team. No more lost paper warranty cards.', 'img/smart-products/warranty-card.svg', 0, 1, 1, 'url', 60],
                ['SP-MEMBERSHIP-CARD', 'NFC Membership Card', 'nfc_card', 'Branded membership card for clubs, gyms and loyalty programs.', 'A premium NFC membership card with your branding. Members tap it to open their profile, benefits or your loyalty page.', 'img/smart-products/membership-card.svg', 0, 1, 1, 'vcard', 70],
                ['SP-WHATSAPP-ORDER-STAND', 'WhatsApp Ordering Stand', 'nfc_stand', 'Customers tap to open your WhatsApp store and order.', 'A table or counter stand that opens your WhatsApp store. Customers browse your products and send their order on WhatsApp without downloading any app.', 'img/smart-products/whatsapp-order-stand.svg', 0, 1, 1, 'store', 80],
                ['SP-BOOKING-STAND', 'Appointment Booking Stand', 'nfc_stand', 'Tap to open your appointment booking page.', 'Perfect for clinics, salons and consultants. Customers tap or scan to open your business card and book an appointment in a free slot.', 'img/smart-products/booking-stand.svg', 0, 1, 1, 'vcard', 90],
                ['SP-REVIEW-STAND', 'Review Collection Stand', 'nfc_stand', 'Collect reviews on any platform: Google, Facebook, TripAdvisor and more.', 'Point this stand to any review site you choose. Customers tap or scan and leave a review while the experience is fresh.', 'img/smart-products/review-stand.svg', 0, 1, 1, 'url', 100],
                ['SP-MENU-STAND', 'Smart Menu Stand', 'nfc_stand', 'Contactless menu for restaurants and cafés.', 'Guests tap or scan to see your menu with photos and prices, and order on WhatsApp. Update the menu any time without reprinting.', 'img/smart-products/menu-stand.svg', 0, 1, 1, 'store', 110],
                ['SP-SOCIAL-STAND', 'Social Media Follow Stand', 'nfc_stand', 'Grow your followers with a tap-to-follow stand.', 'Send customers to your Instagram, Facebook, YouTube or any profile with one tap. Great for shops, cafés and events.', 'img/smart-products/social-stand.svg', 0, 1, 1, 'url', 120],
                ['SP-NFC-STICKER', 'NFC Smart Sticker', 'sticker', 'Small sticker that turns any surface into a tap point.', 'Stick it on a phone case, laptop, door or product. Link it to your business card, store or any URL and change it whenever you like.', 'img/smart-products/nfc-sticker.svg', 0, 1, 0, 'url', 130],
            ];

            foreach ($defaults as $p) {
                if (! DB::table('nfc_card_designs')->where('nfc_card_id', $p[0])->exists()) {
                    DB::table('nfc_card_designs')->insert([
                        'nfc_card_id'          => $p[0],
                        'nfc_card_name'        => $p[1],
                        'product_type'         => $p[2],
                        'short_description'    => $p[3],
                        'nfc_card_description' => $p[4],
                        'nfc_card_front_image' => $p[5],
                        'nfc_card_back_image'  => $p[5],
                        'gallery_images'       => json_encode([$p[5]]),
                        'nfc_card_price'       => $p[6],
                        'available_stocks'     => 0,
                        'generates_keys'       => $p[7],
                        'allow_logo_upload'    => $p[8],
                        'default_link_type'    => $p[9],
                        'sort_order'           => $p[10],
                        'status'               => 0,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                }
            }
        }

        // 10. Backfill image assets and normalize product types
        if (Schema::hasTable('nfc_card_designs') && Schema::hasColumn('nfc_card_designs', 'image_assets')) {
            DB::statement("
                UPDATE `nfc_card_designs`
                SET `image_assets` = '[{\"key\":\"logo\",\"name\":\"Logo\",\"resolution\":\"\",\"required\":false}]'
                WHERE `allow_logo_upload` = 1 AND (`image_assets` IS NULL OR `image_assets` = '' OR `image_assets` = '[]')
            ");
        }

        if (Schema::hasTable('nfc_card_designs') && Schema::hasColumn('nfc_card_designs', 'product_type') && Schema::hasTable('smart_product_types')) {
            DB::statement("
                UPDATE `nfc_card_designs` AS product
                LEFT JOIN `smart_product_types` AS nfc_card ON nfc_card.`code` = 'nfc_card'
                SET product.`product_type` = COALESCE(nfc_card.`code`, 'generic')
                WHERE product.`product_type` IS NULL OR product.`product_type` = ''
            ");

            DB::statement("
                UPDATE `nfc_card_designs` AS product
                LEFT JOIN `smart_product_types` AS assigned_type
                    ON assigned_type.`code` = CONVERT(product.`product_type` USING utf8mb4) COLLATE utf8mb4_unicode_ci
                SET product.`product_type` = 'generic'
                WHERE assigned_type.`id` IS NULL
            ");
        }
    }

    /**
     * NFC Card Orders: lock flag for permanent read-only status.
     * Direct DB statements from nfc_order_is_locked_migration.sql.
     */
    protected function nfcOrderIsLocked(): void
    {
        if (Schema::hasTable('nfc_card_orders') && ! Schema::hasColumn('nfc_card_orders', 'is_locked')) {
            DB::statement("ALTER TABLE `nfc_card_orders` ADD `is_locked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_nfc_written`");
        }
    }

    /**
     * Multi-currency history: referral_withdraw_requests.currency, the currency
     * of existing referral earnings / requests, and payment rows that saved a
     * config key instead of a currency code.
     * Direct DB statements from referral_currency_migration.sql.
     */
    protected function referralCurrency(): void
    {
        if (Schema::hasTable('referral_withdraw_requests') && ! Schema::hasColumn('referral_withdraw_requests', 'currency')) {
            DB::statement("ALTER TABLE `referral_withdraw_requests` ADD `currency` VARCHAR(10) NULL DEFAULT NULL AFTER `amount`");
        }

        $base = 'USD';
        if (Schema::hasTable('config')) {
            $val = DB::table('config')->where('config_key', 'currency')->value('config_value');
            if (! empty($val)) {
                $base = strtoupper(trim($val));
            }
        }

        if (Schema::hasTable('referral_withdraw_requests')) {
            DB::statement("UPDATE `referral_withdraw_requests` SET `currency` = '{$base}' WHERE `currency` IS NULL OR `currency` = ''");
        }

        if (Schema::hasTable('referrals')) {
            DB::statement("
                UPDATE `referrals`
                SET `referral_scheme` = JSON_SET(`referral_scheme`, '$.currency', '{$base}')
                WHERE JSON_VALID(`referral_scheme`)
                  AND JSON_TYPE(`referral_scheme`) = 'OBJECT'
                  AND (JSON_EXTRACT(`referral_scheme`, '$.currency') IS NULL
                       OR JSON_UNQUOTE(JSON_EXTRACT(`referral_scheme`, '$.currency')) = '')
            ");
        }

        if (Schema::hasTable('transactions')) {
            DB::statement("UPDATE `transactions` SET `transaction_currency` = '{$base}' WHERE CONVERT(`transaction_currency` USING utf8mb4) IN (SELECT CONVERT(`config_key` USING utf8mb4) FROM `config`)");
        }

        if (Schema::hasTable('nfc_card_order_transactions')) {
            DB::statement("UPDATE `nfc_card_order_transactions` SET `currency` = '{$base}' WHERE CONVERT(`currency` USING utf8mb4) IN (SELECT CONVERT(`config_key` USING utf8mb4) FROM `config`)");
        }

        if (Schema::hasTable('ai_credits_transactions')) {
            DB::statement("UPDATE `ai_credits_transactions` SET `currency` = '{$base}' WHERE CONVERT(`currency` USING utf8mb4) IN (SELECT CONVERT(`config_key` USING utf8mb4) FROM `config`)");
        }
    }

    /**
     * GoBizOriginal homepage copy for Smart Products.
     * Inserts default section rows (badge, title, desc) if missing,
     * preserving any existing edits.
     * Direct DB statements from gobiz_original_smart_products_migration.sql.
     */
    protected function gobizOriginalSmartProducts(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        $defaults = [
            'badge' => 'Smart Products',
            'title' => 'Smart NFC Products For Your Business',
            'desc'  => 'NFC cards, review stands, tags and more. Each one opens your business card, store or any link with a tap, and you can change it any time.',
        ];

        $home = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', 'home');
        $smartProducts = (clone $home)->where('section_name', 'smart_products')
            ->whereIn('section_title', array_keys($defaults));

        if (!array_diff_key($defaults, array_flip($smartProducts->pluck('section_title')->all()))) {
            return;
        }

        $pricingId = $home->where('section_name', 'pricing')->where('section_title', 'pricing_subtitle')
            ->orderBy('id')->value('id');

        if ($pricingId === null) {
            return;
        }

        $pricing = DB::table('pages')->where('id', $pricingId)->first();
        if ($pricing === null) {
            return;
        }

        $existing = $smartProducts->pluck('section_title')->all();
        $timestamp = now();
        $rows = [];

        foreach (array_diff_key($defaults, array_flip($existing)) as $field => $content) {
            $rows[] = [
                'template_id'     => 'GoBizOriginal',
                'page_name'       => 'home',
                'section_name'    => 'smart_products',
                'section_title'   => $field,
                'section_content' => $content,
                'title'           => $pricing->title,
                'description'     => $pricing->description,
                'keywords'        => $pricing->keywords,
                'status'          => 'active',
                'created_at'      => $timestamp,
                'updated_at'      => $timestamp,
            ];
        }

        if (!empty($rows)) {
            DB::table('pages')->insert($rows);
        }
    }

    /**
     * Ensure "personalized_link_excludes" table exists and default reserved keywords are seeded.
     * Direct DB statements from personalized_link_excludes_migration.sql.
     */
    protected function personalizedLinkExcludes(): void
    {
        if (!Schema::hasTable('personalized_link_excludes')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `personalized_link_excludes` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `keyword` VARCHAR(255) NOT NULL,
                    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `personalized_link_excludes_keyword_unique` (`keyword`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        $keywords = [
            'about-us', 'admin', 'ai-credit', 'ai-credits', 'amwalpay', 'api',
            'app-handoff', 'bcrypt-password-generator', 'biscolab-recaptcha',
            'blog', 'blogs', 'book-appointment', 'book-service', 'broadcasting',
            'cashfree', 'cashfree-payment-status', 'categories', 'check-password',
            'check-slug-availability', 'coinbase', 'contact', 'contact-us',
            'cookie-policy', 'css-beautifier', 'css-minifier', 'directory',
            'dns-lookup', 'download', 'dynamic-card', 'email', 'emojies',
            'esewa', 'faq', 'flutterwave-callback', 'flutterwave-payment-status',
            'generate-service-worker', 'get-available-time-slots', 'google-login',
            'html-beautifier', 'html-minifier', 'impersonate', 'in-app-purchase',
            'install', 'ip-lookup', 'iyzipay', 'iyzipay-payment-status',
            'js-beautifier', 'js-minifier', 'login', 'logout', 'lorem-generator',
            'maintenance', 'manifest.json', 'mark-offline-payment',
            'md5-password-generator', 'mercadopago-callback',
            'mercadopago-payment-failure', 'mercadopago-payment-pending',
            'mercadopago-payment-status', 'mollie-payment-status', 'moneroo',
            'nfc', 'nfc-cards', 'offline', 'order', 'p', 'paddle',
            'paddle-payment-status', 'paddle-payment-webhook', 'password',
            'payment', 'payment-flutterwave', 'payment-mercadopago',
            'payment-mollie', 'payment-offline', 'payment-paypal',
            'payment-paystack', 'payment-phonepe', 'payment-razorpay',
            'payment-status', 'payment-stripe', 'payment-toyyibpay',
            'paystack-payment', 'paytr', 'paytr-payment-failure',
            'paytr-payment-status', 'paytr-payment-webhook', 'phonepe-payment-status',
            'prepare-payment', 'privacy-policy', 'product', 'public',
            'qr-maker', 'random-password-generator', 'random-word-generator',
            'razorpay-payment-status', 'refund-policy', 'register',
            'return-policy', 'send-otp', 'sent-enquiry', 'sent-mail',
            'set-locale', 'shipping-policy', 'sign-in-with-google',
            'sslcommerz', 'storage', 'stripe-payment-cancel',
            'stripe-payment-status', 'subscribe', 'terms-and-conditions',
            'text-counter', 'toyyibpay-payment-status', 'toyyibpay-payment-success',
            'two-factor-authentication', 'user', 'verify-email',
            'verify-email-address', 'verify-two-factor-authentication',
            'whois-lookup', 'xendit', 'xendit-payment-failure',
            'xendit-payment-status', 'xendit-payment-webhook',
        ];

        $now = now();
        $rows = array_map(static fn(string $kw) => [
            'keyword'    => $kw,
            'is_default' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $keywords);

        DB::table('personalized_link_excludes')->insertOrIgnore($rows);
    }

    /**
     * Safely updates .env by removing old/duplicate S3 & R2 keys
     * and cleanly appending the standardized storage configuration at the bottom.
     */
    protected function updateEnvironmentFile(): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath) || !is_writable($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);
        if ($content === false) {
            return;
        }

        // Helper to extract an existing key's value to preserve user settings
        $extract = function (string $key, string $default = '') use ($content): string {
            if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=\s*(.*)$/m', $content, $matches)) {
                return trim($matches[1]);
            }
            return $default;
        };

        // Extract existing customer values if already configured
        $filesystemDriver = $extract('FILESYSTEM_DRIVER', 'local') ?: 'local';
        $accessKey        = $extract('AWS_ACCESS_KEY_ID', '');
        $secretKey        = $extract('AWS_SECRET_ACCESS_KEY', '');
        $region           = $extract('AWS_DEFAULT_REGION', 'us-east-1') ?: 'us-east-1';
        $bucket           = $extract('AWS_BUCKET', '');
        $endpoint         = $extract('AWS_ENDPOINT', '');
        $awsUrl           = $extract('AWS_URL', '');
        $usePathStyle     = $extract('AWS_USE_PATH_STYLE_ENDPOINT', 'false') ?: 'false';

        // Strip existing active keys
        $keys = [
            'FILESYSTEM_DRIVER',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_ENDPOINT',
            'AWS_URL',
            'AWS_USE_PATH_STYLE_ENDPOINT',
        ];

        foreach ($keys as $k) {
            $content = preg_replace('/^\s*' . preg_quote($k, '/') . '\s*=.*$/m', '', $content);
        }

        // Strip previous comment blocks if already present to prevent duplicates
        $commentPatterns = [
            '/^#\s*File Storage:.*$/mi',
            '/^#\s*---\s*AWS S3 Setup\s*---.*$/mi',
            '/^#\s*---\s*Cloudflare R2 Setup\s*---.*$/mi',
            '/^#\s*FILESYSTEM_DRIVER=.*$/mi',
            '/^#\s*AWS_[A-Z0-9_]+=.*$/mi',
        ];

        foreach ($commentPatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Normalize multiple blank lines down to a single blank line
        $content = preg_replace("/[\r\n]{3,}/", "\n\n", trim($content));

        // Format the standardized configuration block
        $block = <<<EOT


# File Storage: local | s3
FILESYSTEM_DRIVER={$filesystemDriver}

# --- AWS S3 Setup ---
# FILESYSTEM_DRIVER=s3
# AWS_ACCESS_KEY_ID=your-aws-access-key-id
# AWS_SECRET_ACCESS_KEY=your-aws-secret-access-key
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-s3-bucket-name
# AWS_URL=https://your-s3-bucket-name.s3.amazonaws.com
# AWS_ENDPOINT=
# AWS_USE_PATH_STYLE_ENDPOINT=false

# --- Cloudflare R2 Setup ---
# FILESYSTEM_DRIVER=s3
# AWS_ACCESS_KEY_ID=your-r2-token-access-key-id
# AWS_SECRET_ACCESS_KEY=your-r2-token-secret-access-key
# AWS_DEFAULT_REGION=auto
# AWS_BUCKET=your-r2-bucket-name
# AWS_ENDPOINT=https://<your-cloudflare-account-id>.r2.cloudflarestorage.com
# AWS_URL=https://pub-<unique-id>.r2.dev
# AWS_USE_PATH_STYLE_ENDPOINT=true

AWS_ACCESS_KEY_ID={$accessKey}
AWS_SECRET_ACCESS_KEY={$secretKey}
AWS_DEFAULT_REGION={$region}
AWS_BUCKET={$bucket}
AWS_ENDPOINT={$endpoint}
AWS_URL={$awsUrl}
AWS_USE_PATH_STYLE_ENDPOINT={$usePathStyle}
EOT;

        file_put_contents($envPath, $content . $block . "\n");
    }
}
