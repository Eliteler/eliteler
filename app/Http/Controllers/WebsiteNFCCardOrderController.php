<?php

namespace App\Http\Controllers;

use App\Setting;
use App\NfcCardDesign;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use App\Services\GoBizCommonService;

class WebsiteNFCCardOrderController extends Controller
{
    public function index()
    {
        // Queries
        $settings = GoBizCommonService::settings();
        $config   = GoBizCommonService::config();
        $web_template = getConfigData('web_template');

        // Check enable nfc card order system
        if ($config[76]->config_value == '0' || $config[97]->config_value == "0") {
            return abort(404);
        }

        // Available NFC Cards
        $availableNfcCards = NfcCardDesign::where('status', 1)->get();

        return view($web_template . '::Website.pages.nfc-order.index', compact('availableNfcCards', 'settings', 'config'));
    }
}
