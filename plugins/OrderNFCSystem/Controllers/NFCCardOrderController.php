<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace Plugins\OrderNFCSystem\Controllers;

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * NFC card ordering on/off (config nfc_order_system, enable_disable_nfc_card_order_website)
 * and activation key length (nfc_character_limit).
 * Page: plugins/OrderNFCSystem/resources/js/settings.tsx.
 */
class NFCCardOrderController extends Controller
{
    // Enable/Disable NFC Card Orders 
    private const KEYS = ['nfc_order_system', 'enable_disable_nfc_card_order_website', 'nfc_character_limit'];

    public function index()
    {
        $config = DB::table('config')->whereIn('config_key', self::KEYS)->pluck('config_value', 'config_key');

        return PluginPage::render('OrderNFCSystem', 'settings', [
            'values' => [
                'enable_disable_nfc_card_order'         => (string) ($config['nfc_order_system'] ?? '0') === '1',
                'enable_disable_nfc_card_order_website' => (string) ($config['enable_disable_nfc_card_order_website'] ?? '0') === '1',
                'nfc_character_limit'                   => (string) ($config['nfc_character_limit'] ?? '12'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enable_disable_nfc_card_order'         => 'required|boolean',
            'enable_disable_nfc_card_order_website' => 'required|boolean',
            'nfc_character_limit'                   => 'required|integer|min:6|max:64',
        ], [], [
            'nfc_character_limit' => __('key length'),
        ]);

        foreach ([
            'nfc_order_system'                      => $request->boolean('enable_disable_nfc_card_order') ? 1 : 0,
            'enable_disable_nfc_card_order_website' => $request->boolean('enable_disable_nfc_card_order_website') ? 1 : 0,
            'nfc_character_limit'                   => (int) $data['nfc_character_limit'],
        ] as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }

        return back()->with('success', __('NFC card ordering settings saved.'));
    }
}
