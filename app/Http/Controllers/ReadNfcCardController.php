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

namespace App\Http\Controllers;

use App\NfcCardKey;
use App\BusinessCard;
use App\Services\GoBizCommonService;
use Illuminate\Http\Request;

class ReadNfcCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function readNfcCard(Request $request, $id)
    {
        // Queries
        $settings = GoBizCommonService::settings();
        $config = GoBizCommonService::config();

        // Get NFC Card Key details
        $nfcCardKey = NfcCardKey::where('unqiue_key', $id)->first();

        if (!$nfcCardKey) {
            // title
            $title = __('NFC Card Not Linked!');

            // description
            $description = __('This NFC card isn\'t connected to any of your active links.');

            // meta
            $meta = [
                'title' => $title,
                'description' => $description,
                'keywords' => '',
                'robots' => 'noindex, nofollow',
            ];

            return view('website.pages.nfc.index', compact('settings', 'config', 'title', 'description', 'meta'));
        }

        // Get business card details
        if ($nfcCardKey->card_id != null) {
            $businessCardDetails = BusinessCard::where('card_id', $nfcCardKey->card_id)->first();

            if (!$businessCardDetails) {
                // title
                $title = __('NFC Card Not Linked!');

                // description
                $description = __('This NFC card isn\'t connected to any of your active links.');

                // meta
                $meta = [
                    'title' => $title,
                    'description' => $description,
                    'keywords' => '',
                    'robots' => 'noindex, nofollow',
                ];

                return view('website.pages.nfc.index', compact('settings', 'config', 'title', 'description', 'meta'));
            }

            if ($businessCardDetails->custom_domain == null) {
                $live = $config[46]->config_value == '1' ? route('subdomain.profile', $businessCardDetails->card_url) : route('profile', $businessCardDetails->card_url);
            } else {
                $live = 'https://www.' . $businessCardDetails->custom_domain . '/';
            }

            return redirect()->to($live);
        } else {
            // title
            $title = __('NFC Card Not Linked!');

            // description
            $description = __('This NFC card isn\'t connected to any of your active links.');

            // meta
            $meta = [
                'title' => $title,
                'description' => $description,
                'keywords' => '',
                'robots' => 'noindex, nofollow',
            ];

            return view('website.pages.nfc.index', compact('settings', 'config', 'title', 'description', 'meta'));
        }
    }
}
