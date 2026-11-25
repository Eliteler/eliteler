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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaytrController extends Controller
{
    private $merchant_id;
    private $merchant_key;
    private $merchant_salt;
    private $mode;

    public function __construct()
    {
        /** paytr api context **/
        $paytr_configuration = DB::table('config')->get();

        $this->merchant_id = $paytr_configuration[68]->config_value;
        $this->merchant_key = $paytr_configuration[69]->config_value;
        $this->merchant_salt = $paytr_configuration[70]->config_value;
        $this->mode = $paytr_configuration[71]->config_value;
    }

    public function nfcGeneratePaymentLink(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        if (Auth::user()) {
            // Queries
            $config = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();

            $userData = User::where('id', Auth::user()->id)->first();

            // Quantity
            $quantity = max((int) $quantity, 1);

            // Get nfc card details
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            // Check nfc card details
            if ($nfcDetails == null) {
                // Page redirect
                return redirect()->route('paytr.payment.failure')->with('failed', trans('Payment failed!'));
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

                // Store transaction details in nfc_card_order_id table before redirecting to Paytr
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

                // Store transaction details in nfc_card_order_transactions table before redirecting to Paytr
                $transaction = new NfcCardOrderTransaction();
                $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                $transaction->nfc_card_order_id = $nfcCardOrderId;
                $transaction->payment_transaction_id = $nfcTransactionId;
                $transaction->payment_method = "Paytr";
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
                    $appliedCoupon->transaction_id = $nfcTransactionId;
                    $appliedCoupon->user_id = Auth::user()->id;
                    $appliedCoupon->coupon_id = $couponId;
                    $appliedCoupon->status = 0;
                    $appliedCoupon->save();
                }

                $paymentData = [
                    'merchant_id' => $this->merchant_id,
                    'user_ip' => $request->ip(),
                    'merchant_oid' => $nfcTransactionId, // Unique order ID
                    'email' => $userData->billing_email,
                    'payment_amount' => round($amountToBePaidPaise), // Amount in cents
                    'user_basket' => base64_encode(json_encode([
                        ['NFC Card Purchase for' . $nfcDetails->nfc_card_name, $nfcDetails->nfc_card_price, $quantity],
                    ])),
                    'debug_on' => 1,
                    'no_installment' => 0,
                    'max_installment' => 0,
                    'currency' => 'TL',
                    'test_mode' => $this->mode,
                    'user_name' => Auth::user()->billing_name,
                    'user_address' => Auth::user()->billing_address,
                    'user_phone' => Auth::user()->billing_phone,
                    'merchant_ok_url' => route('nfc.paytr.payment.status'),
                    'merchant_fail_url' => route('nfc.paytr.payment.failure')
                ];

                // Generate paytr_token
                $hash_str = $this->merchant_id .
                    $paymentData['user_ip'] .
                    $paymentData['merchant_oid'] .
                    $paymentData['email'] .
                    $paymentData['payment_amount'] .
                    $paymentData['user_basket'] .
                    $paymentData['no_installment'] .
                    $paymentData['max_installment'] .
                    $paymentData['currency'] .
                    $paymentData['test_mode'];

                $paymentData['paytr_token'] = base64_encode(hash_hmac('sha256', $hash_str . $this->merchant_salt, $this->merchant_key, true));

                try {
                    // Send request to PayTR
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($paymentData));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $response = curl_exec($ch);

                    if (curl_errno($ch)) {
                        return back()->withErrors(['failed' => curl_error($ch)]);
                    }

                    curl_close($ch);

                    $result = json_decode($response, true);

                    if ($result['status'] === 'success') {
                        return view('user.pages.order.nfc-card.checkout.pay-with-paytr', [
                            'settings' => $settings,
                            'iframe_token' => $result['token'],
                            'quantity' => $quantity,
                        ]);
                    }
                } catch (\Exception $e) {
                    return back()->with(['failed' => trans('Something went wrong. Please try again later.')]);
                }

                return back()->with(['failed' => $result['reason']]);
            }
        } else {
            return redirect()->route('login');
        }
    }

    // PayTR Payment Status
    public function nfcPaytrPaymentStatus(Request $request)
    {
        // Check if the request is a PayTR background server notification (Webhook)
        if ($request->has('hash') && $request->has('status') && $request->has('total_amount')) {
            return $this->nfcPaytrPaymentWebhook($request);
        }

        $transactionId = $request->input('merchant_oid');

        // Restore user session if dropped by browser SameSite policy on PayTR cross-domain POST
        if (!Auth::check() && $transactionId) {
            $paymentDetails = NfcCardOrder::join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                ->where('nfc_card_order_transactions.payment_transaction_id', $transactionId)
                ->first();

            if ($paymentDetails) {
                Auth::loginUsingId($paymentDetails->user_id);
            }
        }

        if (Auth::check()) {
            // Get the user
            $user = Auth::user();

            // Retrieve transaction based on merchant_oid if available, or fetch the latest
            if ($transactionId) {
                $paymentDetails = NfcCardOrder::where('nfc_card_orders.user_id', $user->id)
                    ->join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                    ->where('nfc_card_order_transactions.payment_transaction_id', $transactionId)
                    ->first();
            } else {
                $paymentDetails = NfcCardOrder::where('nfc_card_orders.user_id', $user->id)
                    ->join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                    ->latest('nfc_card_order_transactions.created_at')
                    ->first();
            }

            if (!$paymentDetails) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed'));
            }

            try {
                // Security: Verify PayTR's notification
                $hash = base64_encode(hash_hmac(
                    'sha256',
                    $paymentDetails['nfc_card_order_transaction_id'] . $this->merchant_salt . $paymentDetails['payment_status'] . $paymentDetails['amount'],
                    $this->merchant_key,
                    true
                ));
            } catch (\Exception $e) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment was not verified. Please try again later.'));
            }

            // Queries
            $transactionId = $paymentDetails['nfc_card_order_transaction_id'];
            $paymentId = $paymentDetails['nfc_card_order_transaction_id'];
            $config = DB::table('config')->get();

            // Check payment status
            if ($paymentDetails['payment_status'] == "success" || $paymentDetails['payment_status'] == "pending") {
                return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
            } else {
                // Payment failed
                $order = new OrderNFC();
                $order->paymentFailed($transactionId);

                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }
        } else {
            // Page redirect
            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed'));
        }
    }

    // PayTR Payment Failure
    public function nfcPaytrPaymentFailure(Request $request)
    {
        $paymentDetails = $request->all();

        if (!$paymentDetails) {
            // Page redirect
            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment status update failed'));
        } else {
            // Payment failed
            $order = new OrderNFC();
            $order->paymentFailed($paymentDetails['merchant_oid']);

            // Page redirect
            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed'));
        }
    }

    // PayTR Payment Webhook
    public function nfcPaytrPaymentWebhook(Request $request)
    {
        $paymentDetails = $request->all();

        // Validate required fields
        $request->validate([
            'merchant_oid' => 'required|string',
            'status' => 'required|string',
            'total_amount' => 'required|numeric',
            'hash' => 'required|string',
        ]);

        $transactionId = $paymentDetails['merchant_oid'];

        // Verify PayTR's notification
        $hash = base64_encode(hash_hmac(
            'sha256',
            $paymentDetails['merchant_oid'] . $this->merchant_salt . $paymentDetails['status'] . $paymentDetails['total_amount'],
            $this->merchant_key,
            true
        ));

        if (!hash_equals($hash, $paymentDetails['hash'])) {
            return response('Invalid hash', 400);
        }

        // Retrieve transaction details (NFCCardOrderTransaction)
        $transaction_details = NfcCardOrderTransaction::where('payment_transaction_id', $transactionId)->first();
        if (!$transaction_details) {
            return response('Transaction not found', 404);
        }

        // Prevent duplicate processing if already completed
        if ($transaction_details->payment_status == 'success') {
            return response('OK', 200);
        }

        if ($paymentDetails['status'] !== 'success') {
            // Mark transaction as failed
            $order = new OrderNFC();
            $order->paymentFailed($transactionId);
            return response('Payment failed', 200);
        }

        try {
            // Place order
            $order = new OrderNFC();
            $order->order($transactionId, $paymentDetails);

            // Respond to PayTR
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('PayTR Webhook Error: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }
}
