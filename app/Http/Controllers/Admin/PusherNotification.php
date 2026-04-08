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

namespace App\Http\Controllers\Admin;

use App\Setting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\PusherBeamsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\SubscriberNotification;

class PusherNotification extends Controller
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

    public function index()
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.marketing.pusher-notification.index', compact('settings', 'config'));
    }

    // Send Notification
    public function send(Request $request, PusherBeamsService $beamsService)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:255',
            'image' => 'required|mimes:png,jpeg,jpg|max:' . env('SIZE_LIMIT'),
            'target_url' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Handle image upload
        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $cleanName = Str::slug($originalName);
        $fileName = 'notification-' . $cleanName . '-' . time() . '.' . $extension;

        $storagePath = 'uploads/notifications/' . $fileName;
        Storage::disk('public')->put($storagePath, file_get_contents($image));

        // Get public URL
        $imageUrl = url('storage/' . $storagePath);

        // Prepare notification payload
        $notification = [
            'title' => $request->title,
            'body' => $request->message,
            'icon' => $imageUrl,
            'deep_link' => $request->target_url,
        ];

        // Send notification
        if ($beamsService) {
            $beamsService->broadcastToInterest('global', $notification);
            return redirect()->route('admin.marketing.pusher.notification')->with('success', trans('Notification sent successfully.'));
        } else {
            return redirect()->back()->with('failed', trans('Pusher Beams instance ID or secret key is missing.'));
        }

        return redirect()->route('admin.marketing.pusher.notification');
    }
}
