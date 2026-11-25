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

namespace App\Support\SmartProducts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Smart Products schema (evolution of the NFC card module).
 *
 * Every step checks before it changes anything, so this is safe to run on
 * fresh installs (migration), on updates (GoBizUpdater) and more than once.
 * Keep it in step with smart_products_migration.sql in the project root.
 */
class SmartProductsSchema
{
    public static function up(): void
    {
        self::productTypes();
        self::designs();
        self::keys();
        self::orders();
        self::histories();
        self::orderAssets();
        self::plans();
        self::config();
        self::seedProducts();
        self::backfillImageAssets();
    }

    /** Product fields on nfc_card_designs. Defaults keep old designs as NFC cards. */
    private static function designs(): void
    {
        $columns = [
            'product_type'      => fn (Blueprint $t) => $t->string('product_type', 50)->default('nfc_card')->after('nfc_card_name'),
            'short_description' => fn (Blueprint $t) => $t->text('short_description')->nullable()->after('product_type'),
            'gallery_images'    => fn (Blueprint $t) => $t->text('gallery_images')->nullable()->after('nfc_card_back_image'),
            'compare_at_price'  => fn (Blueprint $t) => $t->decimal('compare_at_price', 10, 2)->nullable()->after('nfc_card_price'),
            'generates_keys'    => fn (Blueprint $t) => $t->boolean('generates_keys')->default(true)->after('available_stocks'),
            'allow_logo_upload' => fn (Blueprint $t) => $t->boolean('allow_logo_upload')->default(true)->after('generates_keys'),
            'default_link_type' => fn (Blueprint $t) => $t->string('default_link_type', 20)->default('vcard')->after('allow_logo_upload'),
            // Image assets customers upload for printing: [{key, name, resolution, required}]
            'image_assets'      => fn (Blueprint $t) => $t->text('image_assets')->nullable()->after('allow_logo_upload'),
            'sort_order'        => fn (Blueprint $t) => $t->integer('sort_order')->default(0)->after('default_link_type'),
        ];

        self::addColumns('nfc_card_designs', $columns);
    }

    /** Chip destinations and scan counts on nfc_card_keys. */
    private static function keys(): void
    {
        $columns = [
            // Used by vouchers since v28 but never in a Laravel migration (fresh installs only).
            'associated_plan_id'     => fn (Blueprint $t) => $t->string('associated_plan_id')->nullable()->after('card_id'),
            'associated_nfc_card_id' => fn (Blueprint $t) => $t->string('associated_nfc_card_id')->nullable()->after('associated_plan_id'),
            'key_status'             => fn (Blueprint $t) => $t->string('key_status')->default('expired')->after('status'),
            // Smart Products
            'link_type'              => fn (Blueprint $t) => $t->string('link_type', 20)->default('vcard')->after('link_status'),
            'link_target'            => fn (Blueprint $t) => $t->text('link_target')->nullable()->after('link_type'),
            'label'                  => fn (Blueprint $t) => $t->string('label', 100)->nullable()->after('link_target'),
            'scan_count'             => fn (Blueprint $t) => $t->unsignedInteger('scan_count')->default(0)->after('label'),
            'last_scanned_at'        => fn (Blueprint $t) => $t->dateTime('last_scanned_at')->nullable()->after('scan_count'),
            // Owner (users.id): set when keys are made for an order or activated, so a key
            // pointing to a URL (no business card) still belongs to someone
            'user_id'                => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->index()->after('nfc_card_key_id'),
        ];

        self::addColumns('nfc_card_keys', $columns);
    }

    private static function orders(): void
    {
        $columns = [
            // Used by the NFC Writer API but never in a Laravel migration (fresh installs only).
            'is_nfc_written' => fn (Blueprint $t) => $t->boolean('is_nfc_written')->default(false)->after('order_status'),
            // NFC Writer app: card was made permanently read-only when written
            'is_locked'      => fn (Blueprint $t) => $t->boolean('is_locked')->default(false)->after('is_nfc_written'),
            // Smart Products
            'shipped_at'     => fn (Blueprint $t) => $t->dateTime('shipped_at')->nullable()->after('is_nfc_written'),
            'delivered_at'   => fn (Blueprint $t) => $t->dateTime('delivered_at')->nullable()->after('shipped_at'),
        ];

        self::addColumns('nfc_card_orders', $columns);
    }

