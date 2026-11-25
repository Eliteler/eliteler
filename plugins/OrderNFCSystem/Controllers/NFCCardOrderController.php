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

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * Smart Products ordering on/off (config nfc_order_system, enable_disable_nfc_card_order_website),
 * activation key length (nfc_character_limit) and the flat shipping fee
 * (smart_product_shipping_fee, smart_product_free_shipping_above; 0 = off).
 * Page: plugins/OrderNFCSystem/resources/js/settings.tsx.
 */
class NFCCardOrderController extends Controller
{
    // Enable/Disable NFC Card Orders 
    private const KEYS = [
        'nfc_order_system', 'enable_disable_nfc_card_order_website', 'nfc_character_limit',
        'smart_product_shipping_fee', 'smart_product_free_shipping_above',
    ];

    public function index()
    {
        $config = DB::table('config')->whereIn('config_key', self::KEYS)->pluck('config_value', 'config_key');

        return PluginPage::render('OrderNFCSystem', 'settings', [
            'values' => [
                'enable_disable_nfc_card_order'         => (string) ($config['nfc_order_system'] ?? '0') === '1',
                'enable_disable_nfc_card_order_website' => (string) ($config['enable_disable_nfc_card_order_website'] ?? '0') === '1',
                'nfc_character_limit'                   => (string) ($config['nfc_character_limit'] ?? '12'),
                'smart_product_shipping_fee'            => (string) (float) ($config['smart_product_shipping_fee'] ?? 0),
                'smart_product_free_shipping_above'     => (string) (float) ($config['smart_product_free_shipping_above'] ?? 0),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enable_disable_nfc_card_order'         => 'required|boolean',
            'enable_disable_nfc_card_order_website' => 'required|boolean',
            'nfc_character_limit'                   => 'required|integer|min:6|max:64',
            'smart_product_shipping_fee'            => 'required|numeric|min:0|max:9999999',
            'smart_product_free_shipping_above'     => 'required|numeric|min:0|max:9999999',
        ], [], [
            'nfc_character_limit'               => __('key length'),
            'smart_product_shipping_fee'        => __('shipping fee'),
            'smart_product_free_shipping_above' => __('free shipping amount'),
        ]);

        foreach ([
            'nfc_order_system'                      => $request->boolean('enable_disable_nfc_card_order') ? 1 : 0,
            'enable_disable_nfc_card_order_website' => $request->boolean('enable_disable_nfc_card_order_website') ? 1 : 0,
            'nfc_character_limit'                   => (int) $data['nfc_character_limit'],
            'smart_product_shipping_fee'            => round((float) $data['smart_product_shipping_fee'], 2),
            'smart_product_free_shipping_above'     => round((float) $data['smart_product_free_shipping_above'], 2),
        ] as $key => $value) {
            // updateOrInsert: the shipping keys are missing until the Smart Products update has run
            DB::table('config')->updateOrInsert(['config_key' => $key], ['config_value' => $value, 'updated_at' => now()]);
        }

        return back()->with('success', __('Smart Products settings saved.'));
    }
}
