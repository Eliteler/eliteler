<?php

namespace App\Support\Store;

use App\BusinessCard;
use App\StoreCategory;
use App\StoreProduct;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

/**
 * Store product CSV import/export, shared by the Blade editor
 * (User\Store\Edit\UpdateStoreProductImportController) and the React user panel
 * (ModernDashboard StoreProductImportController), so files move between both.
 *
 * Variations go in one "variants" column: "Name:RegularPrice:SalesPrice:Status | …".
 * An import checks every row first and saves nothing when any row is wrong;
 * categories must belong to the store, the plan limit applies, descriptions are cleaned.
 */
class ProductCsv
{
    /** Columns every import file needs. */
    public const REQUIRED = [
        'category_id',
        'badge',
        'product_image',
        'product_name',
        'product_short_description',
        'product_description',
        'regular_price',
        'sales_price',
        'product_status',
    ];

    /** Columns written by the export. */
    public const EXPORT = [
        'card_id',
        'product_id',
        'category_id',
        'badge',
        'product_image',
        'product_name',
        'product_short_description',
        'product_description',
        'regular_price',
        'sales_price',
        'product_status',
        'has_variants',
        'variants',
        'status',
        'created_at',
        'updated_at',
    ];

    public const STATUSES = ['instock', 'outstock'];

    public const MAX_ROWS = 1000;

    public const MAX_VARIANTS = 50;

    /** Errors listed back to the user; the rest are counted. */
    public const MAX_ERRORS = 20;

    /** The store's products as CSV text. */
    public static function export(BusinessCard $store): string
    {
        $products = DB::table('store_products')->where('card_id', $store->card_id)->orderBy('id')->get();
        $variants = DB::table('store_product_variants')
            ->whereIn('product_id', $products->pluck('product_id'))
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        $rows = [self::EXPORT];
        foreach ($products as $product) {
            $options = (int) $product->has_variants === 1 ? $variants->get($product->product_id, collect()) : collect();
            $row     = [];

            foreach (self::EXPORT as $column) {
                $row[] = $column === 'variants'
                    ? $options->map(fn($v) => "{$v->variant_name}:{$v->regular_price}:{$v->sales_price}:{$v->status}")->implode(' | ')
                    : ($product->$column ?? '');
            }

            $rows[] = $row;
        }

        return self::toCsv($rows);
    }

    /** A sample file to fill in, using the store's first category. */
    public static function sample(BusinessCard $store): string
    {
        $category = StoreCategory::where('store_id', $store->card_id)->where('status', '1')->value('category_id') ?? 'CATEGORY_ID';

        return self::toCsv([
            [...self::REQUIRED, 'variants'],
            [$category, 'New', 'storage/images/product.png', 'Classic Mug', 'Ceramic mug, 350 ml', '<p>Dishwasher safe ceramic mug.</p>', '15', '12', 'instock', ''],
            [$category, 'Best Seller', 'storage/images/t-shirt.png', 'Cotton T-Shirt', 'Soft cotton tee', '<p>100% cotton, regular fit.</p>', '25', '', 'instock', 'Small:25:19:instock | Medium:25:21:instock | Large::23:outstock'],
        ]);
    }

