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
use App\Services\GoBizCommonService;
use App\Services\SmartProducts\ChipLinks;
use Illuminate\Http\Request;

/**
 * /nfc/{key}: the address written on every Smart Product chip. Opens the
 * business card, store or link the owner chose (App\Services\SmartProducts\ChipLinks)
 * and counts the tap. Never change this URL: it is printed on chips.
 */
class ReadNfcCardController extends Controller
{
    public function readNfcCard(Request $request, $id)
    {
        $nfcCardKey = NfcCardKey::where('unqiue_key', $id)->first();

        if (! $nfcCardKey) {
            return $this->notLinked();
        }

        ChipLinks::recordScan($nfcCardKey);

        $destination = ChipLinks::destination($nfcCardKey);

        return $destination ? redirect()->away($destination) : $this->notLinked();
    }

    private function notLinked()
    {
        $settings = GoBizCommonService::settings();
        $config   = GoBizCommonService::config();

        $title       = __('NFC Card Not Linked!');
        $description = __('This NFC card isn\'t connected to any of your active links.');
        $meta        = [
            'title'       => $title,
            'description' => $description,
            'keywords'    => '',
            'robots'      => 'noindex, nofollow',
        ];

        $web_template = getConfigData('web_template');

        return view(
            $web_template . '::Website.pages.nfc.index',
            compact('settings', 'config', 'title', 'description', 'meta')
        );
    }
}
