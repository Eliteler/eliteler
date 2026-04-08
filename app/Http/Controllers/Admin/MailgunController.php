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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MailgunController extends Controller
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

    // All Mailgun configurations
    public function index()
    {
        // Get Mailgun configuration
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.marketing.mailgun.index', compact('config', 'settings'));
    }

    // Update Mailgun configuration
    public function update(Request $request)
    {
        // Update Mailgun configurations
        DB::table('config')->where('config_key', 'mailgun_smtp_password')->update([
            'config_value' => $request->mailgun_api_key,
        ]);

        DB::table('config')->where('config_key', 'mailgun_from_address')->update([
            'config_value' => $request->mailgun_from_email,
        ]);

        // Update Mailgun region
        DB::table('config')->where('config_key', 'mailgun_region')->update([
            'config_value' => $request->mailgun_region,
        ]);

        return redirect()->route('admin.marketing.mailgun')->with('success', trans('Updated!'));
    }
}
