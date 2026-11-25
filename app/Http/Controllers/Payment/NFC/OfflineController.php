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

namespace App\Http\Controllers\Payment\NFC;

use App\Services\SmartProducts\OrderQuote;
use App\User;
use App\Coupon;
use App\Setting;
use App\NfcCardOrder;
use App\AppliedCoupon;
use App\NfcCardDesign;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OfflineController extends Controller
{
    public function nfcOfflineCheckout(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        // Queries
        $config = DB::table('config')->get();

        // Quantity
        $quantity = max((int) $quantity, 1);

        if ($config[31]->config_value == null) {
            return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', trans('No Bank Transfer details found!'));
        } else {
            // Queries
            $settings = Setting::where('status', 1)->first();
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            return view('user.pages.order.nfc-card.checkout.pay-with-offline', compact('settings', 'nfcDetails', 'config', 'couponId', 'quantity'));
        }
    }

    // Mark Offline Payment
    public function nfcMarkOfflinePayment(Request $request)
    {
        if (Auth::user()) {
            // Queries
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();

            $settings = Setting::where('status', 1)->first();
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $request->nfc_id)->where('status', 1)->first();

            // Check $request->transaction_id is empty or not
            if ($request->transaction_id == null) {
                return redirect()->route('user.order.nfc.card.checkout', $nfcDetails->nfc_card_id)->with('failed', trans('Transaction ID is required.'));
            }

            // Get coupon id
            $couponId = $request->coupon_id;

            // Quantity
            $quantity = max((int) $request->quantity, 1);

            if ($nfcDetails == null) {
                return view('errors.404');
            } else {
                // Check $request->transaction_id is exist or not
                $transaction = NfcCardOrderTransaction::where('payment_transaction_id', $request->transaction_id)->count();

                if ($transaction > 0) {
                    return redirect()->route('user.order.nfc.card.checkout', $nfcDetails->nfc_card_id)->with('failed', trans('This transaction ID is already processed.'));
                } else {

                    // Check applied coupon
                    $couponDetails = Coupon::where('used_for', 'nfc')->where('coupon_code', $couponId)->first();

                    // Applied tax in total
                    $appliedTaxInTotal = 0;

                    // Discount price
                    $discountPrice = 0;

                    // Applied coupon
                    $appliedCoupon = null;

                    // Price
                    $subtotal = $nfcDetails->nfc_card_price * $quantity;

                    // NFC Card Order ID
                    $nfcCardOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());
                    $nfcTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

                    // Price: same calculation as the checkout page (App\Services\SmartProducts\OrderQuote)
                    $quote             = OrderQuote::make($nfcDetails, $quantity, $couponDetails);
                    $subtotal          = $quote->subtotal;
                    $appliedTaxInTotal = $quote->tax;
                    $discountPrice     = $quote->discount;
                    $appliedCoupon     = $quote->couponCode;
                    $amountToBePaid    = $quote->total;

                    // Store transaction details in nfc_card_order_id table before redirecting to PayPal
                    $nfcCardOrder = new NfcCardOrder();
                    $nfcCardOrder->nfc_card_order_id = $nfcCardOrderId;
                    $nfcCardOrder->user_id = Auth::id();
                    $nfcCardOrder->nfc_card_id = $nfcDetails->nfc_card_id;
                    $nfcCardOrder->nfc_card_order_transaction_id = $nfcTransactionId;
                    $nfcCardOrder->order_details = json_encode($quote->orderDetails());
                    $nfcCardOrder->delivery_address = json_encode(OrderQuote::deliveryAddress($userData));
                    $nfcCardOrder->delivery_note = "-";
                    $nfcCardOrder->order_status = 'pending';
                    $nfcCardOrder->status = 1;
                    $nfcCardOrder->save();

                    // Store transaction details in nfc_card_order_transactions table before redirecting to PayPal
                    $transaction = new NfcCardOrderTransaction();
                    $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                    $transaction->nfc_card_order_id = $nfcCardOrderId;
                    $transaction->payment_transaction_id = $request->transaction_id;
                    $transaction->payment_method = "Offline";
                    $transaction->currency = $config[1]->config_value;
                    $transaction->amount = $amountToBePaid;
                    $transaction->invoice_details = json_encode($quote->invoiceDetails($userData));
                    $transaction->payment_status = "pending";
                    $transaction->save();

                    // Coupon is not applied
                    if ($couponId != null) {
                        // Save applied coupon
                        $appliedCoupon = new AppliedCoupon;
                        $appliedCoupon->applied_coupon_id = uniqid();
                        $appliedCoupon->transaction_id = $request->transaction_id;
                        $appliedCoupon->user_id = Auth::user()->id;
                        $appliedCoupon->coupon_id = $couponId;
                        $appliedCoupon->status = 0;
                        $appliedCoupon->save();
                    }

                    return redirect()->route('user.order.nfc.cards')->with('success', trans('Hold on some time! Your transaction details will be sent for verification. After the process, your plan will be activated.'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }
}