    /** Order tracking timeline. */
    private static function histories(): void
    {
        if (Schema::hasTable('nfc_card_order_histories')) {
            return;
        }

        Schema::create('nfc_card_order_histories', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('nfc_card_order_id', 191)->index();
            $t->string('order_status', 50);
            $t->text('message')->nullable();
            $t->string('tracking_number', 191)->nullable();
            $t->string('courier_partner', 191)->nullable();
            $t->boolean('notified')->default(false);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    /** Files customers upload per image asset of an order (one per asset; uploading again replaces it). */
    private static function orderAssets(): void
    {
        if (Schema::hasTable('nfc_card_order_assets')) {
            return;
        }

        Schema::create('nfc_card_order_assets', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('nfc_card_order_id', 191)->index();
            $t->string('asset_key', 20);
            $t->string('file_path', 255);
            $t->string('file_type', 10);
            $t->string('original_name', 191)->nullable();
            $t->unsignedInteger('size')->default(0);
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->timestamps();
            $t->unique(['nfc_card_order_id', 'asset_key']);
        });
    }

    /**
     * Products that take a logo but have no asset list yet get one asset
     * "Logo" (key "logo"), so old orders' logos keep showing as that asset.
     */
    private static function backfillImageAssets(): void
    {
        if (! Schema::hasTable('nfc_card_designs') || ! Schema::hasColumn('nfc_card_designs', 'image_assets')) {
            return;
        }

        DB::table('nfc_card_designs')
            ->where('allow_logo_upload', 1)
            ->where(fn ($q) => $q->whereNull('image_assets')->orWhere('image_assets', '')->orWhere('image_assets', '[]'))
            ->update(['image_assets' => json_encode([['key' => 'logo', 'name' => 'Logo', 'resolution' => '', 'required' => false]])]);
    }

    /** Whether "Smart Products" is listed in a plan's pricing points. */
    private static function plans(): void
    {
        self::addColumns('plans', [
            'show_nfc_card_feature' => fn (Blueprint $t) => $t->boolean('show_nfc_card_feature')->default(true)->after('nfc_card'),
        ]);
    }

    private static function config(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        foreach (['smart_product_shipping_fee' => '0', 'smart_product_free_shipping_above' => '0'] as $key => $value) {
            if (! DB::table('config')->where('config_key', $key)->exists()) {
                DB::table('config')->insert([
                    'config_key'   => $key,
                    'config_value' => $value,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    public static function productTypes(): void
    {
        if (! Schema::hasTable('smart_product_types')) {
            Schema::create('smart_product_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100)->unique();
                $table->timestamps();
            });
        }

        DB::transaction(function () {
            self::seedProductTypes(onlyOnInitialization: true);

            if (Schema::hasTable('nfc_card_designs') && Schema::hasColumn('nfc_card_designs', 'product_type')) {
                $generic = SmartProductCatalog::GENERIC_TYPE;
                $codes = DB::table('smart_product_types')->lockForUpdate()->pluck('code');
                DB::table('nfc_card_designs')
                    ->where(fn ($query) => $query->whereNull('product_type')->orWhere('product_type', ''))
                    ->update(['product_type' => $codes->contains('nfc_card') ? 'nfc_card' : $generic]);

                DB::table('nfc_card_designs')->whereNotIn('product_type', $codes)->update(['product_type' => $generic]);
            }
        });
    }

    public static function seedProductTypes(bool $onlyOnInitialization = false): void
    {
        DB::transaction(function () use ($onlyOnInitialization) {
            $types = DB::table('smart_product_types');
            $timestamp = now();

            $initialized = $types->insertOrIgnore([
                'code' => SmartProductCatalog::GENERIC_TYPE, 'name' => 'Generic', 'created_at' => $timestamp, 'updated_at' => $timestamp,
            ]);

            // Migrations import the legacy options only when Generic is first created.
            if ($onlyOnInitialization && ! $initialized) {
                return;
            }

            $legacy = [];
            foreach (SmartProductCatalog::LEGACY_TYPES as $code => $name) {
                $legacy[] = ['code' => $code, 'name' => $name, 'created_at' => $timestamp, 'updated_at' => $timestamp];
            }

            $types->insertOrIgnore($legacy);
        });
    }

    /**
     * Default catalogue. Inserted inactive (status 0) with price 0, so customers
     * see nothing new until the admin sets a price and photos and activates it.
     */
    private static function seedProducts(): void
    {
        if (! Schema::hasTable('nfc_card_designs') || ! Schema::hasTable('smart_product_types')) {
            return;
        }

        $typeCodes = DB::table('smart_product_types')->pluck('code')->flip();

        foreach (SmartProductCatalog::defaults() as $i => $product) {
            if (DB::table('nfc_card_designs')->where('nfc_card_id', $product['id'])->exists()) {
                continue;
            }

            $image = 'img/smart-products/' . $product['image'];

            DB::table('nfc_card_designs')->insert([
                'nfc_card_id'          => $product['id'],
                'nfc_card_name'        => $product['name'],
                'product_type'         => $typeCodes->has($product['type']) ? $product['type'] : SmartProductCatalog::GENERIC_TYPE,
                'short_description'    => $product['short'],
                'nfc_card_description' => $product['description'],
                'nfc_card_front_image' => $image,
                'nfc_card_back_image'  => $image,
                'gallery_images'       => json_encode([$image], JSON_UNESCAPED_SLASHES),
                'nfc_card_price'       => 0,
                'available_stocks'     => 0,
                'generates_keys'       => 1,
                'allow_logo_upload'    => $product['logo'] ? 1 : 0,
                'default_link_type'    => $product['link'],
                'sort_order'           => ($i + 1) * 10,
                'status'               => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    /** @param array<string, callable(Blueprint): mixed> $columns */
    private static function addColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $name => $define) {
            if (! Schema::hasColumn($table, $name)) {
                Schema::table($table, fn (Blueprint $t) => $define($t));
            }
        }
    }
}
