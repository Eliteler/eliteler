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

namespace Plugins\AiBuilder\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;

class AiBuilderController extends Controller
{
    // Enable/Disable AiBuilder
    public function index()
    {
        // Queries
        $settings = GoBizCommonService::settings();
        $aibuilder_settings = DB::table('aibuilder_settings')->first();

        return view()->file(base_path('plugins/AiBuilder/Views/index.blade.php'), compact('settings', 'aibuilder_settings'));
    }

    // Update Enable/Disable AiBuilder
    public function update(Request $request)
    {
        // Check if the form is valid
        $enableAiBuilder = $request->aibuilder == '1' ? 1 : 0;
        $provider = $request->provider;
        $model = $request->model;
        $key_1 = $request->key_1;
        $key_2 = $request->key_2;
        $generate_image = $request->generate_image == '1' ? 1 : 0;

        // Update the database
        DB::table('aibuilder_settings')->update([
            'aibuilder' => $enableAiBuilder,
            'provider' => $provider,
            'model' => $model,
            'generate_image' => $generate_image,
            'key_1' => $key_1,
            'key_2' => $key_2
        ]);

        return redirect()->route('admin.plugin.aibuilder')->with('success', trans('Updated!'));
    }
}
