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

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * Subdomains for cards and stores on/off (config enable_subdomain).
 * Page: plugins/SubDomain/resources/js/settings.tsx.
 */
class SubDomainController extends Controller
{
    // Index
    public function index()
    {
        return PluginPage::render('SubDomain', 'settings', [
            'enabled' => (string) DB::table('config')->where('config_key', 'enable_subdomain')->value('config_value') === '1',
            'example' => 'yourname.' . parse_url(url('/'), PHP_URL_HOST),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['enable_subdomain' => 'required|boolean']);

        DB::table('config')->where('config_key', 'enable_subdomain')->update(['config_value' => $request->boolean('enable_subdomain') ? 1 : 0]);

        return back()->with('success', $request->boolean('enable_subdomain') ? __('Subdomains are on.') : __('Subdomains are off.'));
    }
}
