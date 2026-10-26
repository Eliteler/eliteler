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

namespace App\Http\Controllers\User\Store\Edit;

use App\BusinessCard;
use App\StoreProduct;
use App\StoreCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UpdateStoreProductImportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the product import page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Product import with variations support
    public function importProducts(Request $request)
    {
        // Validate file
        $request->validate([
            'csv_file' => 'required|file|mimetypes:text/csv,application/csv,text/plain',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $firstLine = fgets(fopen($path, 'r'));

        // Detect delimiter (tab or comma)
        $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter, '"');

        // Required headers for standard simple products
        $requiredHeaders = [
            "category_id",
            "badge",
            "product_image",
            "product_name",
            "product_short_description",
            "product_description",
            "regular_price",
            "sales_price",
            "product_status"
        ];

        $header = null;
        $errors = [];
        $rowNumber = 1;

        foreach ($file as $row) {
            $rowNumber++;

            // Skip invalid or empty rows
            if (!is_array($row) || $row === [null] || (count($row) === 1 && trim($row[0]) === '')) {
                continue;
            }

            // Handle headers
            if (!$header) {
                // Normalize headers: trim, lowercase, remove UTF-8 BOM
                $header = array_map(function ($h) {
                    $h = trim($h);
                    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                    return strtolower($h);
                }, $row);

                // Check if required headers exist
                $missing = array_diff($requiredHeaders, $header);
                if (!empty($missing)) {
                    return response()->json([
                        'success' => false,
                        'message' => trans('CSV is missing required columns: ' . implode(', ', $missing)),
                    ]);
                }

                continue;
            }

            // Row column mismatch
            if (count($row) !== count($header)) {
                $errors[] = trans("Row {$rowNumber}: Column mismatch.");
                continue;
            }

            $rowData = array_combine($header, $row);

            // Validate required fields
            foreach ($requiredHeaders as $field) {
                if (!isset($rowData[$field]) || trim($rowData[$field]) === '') {
                    $errors[] = trans("Row {$rowNumber}: Missing value for '{$field}'.");
                    continue 2;
                }
            }

            // Check category_id exists
            if (!StoreCategory::where('category_id', $rowData['category_id'])->exists()) {
                $errors[] = trans("Row {$rowNumber}: Category ID '{$rowData['category_id']}' does not exist.");
                continue;
            }

            $generatedProductId = uniqid();
            $hasVariants = 0;
            $variantsToInsert = [];

            // Check if CSV contains optional 'variants' column (Format: Name:RegularPrice:SalesPrice:Status | ...)
            if (isset($rowData['variants']) && !empty(trim($rowData['variants']))) {
                $variantEntries = explode('|', $rowData['variants']);
                foreach ($variantEntries as $vEntry) {
                    $vParts = array_map('trim', explode(':', $vEntry));
                    if (count($vParts) >= 2) {
                        $vName = $vParts[0];
                        $vRegular = !empty($vParts[1]) ? (float) $vParts[1] : null;
                        $vSales = isset($vParts[2]) && !empty($vParts[2]) ? (float) $vParts[2] : (float) $vParts[1];
                        $vStatus = isset($vParts[3]) && !empty($vParts[3]) ? strtolower($vParts[3]) : 'instock';

                        $variantsToInsert[] = [
                            'product_id'    => $generatedProductId,
                            'variant_name'  => $vName,
                            'regular_price' => $vRegular,
                            'sales_price'   => $vSales,
                            'status'        => $vStatus,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ];
                    }
                }
                if (!empty($variantsToInsert)) {
                    $hasVariants = 1;
                }
            }

            // Insert into store_products
            DB::table('store_products')->insert([
                'card_id'                   => $request->card_id,
                'product_id'                => $generatedProductId,
                'category_id'               => $rowData['category_id'],
                'badge'                     => $rowData['badge'],
                'product_image'             => $rowData['product_image'],
                'product_name'              => $rowData['product_name'],
                'product_short_description' => $rowData['product_short_description'],
                'product_description'       => $rowData['product_description'],
                'regular_price'             => !empty($rowData['regular_price']) ? $rowData['regular_price'] : null,
                'sales_price'               => $rowData['sales_price'],
                'product_status'            => $rowData['product_status'],
                'has_variants'              => $hasVariants,
                'status'                    => '1',
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);

            // Insert variants into store_product_variants if available
            if (!empty($variantsToInsert)) {
                DB::table('store_product_variants')->insert($variantsToInsert);
            }
        }

        // Return errors or success
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => implode('<br>', $errors),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => trans('Product import successful.'),
        ]);
    }

    // Export products with variations
    public function exportProducts(Request $request, $id)
    {
        $storeId = $id;

        // Fetch products along with left-joined variants
        $products = DB::table('store_products')
            ->where('card_id', $storeId)
            ->get();

        // Fetch all variants belonging to this store's products
        $productIds = $products->pluck('product_id')->toArray();
        $allVariants = DB::table('store_product_variants')
            ->whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        // Fetch and sanitize store name for filename
        $storeCard = BusinessCard::where('card_id', $storeId)->first(['card_url']);
        $storeName = $storeCard ? $storeCard->card_url : 'store';
        $safeStoreName = preg_replace('/[^A-Za-z0-9_-]/', '_', $storeName);
        $filename = $safeStoreName . '_products.csv';

        try {
            // Define CSV headers
            $columns = [
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
                'variants', // <-- Compact format: Name:RegPrice:SalesPrice:Status
                'status',
                'created_at',
                'updated_at'
            ];

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return new StreamedResponse(function () use ($products, $allVariants, $columns) {
                $handle = fopen('php://output', 'w');

                // Write header row
                fputcsv($handle, $columns);

                // Write product rows
                foreach ($products as $product) {
                    $row = [];

                    // Format variants into a pipe-separated string
                    $variantsStr = '';
                    if (!empty($product->has_variants) && isset($allVariants[$product->product_id])) {
                        $vList = [];
                        foreach ($allVariants[$product->product_id] as $v) {
                            $vList[] = "{$v->variant_name}:{$v->regular_price}:{$v->sales_price}:{$v->status}";
                        }
                        $variantsStr = implode(' | ', $vList);
                    }

                    foreach ($columns as $column) {
                        if ($column === 'variants') {
                            $row[] = $variantsStr;
                        } else {
                            $row[] = $product->$column ?? '';
                        }
                    }

                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('Failed to export products.'),
            ]);
        }
    }
}
