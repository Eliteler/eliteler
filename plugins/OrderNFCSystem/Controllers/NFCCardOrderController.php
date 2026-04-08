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

namespace Plugins\OrderNFCSystem\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class NFCCardOrderController extends Controller
{
    // Enable/Disable NFC Card Orders 
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // Update Enable/Disable NFC Card Orders
        return view()->file(base_path('plugins/OrderNFCSystem/Views/index.blade.php'), compact('settings', 'config'));
    }

    // Update Enable/Disable NFC Card Orders
    public function update(Request $request)
    {
        // Check if the form is valid
        $nfcCardOrderSystem = $request->enable_disable_nfc_card_order == '1' ? 1 : 0;
        $nfcCardOrderSystemInWebsite = $request->enable_disable_nfc_card_order_website == '1' ? 1 : 0;

        // Character limit below 6
        if (empty($request->nfc_character_limit) || $request->nfc_character_limit < 6) {
            return redirect()->route('admin.plugin.status.nfc.cards.order')->with('failed', trans('Character limit must be greater than 6'));
        }

        // Character limit
        $characterLimit = $request->nfc_character_limit ?? 6;

        // Update the database
        DB::table('config')
            ->where('config_key', 'nfc_order_system')
            ->update([
                'config_value'        => $nfcCardOrderSystem,
            ]);

        DB::table('config')
            ->where('config_key', 'nfc_character_limit')
            ->update([
                'config_value'        => $characterLimit,
            ]);

        DB::table('config')
            ->where('config_key', 'enable_disable_nfc_card_order_website')
            ->update([
                'config_value'        => $nfcCardOrderSystemInWebsite,
            ]);

        return redirect()->route('admin.plugin.status.nfc.cards.order')->with('success', trans('NFC Card Order System Updated Successfully!'));
    }
}
