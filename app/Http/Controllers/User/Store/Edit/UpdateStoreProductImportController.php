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
use App\Http\Controllers\Controller;
use App\Support\Store\ProductCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Product import with variations support (format and checks: App\Support\Store\ProductCsv)
    public function importProducts(Request $request)
    {
        // Validate file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        // Only into the user's own store
        $store = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->card_id)->where('card_type', 'store')->first();
        if (! $store) {
            return response()->json([
                'success' => false,
                'message' => trans('Store not found!'),
            ]);
        }

        $planDetails = !empty(Auth::user()->plan_details) ? json_decode(Auth::user()->plan_details) : null;
        $result = ProductCsv::import($request->file('csv_file')->getRealPath(), $store, $planDetails);

        // Return errors (one per line) or success
        if ($result['errors'] !== []) {
            return response()->json([
                'success' => false,
                'message' => implode("\n", $result['errors']),
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
        // Only the user's own store
        $store = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->where('card_type', 'store')->first();
        if (! $store) {
            abort(404);
        }

        return response(ProductCsv::export($store), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . ProductCsv::filename($store) . '"',
        ]);
    }

    // Sample file for the import, made from this store's first category
    public function sampleProducts(Request $request, $id)
    {
        $store = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->where('card_type', 'store')->first();
        if (! $store) {
            abort(404);
        }

        return response(ProductCsv::sample($store), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_import_data_products.csv"',
        ]);
    }
}