    /** Safe download file name for the store's export. */
    public static function filename(BusinessCard $store): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '_', $store->card_url ?: 'store') . '_products.csv';
    }

    /**
     * Import a CSV file into the store. Nothing is saved when there are errors.
     *
     * @return array{imported: int, errors: array<int, string>}
     */
    public static function import(string $path, BusinessCard $store, ?object $plan, bool $updateExisting = true): array
    {
        [$rows, $errors] = self::read($path, $store);

        if ($errors === [] && $rows === []) {
            $errors[] = __('The file has no products.');
        }

        // Fetch existing store product IDs to determine updates vs inserts
        $existingProductIds = StoreProduct::where('card_id', $store->card_id)->pluck('product_id')->flip();

        // Calculate only new products against plan limit
        $newProductsCount = 0;
        foreach ($rows as $row) {
            $isExisting = $updateExisting && ! empty($row['product_id']) && $existingProductIds->has($row['product_id']);
            if (! $isExisting) {
                $newProductsCount++;
            }
        }

        // Plan limit, counting the new products to be added to the store
        $limit = $plan ? ((int) $plan->no_of_store_products == 999 ? 999999 : (int) $plan->no_of_store_products) : 0;
        $count = StoreProduct::where('card_id', $store->card_id)->where('status', '1')->count();
        if ($errors === [] && $count + $newProductsCount > $limit) {
            $errors[] = __('Your plan allows :limit products and this store has :count, so up to :left more can be imported.', [
                'limit' => $limit,
                'count' => $count,
                'left'  => max(0, $limit - $count),
            ]);
        }

        if ($errors !== []) {
            $shown = array_slice($errors, 0, self::MAX_ERRORS);
            if (count($errors) > self::MAX_ERRORS) {
                $shown[] = __('…and :count more.', ['count' => count($errors) - self::MAX_ERRORS]);
            }

            return ['imported' => 0, 'errors' => $shown];
        }

        DB::transaction(function () use ($rows, $store, $existingProductIds, $updateExisting) {
            foreach ($rows as $row) {
                $isUpdate = $updateExisting && ! empty($row['product_id']) && $existingProductIds->has($row['product_id']);
                $productId = $isUpdate ? $row['product_id'] : uniqid();

                if ($isUpdate) {
                    // Update existing product
                    DB::table('store_products')
                        ->where('card_id', $store->card_id)
                        ->where('product_id', $productId)
                        ->update([
                            'category_id'               => $row['category_id'],
                            'badge'                     => $row['badge'],
                            'product_image'             => $row['product_image'],
                            'product_name'              => $row['product_name'],
                            'product_short_description' => $row['product_short_description'],
                            'product_description'       => Purifier::clean($row['product_description']),
                            'regular_price'             => $row['regular_price'],
                            'sales_price'               => $row['sales_price'],
                            'product_status'            => $row['product_status'],
                            'has_variants'              => $row['variants'] === [] ? 0 : 1,
                            'updated_at'                => now(),
                        ]);

                    // Remove existing variants before re-inserting
                    DB::table('store_product_variants')->where('product_id', $productId)->delete();
                } else {
                    // Insert new product
                    DB::table('store_products')->insert([
                        'card_id'                   => $store->card_id,
                        'product_id'                => $productId,
                        'category_id'               => $row['category_id'],
                        'badge'                     => $row['badge'],
                        'product_image'             => $row['product_image'],
                        'product_name'              => $row['product_name'],
                        'product_short_description' => $row['product_short_description'],
                        'product_description'       => Purifier::clean($row['product_description']),
                        'regular_price'             => $row['regular_price'],
                        'sales_price'               => $row['sales_price'],
                        'product_status'            => $row['product_status'],
                        'has_variants'              => $row['variants'] === [] ? 0 : 1,
                        'status'                    => '1',
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]);
                }

                if ($row['variants'] !== []) {
                    DB::table('store_product_variants')->insert(array_map(fn($variant) => [
                        'product_id'    => $productId,
                        'variant_name'  => $variant['name'],
                        'regular_price' => $variant['regular_price'],
                        'sales_price'   => $variant['sales_price'],
                        'status'        => $variant['status'],
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ], $row['variants']));
                }
            }
        });

        return ['imported' => count($rows), 'errors' => []];
    }

    private static function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '\\');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Read and check every row: comma or tab separated, header names trimmed and
     * lower-cased, UTF-8 BOM removed (as the original Blade import read them).
     *
     * @return array{0: array<int, array>, 1: array<int, string>} valid rows, errors
     */
    private static function read(string $path, BusinessCard $store): array
    {
        $handle    = fopen($path, 'r');
        $firstLine = (string) fgets($handle);
        fclose($handle);

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD);
        $file->setCsvControl(str_contains($firstLine, "\t") ? "\t" : ',', '"', '\\');

        $categories = StoreCategory::where('store_id', $store->card_id)->pluck('category_id')->flip();
        $header     = null;
        $rows       = [];
        $errors     = [];

        foreach ($file as $index => $row) {
            $line = $index + 1;

            if (! is_array($row) || $row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                continue;
            }

            if ($header === null) {
                $header  = array_map(fn($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $row);
                $missing = array_diff(self::REQUIRED, $header);
                if ($missing !== []) {
                    return [[], [__('The file is missing these columns: :columns', ['columns' => implode(', ', $missing)])]];
                }

                continue;
            }

            if (count($rows) + count($errors) >= self::MAX_ROWS) {
                return [[], [__('A file can have up to :max products.', ['max' => self::MAX_ROWS])]];
            }

            if (count($row) !== count($header)) {
                $errors[] = __('Row :row: it has :found columns instead of :expected.', ['row' => $line, 'found' => count($row), 'expected' => count($header)]);

                continue;
            }

            $data    = array_map(fn($value) => trim((string) $value), array_combine($header, $row));
            $problem = self::check($data, $categories);

            if ($problem !== null) {
                $errors[] = __('Row :row: :problem', ['row' => $line, 'problem' => $problem]);

                continue;
            }

            $variants = self::variants($data['variants'] ?? '');
            if (is_string($variants)) {
                $errors[] = __('Row :row: :problem', ['row' => $line, 'problem' => $variants]);

                continue;
            }

            $rows[] = [
                'product_id'                => ! empty($data['product_id']) ? $data['product_id'] : null,
                'category_id'               => $data['category_id'],
                'badge'                     => $data['badge'] !== '' ? $data['badge'] : null,
                'product_image'             => $data['product_image'],
                'product_name'              => $data['product_name'],
                'product_short_description' => $data['product_short_description'],
                'product_description'       => $data['product_description'],
                'regular_price'             => $data['regular_price'] !== '' ? (float) $data['regular_price'] : null,
                // With variations, an empty sales price takes the first one's (as in the product form)
                'sales_price'               => $data['sales_price'] !== '' ? (float) $data['sales_price'] : $variants[0]['sales_price'],
                'product_status'            => $data['product_status'],
                'variants'                  => $variants,
            ];
        }

        return [$rows, $errors];
    }

    /** What's wrong with one product row, or null. */
    private static function check(array $data, $categories): ?string
    {
        foreach (['category_id', 'product_image', 'product_name', 'product_short_description', 'product_description', 'product_status'] as $field) {
            if ($data[$field] === '') {
                return __('“:field” is empty.', ['field' => $field]);
            }
        }

        if (! $categories->has($data['category_id'])) {
            return __('category “:id” is not a category of this store.', ['id' => $data['category_id']]);
        }
        if (mb_strlen($data['product_name']) > 255) {
            return __('the product name is longer than 255 characters.');
        }
        if (mb_strlen($data['badge']) > 191) {
            return __('the badge is longer than 191 characters.');
        }
        if (! in_array($data['product_status'], self::STATUSES, true)) {
            return __('product_status must be “instock” or “outstock”.');
        }
        if ($data['regular_price'] !== '' && (! is_numeric($data['regular_price']) || $data['regular_price'] < 0)) {
            return __('regular_price must be a number.');
        }
        if ($data['sales_price'] === '' && trim($data['variants'] ?? '') === '') {
            return __('“:field” is empty.', ['field' => 'sales_price']);
        }
        if ($data['sales_price'] !== '' && (! is_numeric($data['sales_price']) || $data['sales_price'] < 0)) {
            return __('sales_price must be a number.');
        }

        return null;
    }

    /**
     * The "variants" cell as rows, or an error. "Name:Regular:Sales:Status", separated
     * by "|". As in the original Blade import, "Name:Price" uses the price for both,
     * and the status defaults to in stock.
     *
     * @return array<int, array{name: string, regular_price: ?float, sales_price: float, status: string}>|string
     */
    private static function variants(string $cell): array|string
    {
        if (trim($cell) === '') {
            return [];
        }

        $variants = [];
        foreach (explode('|', $cell) as $entry) {
            $parts = array_map('trim', explode(':', $entry));
            $name  = $parts[0] ?? '';

            if (count($parts) < 2 || count($parts) > 4 || $name === '') {
                return __('variation “:entry” should look like Name:RegularPrice:SalesPrice:Status.', ['entry' => trim($entry)]);
            }
            if (mb_strlen($name) > 191) {
                return __('variation name “:name” is longer than 191 characters.', ['name' => $name]);
            }

            $regular = $parts[1];
            $sales   = ($parts[2] ?? '') !== '' ? $parts[2] : $parts[1];
            $status  = ($parts[3] ?? '') !== '' ? strtolower($parts[3]) : 'instock';

            if (($regular !== '' && ! is_numeric($regular)) || ! is_numeric($sales) || $sales < 0 || ($regular !== '' && $regular < 0)) {
                return __('variation “:name” needs a number as its price.', ['name' => $name]);
            }
            if (! in_array($status, self::STATUSES, true)) {
                return __('variation “:name” status must be “instock” or “outstock”.', ['name' => $name]);
            }

            $variants[] = [
                'name'          => $name,
                'regular_price' => $regular !== '' ? (float) $regular : null,
                'sales_price'   => (float) $sales,
                'status'        => $status,
            ];
        }

        if (count($variants) > self::MAX_VARIANTS) {
            return __('a product can have up to :max variations.', ['max' => self::MAX_VARIANTS]);
        }

        return $variants;
    }
}
