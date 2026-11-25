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
use App\NfcCardOrder;
use App\AppliedCoupon;
use App\NfcCardDesign;
use GuzzleHttp\Client;
use App\Classes\OrderNFC;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FlutterwaveController extends Controller
{
    protected $secretKey;
    protected $hashKey;
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        // Get API key and category code from config table
        $config = DB::table('config')->get();

        $this->secretKey = $config[52]->config_value;
        $this->hashKey = $config[96]->config_value;
        $this->baseUrl = "https://api.flutterwave.com/v3";
    }

    public function nfcPrepareFlutterwave(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        // Check if user is logged in
        if (Auth::user()) {
            // Queries
            $config = DB::table('config')->get();

            // Get user details
            $userData = User::where('id', Auth::user()->id)->first();

            // Quantity
            $quantity = max((int) $quantity, 1);

            // Get nfc card details
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            if (!$nfcDetails) {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Invalid NFC card!'));
            }

            // Check nfc card details
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

                $amountToBePaidPaise = $amountToBePaid;

                $client = new Client();

                $data = [
                    'tx_ref' => $nfcTransactionId,
                    'amount' => $amountToBePaidPaise, // Ex: 59.0
                    'currency' => $config[1]->config_value, // Ex: USD
                    'redirect_url' => route('nfc.flutterwave.payment.status'), // Redirect URL: http://127.0.0.1:8000/nfc/flutterwave-payment-status
                    'customer' => [
                        'email' => Auth::user()->email, // Ex: test@test.com
                        'name' => Auth::user()->name, // Ex: Test User
                        'phone_number' => Auth::user()->billing_phone == null ? '9876543210' : Auth::user()->billing_phone // Ex: 9876543210
                    ],
                    'customizations' => [
                        'title' => config('app.name'), // Ex: GoBiz
                        'logo' => asset('img/favicon.png'), // Ex: http://127.0.0.1:8000/img/favicon.png
                    ]
                ];

                try {
                    $response = $client->post("{$this->baseUrl}/payments", [ // URL: https://api.flutterwave.com/v3/payments
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->secretKey,  // Correct Bearer Token
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $data
                    ]);

                    $responseBody = json_decode($response->getBody(), true);

                    if ($responseBody['status'] === 'success') {

                        // Store transaction details in nfc_card_order_id table before redirecting to PayPal
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

                        // Store transaction details in nfc_card_order_transactions table before redirecting to PayPal
                        $transaction = new NfcCardOrderTransaction();
                        $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                        $transaction->nfc_card_order_id = $nfcCardOrderId;
                        $transaction->payment_transaction_id = $nfcTransactionId;
                        $transaction->payment_method = "Flutterwave";
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

                        return redirect($responseBody['data']['link']);
                    }

                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment initiation failed'));
                } catch (\Exception $e) {
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Failed to initiate payment.'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    // Update status
    public function nfcFlutterwavePaymentStatus(Request $request)
    {
        // Get transaction id from the request
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        // Transaction success
        if ($status == "successful") {
            // Check if the transaction is already verified
            $transactionId = $request->query('transaction_id');

            if ($transactionId) {
                $client = new Client();

                try {
                    $response = $client->get("{$this->baseUrl}/transactions/{$transactionId}/verify", [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->secretKey,
                            'Content-Type' => 'application/json',
                        ]
                    ]);

                    $verificationResponse = json_decode($response->getBody(), true);

                    // Get tx_ref and flw_ref
                    $tx_ref = $verificationResponse['data']['tx_ref'];
                    $flw_ref = $verificationResponse['data']['flw_ref'];

                    if ($verificationResponse['status'] === 'success') {
                        // Place order
                        $order = new OrderNFC();
                        $order->order($tx_ref, $flw_ref);

                        return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
                    }

                    // Handle failed payment
                    $order = new OrderNFC();
                    $order->paymentFailed($tx_ref);
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed.'));
                } catch (\Exception $e) {
                    // Handle failed payment
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment verification failed.'));
                }
            } else {
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Transaction not found.'));
            }
        } elseif ($status === 'failed') {
            // Handle failed payment
            $order = new OrderNFC();
            $order->paymentFailed($txRef);

            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Transaction failed.'));
        } elseif ($status === 'cancelled') {
            // Update transaction details
            $order = new OrderNFC();
            $order->paymentFailed($txRef);

            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Transaction cancelled.'));
        }

        return redirect()->route('user.order.nfc.cards')->with('failed', trans('Invalid transaction status.'));
    }

    // Callback
    public function nfcFlutterwaveCallback(Request $request)
    {
        // Verify Flutterwave signature
        $secretHash = $this->hashKey;
        $signature = $request->header('flutterwave-signature');

        if (!$signature || $signature !== $secretHash) {
            abort(401);
        }

        Log::info('Flutterwave Webhook:', $request->all());

        $payload = $request->all();

        // Flutterwave webhook events come as "charge.completed"
        if (isset($payload['event']) && $payload['event'] === 'charge.completed') {

            $data = $payload['data'];

            $tx_ref = $data['tx_ref'] ?? null;
            $flw_ref = $data['flw_ref'] ?? null;
            $status = $data['status'] ?? null;
            $processor_response = $data['processor_response'] ?? null;

            if ($status === 'successful' && $processor_response === 'Transaction completed') {

                // Place order
                $order = new OrderNFC();
                $order->order($tx_ref, $flw_ref);

                return response('OK', 200);
            }

            if ($status === 'failed') {
                // Handle failed payment
                $order = new OrderNFC();
                $order->paymentFailed($tx_ref);

                return response('OK', 200);
            }
        }

        return response('OK', 200);
    }
}
