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

namespace App\Http\Controllers\User;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Medias;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class MediaController extends Controller
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

    // All user media
    public function media(Request $request)
    {
        // Check active plan
        $active_plan = json_decode(Auth::user()->plan_details);
        if ($active_plan == null) {
            return redirect()->route('user.plans');
        }

        // AJAX API Response for File Manager
        if ($request->ajax()) {
            $query = Medias::where('user_id', Auth::user()->user_id);

            // Server-side keyword search
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('media_url', 'like', "%{$search}%");
            }

            $media = $query->orderBy('id', 'desc')->paginate(12);

            $media->getCollection()->transform(function ($item) {
                $fileName = basename($item->media_url);
                $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');

                $item->file_name = $fileName;
                $item->extension = $extension;
                $item->formatted_created_at = Carbon::parse($item->created_at)->diffForHumans();
                $item->full_date = Carbon::parse($item->created_at)->format('d M Y, h:i A');
                $item->size_formatted = $this->formatBytes($item->size);
                $item->base_url = asset('');
                $item->full_url = asset($item->media_url);
                return $item;
            });

            return response()->json([
                'media' => $media
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }

        // Calculate Storage Metrics
        $totalStorage = (float) $active_plan->storage;
        $rawUsedBytes = Medias::where('user_id', Auth::user()->user_id)->sum('size');
        $usedStorage  = round($rawUsedBytes / 1024 / 1024, 2); // In MB

        if ($totalStorage <= 0 || $totalStorage == 999) {
            $usedStoragePercentage = 0;
            $availableStorage = 'Unlimited';
            $availableStoragePercentage = 100;
        } else {
            $usedStoragePercentage = round(($usedStorage / $totalStorage) * 100, 1);
            $availableStorage = max(0, round($totalStorage - $usedStorage, 2));
            $availableStoragePercentage = max(0, round(100 - $usedStoragePercentage, 1));
        }

        $settings = Setting::where('status', 1)->first();

        return view('user.pages.media.index', compact(
            'active_plan',
            'settings',
            'totalStorage',
            'usedStorage',
            'usedStoragePercentage',
            'availableStorage',
            'availableStoragePercentage'
        ));
    }

    // Add media
    public function addMedia()
    {
        // Queries
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.media.add', compact('settings'));
    }

    // Upload media
    public function uploadMedia(Request $request)
    {
        // Parameters
        $image = $request->file('file');

        // Validate file presence and type
        if (!$image || !in_array($image->extension(), ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
            return response()->json(['status' => 'error', 'message' => trans('Invalid file format.')], 400);
        }

        // Total customer used storage
        $totalUsedStorage = Medias::where('user_id', Auth::user()->user_id)->sum('size');

        // Unique ID
        $uniqid = uniqid();

        // Get size before move
        $imageSize = $image->getSize();

        // Check user storage limit
        $active_plan = json_decode(Auth::user()->plan_details);

        // Store in unlimited
        if ($active_plan->storage == "999") {
            $active_plan->storage = "1073741824000";
        }

        // Total
        $totalStorage = round(($totalUsedStorage + $imageSize) / 1024 / 1024, 2);

        if ($active_plan?->storage !== null && $totalStorage > $active_plan->storage) {
            return response()->json(['status' => 'error', 'message' => trans('You have reached your storage limit.')], 200);
        }

        // Upload media image with custom name (user_id-uniqid) and get relative path
        $media_url = StorageHelper::uploadImage(
            $image,
            'images',
            Auth::user()->user_id . '-' . $uniqid
        );

        // Save record in database
        $card = new Medias();
        $card->media_id = $uniqid;
        $card->user_id = Auth::user()->user_id;
        $card->media_name = $image->getClientOriginalName();
        $card->media_url = $media_url;
        $card->size = $imageSize;
        $card->save();

        return response()->json(['status' => 'success', 'message' => trans('Uploaded!')]);
    }

    // Delete media
    public function deleteMedia(Request $request)
    {
        $media = Medias::where('user_id', Auth::user()->user_id)
            ->where('media_id', $request->query('id'))
            ->first();

        // Check media
        if (!$media) {
            return redirect()->route('user.media')
                ->with('failed', trans('Media not found.'));
        }

        try {
            // Delete media image file from storage using StorageHelper
            StorageHelper::deleteFile($media->media_url);
        } catch (\Exception $e) {
            Log::error('Media delete failed: ' . $e->getMessage());
        }

        // Delete media database record
        $media->delete();

        return redirect()->route('user.media')
            ->with('success', trans('Removed!'));
    }

    public function multipleImages(Request $request)
    {
        // Parameters
        $image = $request->file('file');

        // Validate file presence and type
        if (!$image || !in_array($image->extension(), ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
            return response()->json(['status' => 'error', 'message' => trans('Invalid file format.')], 400);
        }

        // Total customer used storage
        $totalUsedStorage = Medias::where('user_id', Auth::user()->user_id)->sum('size');

        // Get plan
        $active_plan = json_decode(Auth::user()->plan_details);

        // Get image size
        $imageSize = $image->getSize();

        // Calculate new total in MB
        $totalStorage = round(($totalUsedStorage + $imageSize) / 1024 / 1024, 2);

        // Store in unlimited
        if ($active_plan->storage == "999") {
            $active_plan->storage = "1073741824000";
        }

        // Check storage limit
        if (!empty($active_plan?->storage) && $totalStorage > $active_plan->storage) {
            return response()->json(['status' => 'error', 'message' => trans('You have reached your storage limit.')], 200);
        }

        // Unique ID
        $uniqid = uniqid();

        // Upload image using StorageHelper and get relative path
        $media_url = StorageHelper::uploadImage(
            $image,
            'images',
            Auth::user()->user_id . '-' . $uniqid
        );

        // Save media record in database
        $card = new Medias();
        $card->media_id = $uniqid;
        $card->user_id = Auth::user()->user_id;
        $card->media_name = $image->getClientOriginalName();
        $card->media_url = $media_url;
        $card->size = $imageSize;
        $card->save();

        return response()->json([
            'status'     => 'success',
            'message'    => trans('Uploaded!'),
            'image_url'  => asset($media_url), // full URL for frontend use
        ]);
    }


    // Delete multiple media items
    public function deleteMultipleMedia(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => trans('No files selected for deletion.')
            ], 400);
        }

        // Fetch only media belonging to the authenticated user
        $mediaItems = Medias::where('user_id', Auth::user()->user_id)
            ->whereIn('media_id', $ids)
            ->get();

        if ($mediaItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => trans('No matching media files found.')
            ], 404);
        }

        $deletedCount = 0;

        foreach ($mediaItems as $media) {
            try {
                // Delete file using StorageHelper
                StorageHelper::deleteFile($media->media_url);

                // Delete DB record
                $media->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('Bulk media delete failed for ID ' . $media->media_id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => trans(':count media file(s) removed successfully.', ['count' => $deletedCount])
        ]);
    }

    // vCard and Store media upload
    public function getMediaData(Request $request)
    {
        if ($request->ajax()) {
            $media = Medias::where('user_id', Auth::user()->user_id)
                ->orderBy('id', 'desc');

            return DataTables::of($media)->make(true);
        }

        return view('media.index');
    }

    // Convert to MB
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
