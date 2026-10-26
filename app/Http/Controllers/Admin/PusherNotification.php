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

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Services\PusherBeamsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Browser push notifications to everyone subscribed on the website
 * (Pusher Beams, interest "global").
 */
class PusherNotification extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return Inertia::render('marketing/push', [
            'configured'  => PusherController::configured(),
            'enabled'     => env('PUSHER_BEAMS_ENABLED') === '1',
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    public function send(Request $request, PusherBeamsService $beams)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string|max:255',
            'image'      => 'nullable|file|mimes:png,jpeg,jpg,webp|max:' . env('SIZE_LIMIT', 2048),
            'target_url' => 'nullable|url|max:255',
        ], [], ['target_url' => __('link'), 'image' => __('icon')]);

        if (! PusherController::configured()) {
            return back()->with('failed', __('Pusher Beams isn’t set up yet.'));
        }

        $notification = ['title' => $data['title'], 'body' => $data['message']];

        if ($request->hasFile('image')) {
            $notification['icon'] = asset(StorageHelper::uploadImage($request->file('image'), 'uploads/notifications'));
        }
        if (! empty($data['target_url'])) {
            $notification['deep_link'] = $data['target_url'];
        }

        try {
            $beams->broadcastToInterest('global', $notification);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('The notification could not be sent: :error', ['error' => $e->getMessage()]));
        }

        return back()->with('success', __('Notification sent to all subscribers.'));
    }
}
