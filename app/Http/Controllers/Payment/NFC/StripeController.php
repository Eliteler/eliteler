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
use App\Classes\OrderNFC;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    public function nfcStripeCheckout(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        if (Auth::user()) {
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();

            // Quantity
            $quantity = max((int) $quantity, 1);

            $settings = Setting::where('status', 1)->first();
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            if ($nfcDetails == null) {
                return view('errors.404');
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

                $amountToBePaidPaise = round($amountToBePaid * 100);

                \Stripe\Stripe::setApiKey($config[10]->config_value);

                $currency = Str::lower($config[1]->config_value); // 'usd' or 'eur'
                $paymentMethods = $currency === 'eur' ? ['bancontact', 'ideal', 'sepa_debit', 'card'] : ['card'];

                $payment_intent = \Stripe\PaymentIntent::create([
                    'description' => $nfcDetails->nfc_card_name . " NFC Card",
                    'shipping' => [
                        'name' => Auth::user()->name,
                        'address' => [
                            'line1' => Auth::user()->billing_address,
                            'postal_code' => Auth::user()->billing_zipcode,
                            'city' => Auth::user()->billing_city,
                            'state' => Auth::user()->billing_state,
                            'country' => Auth::user()->billing_country,
                        ],
                    ],
                    'amount' => $amountToBePaidPaise,
                    'currency' => $config[1]->config_value,
                    'payment_method_types' => $paymentMethods, // Default methods
                ]);

                $invoice_details = [];

                $invoice_details['from_billing_name'] = $config[16]->config_value;
                $invoice_details['from_billing_address'] = $config[19]->config_value;
                $invoice_details['from_billing_city'] = $config[20]->config_value;
                $invoice_details['from_billing_state'] = $config[21]->config_value;
                $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
                $invoice_details['from_billing_country'] = $config[23]->config_value;
                $invoice_details['from_vat_number'] = $config[26]->config_value;
                $invoice_details['from_billing_phone'] = $config[18]->config_value;
                $invoice_details['from_billing_email'] = $config[17]->config_value;
                $invoice_details['to_billing_name'] = $userData->billing_name;
                $invoice_details['to_billing_address'] = $userData->billing_address;
                $invoice_details['to_billing_city'] = $userData->billing_city;
                $invoice_details['to_billing_state'] = $userData->billing_state;
                $invoice_details['to_billing_zipcode'] = $userData->billing_zipcode;
                $invoice_details['to_billing_country'] = $userData->billing_country;
                $invoice_details['to_billing_phone'] = $userData->billing_country_code . $userData->billing_phone;
                $invoice_details['to_billing_email'] = $userData->billing_email;
                $invoice_details['to_vat_number'] = $userData->vat_number;
                $invoice_details['subtotal'] = $subtotal;
                $invoice_details['tax_name'] = $config[24]->config_value;
                $invoice_details['tax_type'] = $config[14]->config_value;
                $invoice_details['tax_value'] = $config[25]->config_value;
                $invoice_details['tax_amount'] = $appliedTaxInTotal;
                $invoice_details['applied_coupon'] = $appliedCoupon;
                $invoice_details['discounted_price'] = $discountPrice;
                $invoice_details['invoice_amount'] = $amountToBePaid;

                $intent = $payment_intent->client_secret;
                $paymentId = $payment_intent->id;

                // If order is created from stripe
                if (isset($intent)) {

                    // Store transaction details in nfc_card_order_id table before redirecting to Stripe
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

                    // Store transaction details in nfc_card_order_transactions table before redirecting to Stripe
                    $transaction = new NfcCardOrderTransaction();
                    $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                    $transaction->nfc_card_order_id = $nfcCardOrderId;
                    $transaction->payment_transaction_id = $paymentId;
                    $transaction->payment_method = "Stripe";
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
                        $appliedCoupon->transaction_id = $paymentId;
                        $appliedCoupon->user_id = Auth::user()->id;
                        $appliedCoupon->coupon_id = $couponId;
                        $appliedCoupon->status = 0;
                        $appliedCoupon->save();
                    }

                    return view('user.pages.order.nfc-card.checkout.pay-with-stripe', compact('settings', 'intent', 'nfcDetails', 'nfcTransactionId', 'config', 'paymentId', 'quantity'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    // Update payment status
    public function nfcStripePaymentStatus(Request $request, $paymentId)
    {
        if (!$paymentId) {
            return view('errors.404');
        } else {
            $orderId = $paymentId;
            $config = DB::table('config')->get();
            $stripe = new \Stripe\StripeClient($config[10]->config_value);

            try {
                $payment = $stripe->paymentIntents->retrieve($paymentId, []);
            } catch (\Exception $e) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }

            if ($payment->status == "succeeded") {
                // Place order
                $order = new OrderNFC();
                $order->order($orderId, $payment);

                return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
            } else {
                // Payment failed
                $order = new OrderNFC();
                $order->paymentFailed($orderId);

                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }
        }
    }

    // Cancel payment
    public function nfcStripePaymentCancel(Request $request, $paymentId)
    {
        if (!$paymentId) {
            return view('errors.404');
        } else {
            $config = DB::table('config')->get();
            $stripe = new \Stripe\StripeClient($config[10]->config_value);

            try {
                $payment = $stripe->paymentIntents->cancel($paymentId, []);
            } catch (\Exception $e) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment cancelled!'));
            }

            // Payment failed
            $order = new OrderNFC();
            $order->paymentFailed($paymentId);

            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment cancelled!'));
        }
    }
}
