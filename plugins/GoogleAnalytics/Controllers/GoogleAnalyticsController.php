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

namespace Plugins\GoogleAnalytics\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Google Analytics measurement ID (settings.google_analytics_id, used in app.blade.php
 * and the admin layout). Blank turns tracking off.
 * Page: plugins/GoogleAnalytics/resources/js/settings.tsx.
 */
class GoogleAnalyticsController extends Controller
{
    public function googleAnalyticsSettings()
    {
        return PluginPage::render('GoogleAnalytics', 'settings', [
            'google_analytics_id' => (string) DB::table('settings')->where('id', 1)->value('google_analytics_id'),
        ]);
    }

    public function googleAnalyticsSettingsUpdate(Request $request)
    {
        $request->merge(['google_analytics_id' => strtoupper(trim((string) $request->input('google_analytics_id')))]);
        $data = $request->validate([
            'google_analytics_id' => ['nullable', 'string', 'max:50', 'regex:/^(G|UA|GT|AW)-[A-Z0-9-]+$/'],
        ], [
            'google_analytics_id.regex' => __('Use your measurement ID, which looks like G-XXXXXXXXXX.'),
        ]);

        DB::table('settings')->where('id', 1)->update(['google_analytics_id' => $data['google_analytics_id'] ?: null]);

        return back()->with('success', filled($data['google_analytics_id']) ? __('Google Analytics is on.') : __('Google Analytics is off.'));
    }
}
