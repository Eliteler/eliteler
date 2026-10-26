<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\User\Store\Edit;

use App\BusinessCard;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Medias;
use App\Setting;
use App\StoreCategory;
use App\StoreProduct;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Products datatable and management view
    public function editProducts(Request $request, $id)
    {
        // Get plan details
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Queries
            if ($request->ajax()) {
                $products = StoreProduct::with('product_category')
                    ->where('card_id', $id)
                    ->orderBy('id', 'desc');

                return DataTables::of($products)
                    ->addIndexColumn()
                    ->addColumn('product_category', function ($product) {
                        $categoryName = $product->product_category && $product->product_category->category_name
                            ? __($product->product_category->category_name)
                            : __('Category not found');

                        return '<span class="badge bg-slate-100 text-slate-700 text-capitalize fw-semibold">' . $categoryName . '</span>';
                    })
                    ->addColumn('product_image', function ($product) {
                        if (empty($product->product_image)) {
                            return '<span class="avatar avatar-md rounded bg-primary-lt">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="15" y1="8" x2="15.01" y2="8" /><rect x="4" y="4" width="16" height="16" rx="3" /><path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" /><path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" /></svg>
                </span>';
                        }

                        $productImages = array_filter(array_map('trim', explode(',', $product->product_image)));
                        $firstImage = asset($productImages[0] ?? '');
                        $totalCount = count($productImages);

                        // Position badge on top-right floating corner without covering the picture
                        $badgeHtml = '';
                        if ($totalCount > 1) {
                            $extraCount = $totalCount - 1;
                            $badgeHtml = '<span class="badge bg-primary text-white position-absolute top-0 start-100 translate-middle shadow-sm fw-bold" style="font-size: 0.65rem; border-radius: 10px; padding: 2px 5px; z-index: 2; border: 1.5px solid #ffffff;" title="' . $totalCount . ' images">+' . $extraCount . '</span>';
                        }

                        return '<div class="position-relative d-inline-block my-1" style="width: 44px; height: 44px;">
                <img src="' . $firstImage . '" class="rounded border" alt="' . e($product->product_name ?? 'Product') . '" style="width: 44px; height: 44px; object-fit: cover; display: block;" onerror="this.src=\'' . asset('img/default-cover.png') . '\';"/>
                ' . $badgeHtml . '
            </div>';
                    })
                    ->addColumn('product_name', function ($product) {
                        $raw = $product->product_name ?? '';
                        $clean = strip_tags($raw);
                        $utf8 = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');

                        $short = Str::limit(e($utf8), 30);

                        if (Str::length($utf8) > 30) {
                            return '<span class="fw-bold text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($utf8) . '">' . $short . '</span>';
                        } else {
                            return '<span class="fw-bold text-dark">' . $short . '</span>';
                        }
                    })
                    ->addColumn('product_description', function ($product) {
                        $raw = $product->product_description ?? '';
                        $clean = strip_tags($raw);
                        $utf8 = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');

                        $short = Str::limit(e($utf8), 30);

                        if (Str::length($utf8) > 30) {
                            return '<span class="text-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($utf8) . '">' . $short . '</span>';
                        } else {
                            return '<span class="text-secondary">' . $short . '</span>';
                        }
                    })
                    ->addColumn('product_status', function ($product) {
                        if ($product->product_status == 'instock') {
                            return '<span class="badge bg-green-100 text-green-700 text-capitalize fw-semibold">' . __("In Stock") . '</span>';
                        } else if ($product->product_status == 'outstock') {
                            return '<span class="badge bg-red-100 text-red-700 text-capitalize fw-semibold">' . __("Out of Stock") . '</span>';
                        }
                    })
                    ->addColumn('actions', function ($product) {
                        return '<button type="button" class="btn btn-icon-only btn-primary btn-icon mb-1 me-1" onclick="editProduct(' . $product->id . ')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-edit">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                        <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                        <path d="M16 5l3 3"/>
                                    </svg>
                                </button>
                            <button type="button" class="btn btn-icon-only btn-danger btn-icon mb-1" onclick="deleteProduct(' . $product->id . ')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 7l16 0"/>
                                        <path d="M10 11l0 6"/>
                                        <path d="M14 11l0 6"/>
                                        <path d="M5 7l1 12.07c0 .518 .387 .929 .9 .99l.1 .01h10c.552 0 1 -.448 1 -1l1 -12.07"/>
                                        <path d="M9 7l1 -3h4l1 3"/>
                                    </svg>
                                </button>';
                    })
                    ->filterColumn('product_category', function ($query, $keyword) {
                        $query->whereHas('product_category', function ($q) use ($keyword) {
                            $q->where('category_name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('product_image', function ($query, $keyword) {
                        return;
                    })
                    ->rawColumns(['product_category', 'product_image', 'product_name', 'product_description', 'product_status', 'actions'])
                    ->make(true);
            }

            // Fetch related categories, media, and settings
            $categories = StoreCategory::where('store_id', $id)->where('user_id', Auth::user()->user_id)->where('status', 1)->get();
            $media = Medias::where('user_id', Auth::user()->user_id)->orderBy('id', 'desc')->get();
            $settings = Setting::where('status', 1)->first();

            return view('user.pages.edit-store.edit-products', compact('plan_details', 'business_card', 'categories', 'media', 'settings'));
        }
    }

    // Save new product and variants
    public function saveProduct(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'product_category' => 'required|string',
            'product_image' => 'required|string',
            'product_name' => 'required|string',
            'product_short_description' => 'required|string',
            'product_description' => 'required|string',
            'product_sales_price' => 'required',
            'product_status' => 'required'
        ]);

        // Get plan constraints
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Product count check
        $countedProducts = DB::table('store_products')->where('card_id', $request->card_id)->count();

        if ($countedProducts < $plan_details->no_of_store_products) {

            // Step 1: Process base64 <img> tags inside description
            $productDescription = preg_replace_callback(
                '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
                function ($matches) {
                    $extension = $matches[1];
                    $decodedData = base64_decode($matches[2]);

                    if ($decodedData !== false) {
                        $imagePath = StorageHelper::uploadImage(
                            $decodedData,
                            'uploads/store_products',
                            Str::random(10) . '.' . $extension
                        );
                        return '<img src="' . asset($imagePath) . '" />';
                    }
                    return $matches[0];
                },
                $request->product_description
            );

            // Step 2: Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
            $productDescription = preg_replace_callback(
                '/<a\b([^>]*)href=["\'](.*?)["\']([^>]*)>/i',
                function ($matches) {
                    $beforeHref = $matches[1];
                    $href = $matches[2];
                    $afterHref = $matches[3];

                    $tag = "<a{$beforeHref}href=\"{$href}\"{$afterHref}>";

                    if (!preg_match('/\btarget=["\']?[_a-zA-Z]+["\']?/i', $tag)) {
                        $tag = rtrim($tag, '>') . ' target="_blank">';
                    }
                    if (!preg_match('/\brel=["\']?[^"\']*["\']?/i', $tag)) {
                        $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
                    }

                    return $tag;
                },
                $productDescription
            );

            // Step 3: Clean HTML before saving
            $productDescription = Purifier::clean($productDescription);

            DB::beginTransaction();
            try {
                $generatedProductId = uniqid();
                $hasVariants = $request->has_variants == 1 ? 1 : 0;

                // Insert product into store_products table
                DB::table('store_products')->insert([
                    'card_id' => $request->card_id,
                    'product_id' => $generatedProductId,
                    'category_id' => $request->product_category,
                    'badge' => $request->product_badge,
                    'product_image' => $request->product_image,
                    'product_name' => $request->product_name,
                    'product_short_description' => $request->product_short_description,
                    'product_description' => $productDescription,
                    'regular_price' => $request->product_regular_price,
                    'sales_price' => $request->product_sales_price,
                    'product_status' => $request->product_status,
                    'has_variants' => $hasVariants,
                    'status' => '1',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Insert variants if enabled
                if ($hasVariants && !empty($request->variants)) {
                    $variantsData = json_decode($request->variants, true);
                    if (is_array($variantsData)) {
                        $variantsToInsert = [];
                        foreach ($variantsData as $variant) {
                            if (!empty($variant['name']) && !empty($variant['sales_price'])) {
                                $variantsToInsert[] = [
                                    'product_id' => $generatedProductId,
                                    'variant_name' => $variant['name'],
                                    'regular_price' => !empty($variant['regular_price']) ? $variant['regular_price'] : null,
                                    'sales_price' => $variant['sales_price'],
                                    'status' => $variant['status'] ?? 'instock',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }

                        if (!empty($variantsToInsert)) {
                            DB::table('store_product_variants')->insert($variantsToInsert);
                        }
                    }
                }

                DB::commit();
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => trans('Product save failed.')], 500);
            }
        } else {
            return response()->json(['success' => false, 'error' => trans('You have reached the plan limit!')], 500);
        }
    }

    // Update product and variants
    public function updateProduct(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'product_category' => 'required|string',
            'product_image' => 'required|string',
            'product_name' => 'required|string',
            'product_short_description' => 'required|string',
            'product_description' => 'required|string',
            'product_sales_price' => 'required',
            'product_status' => 'required'
        ]);
        
        $product = DB::table('store_products')->where('id', $request->input('product_id'))->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => trans('Product not found.')
            ], 404);
        }

        // Process base64 <img> tags
        $productDescription = preg_replace_callback(
            '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $extension = $matches[1];
                $decodedData = base64_decode($matches[2]);

                if ($decodedData !== false) {
                    $imagePath = StorageHelper::uploadImage(
                        $decodedData,
                        'uploads/store_products',
                        Str::random(10) . '.' . $extension
                    );
                    return '<img src="' . asset($imagePath) . '" />';
                }
                return $matches[0];
            },
            $request->input('product_description')
        );

        // Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
        $productDescription = preg_replace_callback(
            '/<a\b([^>]*)href=["\'](.*?)["\']([^>]*)>/i',
            function ($matches) {
                $beforeHref = $matches[1];
                $href = $matches[2];
                $afterHref = $matches[3];

                $tag = "<a{$beforeHref}href=\"{$href}\"{$afterHref}>";

                if (!preg_match('/\btarget=["\']?[_a-zA-Z]+["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' target="_blank">';
                }
                if (!preg_match('/\brel=["\']?[^"\']*["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
                }

                return $tag;
            },
            $productDescription
        );

        $productDescription = Purifier::clean($productDescription);
        $hasVariants = $request->input('has_variants') == 1 ? 1 : 0;

        DB::beginTransaction();
        try {
            // Update store_products table
            DB::table('store_products')->where('id', $product->id)->update([
            'badge' => $request->input('product_badge'),
            'category_id' => $request->input('product_category'),
            'product_image' => $request->input('product_image'),
            'product_name' => $request->input('product_name'),
            'product_short_description' => $request->input('product_short_description'),
            'product_description' => $productDescription,
            'regular_price' => $request->input('product_regular_price'),
            'sales_price' => $request->input('product_sales_price'),
                'product_status' => $request->input('product_status'),
                'has_variants' => $hasVariants,
                'updated_at' => now()
        ]);

            // Clear existing variants for this product
            DB::table('store_product_variants')->where('product_id', $product->product_id)->delete();

            // Insert updated variants if enabled
            if ($hasVariants && !empty($request->input('variants'))) {
                $variantsData = json_decode($request->input('variants'), true);
                if (is_array($variantsData)) {
                    $variantsToInsert = [];
                    foreach ($variantsData as $variant) {
                        if (!empty($variant['name']) && !empty($variant['sales_price'])) {
                            $variantsToInsert[] = [
                                'product_id' => $product->product_id,
                                'variant_name' => $variant['name'],
                                'regular_price' => !empty($variant['regular_price']) ? $variant['regular_price'] : null,
                                'sales_price' => $variant['sales_price'],
                                'status' => $variant['status'] ?? 'instock',
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }

                    if (!empty($variantsToInsert)) {
                        DB::table('store_product_variants')->insert($variantsToInsert);
                    }
                }
            }

            DB::commit();
        return response()->json([
            'success' => true,
            'message' => trans('Product updated successfully.')
        ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => trans('Product update failed.')
            ], 500);
        }
    }

    // Get single product and its variants using LEFT JOIN
    public function getProducts($id)
    {
        try {
            // Join store_products with store_product_variants
            $results = DB::table('store_products')
                ->leftJoin('store_product_variants', 'store_products.product_id', '=', 'store_product_variants.product_id')
                ->where('store_products.id', $id)
                ->select(
                    'store_products.*',
                    'store_product_variants.id as variant_table_id',
                    'store_product_variants.variant_name',
                    'store_product_variants.regular_price as variant_regular_price',
                    'store_product_variants.sales_price as variant_sales_price',
                    'store_product_variants.status as variant_status'
                )
                ->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Product not found.')
                ], 404);
            }

            // Base product attributes
            $firstRow = $results->first();
            $product = (array) $firstRow;
            $product['variants'] = [];

            // Aggregate joined variants
            foreach ($results as $row) {
                if (!empty($row->variant_table_id)) {
                    $product['variants'][] = [
                        'id' => $row->variant_table_id,
                        'variant_name' => $row->variant_name,
                        'regular_price' => $row->variant_regular_price,
                        'sales_price' => $row->variant_sales_price,
                        'status' => $row->variant_status,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('Product not found.')
            ], 404);
        }
    }

    // Delete product and its variants
    public function deleteProduct($id)
    {
        $product = DB::table('store_products')->where('id', $id)->first();

        if ($product) {
            // Remove associated variants first, then the product
            DB::table('store_product_variants')->where('product_id', $product->product_id)->delete();
            DB::table('store_products')->where('id', $id)->delete();

            return response()->json(['message' => trans('Product deleted successfully')], 200);
        } else {
            return response()->json(['message' => trans('Product not found.')], 404);
        }
    }
}
