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

namespace App\Http\Middleware;

use App\NfcCardDesign;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Smart Products: the same checks as the checkout page, on every payment
 * gateway's checkout URL (core and plugins), so those URLs can't be opened
 * directly to skip them: module on, signed in, product active, the plan
 * includes Smart Products, and the quantity is in stock.
 *
 * Reads {nfcId} / {quantity} from the route, or nfc_id / quantity from the
 * form (Bank Transfer submit).
 */
class SmartProductCheckout
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(DB::table('config')->where('config_key', 'nfc_order_system')->value('config_value') == '1', 404);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $productId = (string) ($request->route('nfcId') ?? $request->input('nfc_id', ''));
        $quantity  = max((int) ($request->route('quantity') ?? $request->input('quantity', 1)), 1);
        $design    = NfcCardDesign::where('nfc_card_id', $productId)->where('status', 1)->first();

        if (! $design) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('NFC Card not found.'));
        }

        if ((int) (json_decode((string) Auth::user()->plan_details)->nfc_card ?? 0) !== 1) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('This feature is not available on your current plan.'));
        }

        if ((int) $design->available_stocks <= 0) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('This NFC card is out of stock.'));
        }

        if ($quantity > (int) $design->available_stocks) {
            return redirect()->route('user.order.nfc.card.checkout', $design->nfc_card_id)->with('failed', __('Requested quantity exceeds available stock.'));
        }

        return $next($request);
    }
}
