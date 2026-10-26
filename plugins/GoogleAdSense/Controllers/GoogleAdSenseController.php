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

namespace Plugins\GoogleAdSense\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Google AdSense publisher ID (settings.google_adsense_code, used in app.blade.php).
 * "Off" is stored as "DISABLE_ADSENSE_ONLY" (the page shows it as a blank field).
 * Page: plugins/GoogleAdSense/resources/js/settings.tsx.
 */
class GoogleAdSenseController extends Controller
{
    public function googleAdSenseSettings()
    {
        $code = (string) DB::table('settings')->where('id', 1)->value('google_adsense_code');

        return PluginPage::render('GoogleAdSense', 'settings', [
            'google_adsense_code' => $code === 'DISABLE_ADSENSE_ONLY' ? '' : $code,
        ]);
    }

    public function googleAdSenseSettingsUpdate(Request $request)
    {
        $request->merge(['google_adsense_code' => trim((string) $request->input('google_adsense_code'))]);
        $data = $request->validate([
            'google_adsense_code' => ['nullable', 'string', 'max:100', 'regex:/^ca-pub-\d{10,20}$/'],
        ], [
            'google_adsense_code.regex' => __('Use your publisher ID, which looks like ca-pub-1234567890123456.'),
        ]);

        // app.blade.php only skips the ads script for this exact marker, so "off" must store it
        DB::table('settings')->where('id', 1)->update(['google_adsense_code' => $data['google_adsense_code'] ?: 'DISABLE_ADSENSE_ONLY']);

        return back()->with('success', filled($data['google_adsense_code']) ? __('Google AdSense is on.') : __('Google AdSense is off.'));
    }
}
