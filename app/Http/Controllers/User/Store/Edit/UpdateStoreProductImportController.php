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
use Iyzipay\Model\Card;
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

    // Product import
    // Product import
    public function importProducts(Request $request)
    {
        // Validate file
        $request->validate([
            'csv_file' => 'required|file|mimetypes:text/csv,application/csv',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $firstLine = fgets(fopen($path, 'r'));

        // Detect delimiter (tab or comma)
        $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter, '"');

        // Required headers (expected normalized format)
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
                // Normalize headers: trim, lowercase, remove BOM
                $header = array_map(function ($h) {
                    $h = trim($h);
                    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // remove BOM
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
                $errors[] = trans("Row {rowNumber}: Column mismatch.");
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
                $errors[] = trans("Row {rowNumber}: Category ID '{categoryId}' does not exist.");
                continue;
            }

            // Create product
            StoreProduct::create([
                'card_id' => $request->card_id,
                'product_id' => uniqid(), // Generate unique ID
                'category_id' => $rowData['category_id'],
                'badge' => $rowData['badge'],
                'product_image' => $rowData['product_image'],
                'product_name' => $rowData['product_name'],
                'product_short_description' => $rowData['product_short_description'],
                'product_description' => $rowData['product_description'],
                'regular_price' => $rowData['regular_price'],
                'sales_price' => $rowData['sales_price'],
                'product_status' => $rowData['product_status'],
            ]);
        }

        // Return result
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

    // Export products
    public function exportProducts(Request $request, $id)
    {
        // Store ID
        $storeId = $id;

        // Fetch products
        $products = StoreProduct::where('card_id', $storeId)->get([
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
            'status',
            'created_at',
            'updated_at'
        ]);

        // Fetch and sanitize store name for filename
        $storeCard = BusinessCard::where('card_id', $storeId)->first(['card_url']);
        $storeName = $storeCard ? $storeCard->card_url : 'store';
        $safeStoreName = preg_replace('/[^A-Za-z0-9_-]/', '_', $storeName);
        $filename = $safeStoreName . '_products.csv';

        try {

            // Define CSV headers/columns
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
                'status',
                'created_at',
                'updated_at'
            ];

            // Define HTTP headers for the response
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            // Stream the CSV file
            return new StreamedResponse(function () use ($products, $columns) {
                $handle = fopen('php://output', 'w');

                // Write CSV header row
                fputcsv($handle, $columns);

                // Write product rows
                foreach ($products as $product) {
                    $row = [];
                    foreach ($columns as $column) {
                        $row[] = $product->$column;
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
