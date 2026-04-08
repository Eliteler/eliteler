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

namespace Plugins\SubDomain\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SubDomainController extends Controller
{
    // Index
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // return view
        return view()->file(base_path('plugins/SubDomain/Views/index.blade.php'), compact('settings', 'config'));
    }

    // Update sub domain settings
    public function update(Request $request)
    {
        // update to config
        DB::table('config')->where('config_key', 'enable_subdomain')->update(['config_value' => $request->enable_subdomain == '1' ? 1 : 0]);

        // return redirect to index
        return redirect()->route('admin.plugin.sub-domain')->with('success', trans('Sub Domain Settings Updated Successfully!'));
    }
}
