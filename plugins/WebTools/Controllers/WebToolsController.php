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

namespace Plugins\WebTools\Controllers;

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * Free web tools on the public website on/off (config enable_webtools).
 * Page: plugins/WebTools/resources/js/settings.tsx.
 */
class WebToolsController extends Controller
{
    // Enable/Disable Web Tools
    public function index()
    {
        return PluginPage::render('WebTools', 'settings', [
            'enabled' => (string) DB::table('config')->where('config_key', 'enable_webtools')->value('config_value') === '1',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['enable_webtools' => 'required|boolean']);

        DB::table('config')->where('config_key', 'enable_webtools')->update(['config_value' => $request->boolean('enable_webtools') ? 1 : 0]);

        return back()->with('success', $request->boolean('enable_webtools') ? __('Web tools are on.') : __('Web tools are off.'));
    }
}
