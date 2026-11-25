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
use App\Plan;
use App\User;
use App\Coupon;
use App\Setting;
use App\Transaction;
use App\BusinessCard;
use App\NfcCardOrder;
use Razorpay\Api\Api;
use App\AppliedCoupon;
use App\NfcCardDesign;
use App\Classes\OrderNFC;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RazorpayController extends Controller
{
    // RazorPay
    public function nfcPrepareRazorpay($nfcId, $couponId, $quantity = 1)
    {
        if (Auth::user()) {
            // Queries
            $settings = Setting::where('status', 1)->first();
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();
            $userData = User::where('id', Auth::user()->id)->first();

            // Quantity
            $quantity = max((int) $quantity, 1);

            $config = DB::table('config')->get();

            if ($nfcDetails == null) {
                return view('errors.404');
            } else {
                $RAZOR_KEY = $config[6]->config_value;
                $RAZOR_SECRET = $config[7]->config_value;
                $api = new Api($RAZOR_KEY, $RAZOR_SECRET);

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

                $amountToBePaidPaise = round($amountToBePaid * 100); // Amount in paise

                try {
                    $order = $api->order->create(array('receipt' => $nfcTransactionId, 'amount' => (float) $amountToBePaidPaise, 'currency' => $config[1]->config_value)); // Creates order
                } catch (\Exception $e) {
                    $order = [];
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment method not supported!'));
                }

                // If order is created from RazorPay
                if (isset($order)) {
                    // Store transaction details in nfc_card_order_id table before redirecting to RazorPay
                    $nfcCardOrder = new NfcCardOrder();
                    $nfcCardOrder->nfc_card_order_id = $nfcCardOrderId;
                    $nfcCardOrder->user_id = Auth::id();
                    $nfcCardOrder->nfc_card_id = $nfcId;
                    $nfcCardOrder->nfc_card_order_transaction_id = $nfcTransactionId;
                    $nfcCardOrder->order_details = json_encode($quote->orderDetails());
                    $nfcCardOrder->delivery_address = json_encode(OrderQuote::deliveryAddress($userData));
                    $nfcCardOrder->delivery_note = "-";
                    $nfcCardOrder->order_status = 'pending';
                    $nfcCardOrder->status = 1;
                    $nfcCardOrder->save();

                    // Store transaction details in nfc_card_order_transactions table before redirecting to RazorPay
                    $transaction = new NfcCardOrderTransaction();
                    $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                    $transaction->nfc_card_order_id = $nfcCardOrderId;
                    $transaction->payment_transaction_id = $order->id;
                    $transaction->payment_method = "Razorpay";
                    $transaction->currency = $config[1]->config_value;
                    $transaction->amount = $amountToBePaid;
                    $transaction->invoice_details = json_encode($quote->invoiceDetails($userData));
                    $transaction->payment_status = "pending";
                    $transaction->save();

                    // Coupon is not applied
                    if ($couponId != " ") {
                        // Save applied coupon
                        $appliedCoupon = new AppliedCoupon;
                        $appliedCoupon->applied_coupon_id = uniqid();
                        $appliedCoupon->transaction_id = $order->id;
                        $appliedCoupon->user_id = Auth::user()->id;
                        $appliedCoupon->coupon_id = $couponId;
                        $appliedCoupon->status = 0;
                        $appliedCoupon->save();
                    }

                    return view('user.pages.order.nfc-card.checkout.pay-with-razorpay', compact('settings', 'nfcDetails', 'nfcTransactionId', 'order', 'config', 'quantity'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    // Update payment status
    public function nfcRazorpayPaymentStatus(Request $request, $oid, $paymentId)
    {
        if ($oid == "" || $paymentId == "") {
            // Payment failed
            $order = new OrderNFC();
            $order->paymentFailed($oid);

            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
        } else {
            $config = DB::table('config')->get();

            $RAZOR_KEY = $config[6]->config_value;
            $RAZOR_SECRET = $config[7]->config_value;
            $api = new Api($RAZOR_KEY, $RAZOR_SECRET);

            try {
                $payment = $api->payment->fetch($paymentId);
            } catch (\Exception $e) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }

            if ($payment->status == "authorized" || $payment->status == "captured") {
                // Place order
                $order = new OrderNFC();
                $order->order($oid, $payment);

                return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
            } else {
                // Payment failed
                $order = new OrderNFC();
                $order->paymentFailed($oid);

                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }
        }
    }
}
