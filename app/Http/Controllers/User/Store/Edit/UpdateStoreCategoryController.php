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

use App\Setting;
use App\BusinessCard;
use App\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class UpdateStoreCategoryController extends Controller
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

    // Categories
    public function getCategories(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Queries
            if ($request->ajax()) {
                $categories = StoreCategory::where('store_id', $id)->orderBy('id', 'desc')->get();

                return DataTables::of($categories)
                    ->addIndexColumn()
                    ->editColumn('thumbnail', function ($category) {
                        return '<img src="' . asset($category->thumbnail) . '" class="img-fluid rounded" style="max-width: 100px; max-height: 100px;" />';
                    })
                    ->editColumn('category_id', function ($category) {
                        return '<strong>' . $category->category_id . '</strong>';
                    })
                    ->editColumn('category_name', function ($category) {
                        return '<strong>' . $category->category_name . '</strong>';
                    })
                    ->editColumn('status', function ($category) {
                        return $category->status == 0
                            ? '<span class="badge bg-red text-white text-white">' . __('Disabled') . '</span>'
                            : '<span class="badge bg-green text-white text-white">' . __('Enabled') . '</span>';
                    })
                    ->addColumn('actions', function ($category) {
                        $actionBtn = '<a class="dropdown-item" onclick="editCategory(`' . $category->category_id . '`)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1 icon-tabler icons-tabler-outline icon-tabler-pencil">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                        <path d="M13.5 6.5l4 4" />
                                    </svg>' . __('Edit') . '</a>';

                        $actionBtn .= '<a class="dropdown-item text-danger" onclick="deleteCategory(`' . $category->category_id . '`, `deleted`); return false;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1 icon-tabler icons-tabler-outline icon-tabler-trash">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M4 7l16 0" />
                                            <path d="M10 11l0 6" />
                                            <path d="M14 11l0 6" />
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>' . __('Delete') . '</a>';

                        return '
                            <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                ' . $actionBtn . '
                            </div>';
                    })
                    ->rawColumns(['category_id', 'thumbnail', 'category_name', 'status', 'actions'])
                    ->make(true);
            }

            $config   = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();

            return view('user.pages.edit-store.edit-category', compact('business_card', 'settings', 'config'));
        }
    }

    // Save category
    public function saveCategory(Request $request)
    {
        $plan = DB::table('users')
            ->where('user_id', Auth::user()->user_id)
            ->where('status', 1)
            ->first();

        $plan_details = json_decode($plan->plan_details);

        $categories = StoreCategory::where('store_id', $request->store_id)->count();

        if ($categories < $plan_details->no_of_categories) {

            $validator = Validator::make($request->all(), [
                'category_image' => 'required|mimes:jpeg,png,jpg,webp|max:' . env("SIZE_LIMIT"),
                'category_name' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages()->first()
                ]);
            }

            $thumbnailFile = $request->file('category_image');
            if (!$thumbnailFile) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Please upload a thumbnail image!')
                ]);
            }

            $originalExtension = $thumbnailFile->getClientOriginalExtension();
            $uploadPath = 'images/categories/';
            $thumbnailName = 'IMG-' . uniqid() . '-' . time() . '.' . $originalExtension;

            // Store the file in storage/app/public/images/categories
            Storage::disk('public')->putFileAs($uploadPath, $thumbnailFile, $thumbnailName);

            try {
                $category = new StoreCategory();
                $category->store_id = $request->store_id;
                $category->user_id = Auth::user()->user_id;
                $category->category_id = uniqid();
                $category->thumbnail = "storage/" . $uploadPath . $thumbnailName; // Save relative path
                $category->category_name = ucfirst($request->category_name);
                $category->save();

                return response()->json(['success' => true, 'message' => trans('New Category Created!')]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => trans('Failed to create category!')]);
            }
        } else {
            return response()->json(['success' => false, 'message' => trans('You have reached the plan limit!')]);
        }
    }


    // Store category
    public function storeCategory($id)
    {
        try {
            // Retrieve the category from the database
            $category = StoreCategory::where('category_id', $id)->first();

            // Return a JSON response with the category data
            return response()->json([
                'success' => true,
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            // Handle errors (e.g., category not found)
            return response()->json([
                'success' => false,
                'message' => trans('Category not found!')
            ], 404);
        }
    }

    // Update category
    public function updateStoreCategory(Request $request)
    {
        // Validate the request data as per your requirements
        $category = StoreCategory::where('category_id', $request->category_id)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => trans('Category not found.')
            ], 404);
        }

        // Update category data
        if (!empty($request->category_image)) {
            $thumbnailFile = $request->file('category_image');

            if (!$thumbnailFile) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Please upload a thumbnail image!')
                ]);
            }

            $originalExtension = $thumbnailFile->getClientOriginalExtension();
            $uploadPath = 'images/categories/';
            $thumbnailName = 'IMG-' . uniqid() . '-' . time() . '.' . $originalExtension;

            // Store the image in storage/app/public/images/categories
            Storage::disk('public')->putFileAs($uploadPath, $thumbnailFile, $thumbnailName);

            StoreCategory::where('category_id', $request->category_id)->update([
                'thumbnail' => 'storage/' . $uploadPath . $thumbnailName,
            ]);
        }

        StoreCategory::where('category_id', $request->category_id)->update([
            'category_name' => $request->category_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => trans('Category updated successfully.')
        ]);
    }

    // Delete category
    public function deleteStoreCategory($id)
    {
        // Delete
        StoreCategory::where('category_id', $id)->delete();

        return response()->json(['message' => trans('Category deleted successfully')], 200);
    }
}
