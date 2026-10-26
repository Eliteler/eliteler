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

namespace Plugins\ReferralSystem\Controllers;

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * Referral program on/off (config referral_system). Commission and payouts are
 * set in Referral Program → Settings. Page: plugins/ReferralSystem/resources/js/settings.tsx.
 */
class ReferralSystemController extends Controller
{
    // Enable/Disable Referral System
    public function index()
    {
        return PluginPage::render('ReferralSystem', 'settings', [
            'enabled' => (string) DB::table('config')->where('config_key', 'referral_system')->value('config_value') === '1',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['enable_referral_system' => 'required|boolean']);

        DB::table('config')->where('config_key', 'referral_system')->update(['config_value' => $request->boolean('enable_referral_system') ? 1 : 0]);

        return back()->with('success', $request->boolean('enable_referral_system') ? __('The referral program is on.') : __('The referral program is off.'));
    }
}
