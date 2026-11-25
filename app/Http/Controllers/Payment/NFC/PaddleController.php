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
use App\AppliedCoupon;
use App\Classes\OrderNFC;
use App\Coupon;
use App\Http\Controllers\Controller;
use App\NfcCardDesign;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Setting;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaddleController extends Controller
{
    public function __construct()
    {
        // Exclude status and webhook from auth middleware.
        // nfPaddlePaymentStatus is a redirect landing page — session may not be
        // fully restored when Paddle redirects back.
        // nfPaddlePaymentWebhook is called by Paddle's servers — no session exists.
        $this->middleware('auth')->except(['nfPaddlePaymentStatus', 'nfPaddlePaymentWebhook']);
    }

    private function getPaddleBillingBaseUrl($sandbox): string
    {
        return ($sandbox === 'true')
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    private function getPaddleBillingHeaders($apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    public function nfcGeneratePaymentLink(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        if (Auth::user()) {
            // Queries
            $config   = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();
            $userData = User::where('id', Auth::user()->id)->first();

            // Quantity
            $quantity = max((int) $quantity, 1);

            // NFC Card details
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            // Check nfc card details
            if ($nfcDetails == null) {
                return view('errors.404');
            }

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
            $nfcCardOrderId   = "OD" . preg_replace('/\D/', '', Str::uuid());
            $nfcTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            // Price: same calculation as the checkout page (App\Services\SmartProducts\OrderQuote)
            $quote             = OrderQuote::make($nfcDetails, $quantity, $couponDetails);
            $subtotal          = $quote->subtotal;
            $appliedTaxInTotal = $quote->tax;
            $discountPrice     = $quote->discount;
            $appliedCoupon     = $quote->couponCode;
            $amountToBePaid    = $quote->total;

            // Store transaction details in nfc_card_order_id table before redirecting to Paddle
            $nfcCardOrder                                    = new NfcCardOrder();
            $nfcCardOrder->nfc_card_order_id                 = $nfcCardOrderId;
            $nfcCardOrder->user_id                           = Auth::id();
            $nfcCardOrder->nfc_card_id                       = $nfcId;
            $nfcCardOrder->nfc_card_order_transaction_id     = $nfcTransactionId;
            $nfcCardOrder->order_details                     = json_encode($quote->orderDetails());
            $nfcCardOrder->delivery_address                  = json_encode(OrderQuote::deliveryAddress($userData));
            $nfcCardOrder->delivery_note                     = "-";
            $nfcCardOrder->order_status                      = 'pending';
            $nfcCardOrder->status                            = 1;
            $nfcCardOrder->save();

            // Store transaction details in nfc_card_order_transactions table before redirecting to Paddle
            $transaction                                     = new NfcCardOrderTransaction();
            $transaction->nfc_card_order_transaction_id      = $nfcTransactionId;
            $transaction->nfc_card_order_id                  = $nfcCardOrderId;
            $transaction->payment_transaction_id             = $nfcTransactionId;
            $transaction->payment_method                     = "Paddle";
            $transaction->currency                           = $config[1]->config_value;
            $transaction->amount                             = $amountToBePaid;
            $transaction->invoice_details                    = json_encode($quote->invoiceDetails($userData));
            $transaction->payment_status                     = "pending";
            $transaction->save();

            // Save applied coupon
            if ($couponId != " ") {
                $appliedCouponRecord                    = new AppliedCoupon;
                $appliedCouponRecord->applied_coupon_id = uniqid();
                $appliedCouponRecord->transaction_id    = $nfcTransactionId;
                $appliedCouponRecord->user_id           = Auth::user()->id;
                $appliedCouponRecord->coupon_id         = $couponId;
                $appliedCouponRecord->status            = 0;
                $appliedCouponRecord->save();
            }

            // ---------------------------------------------------------------
            // PADDLE BILLING – 3-step checkout
            // ---------------------------------------------------------------
            $client   = new Client();
            $sandbox  = $config[64]->config_value;
            $baseUrl  = $this->getPaddleBillingBaseUrl($sandbox);
            $apiKey   = $config[66]->config_value;
            $currency = strtoupper($config[1]->config_value);

            // Amount as string in lowest currency unit (e.g. cents: "1000" = $10.00)
            $amountInLowestUnit = (string)(float)round($amountToBePaid * 100);

            // Custom data carries our internal IDs so we can match on webhook/return
            $customData = [
                'user_id'        => Auth::user()->id,
                'transaction_id' => $nfcTransactionId,
            ];

            // Return URL — Paddle Billing appends ?_ptxn=<paddle_transaction_id>
            $returnUrl = route('nfc.paddle.payment.status');

            try {
                // -----------------------------------------------------------
                // STEP 1 — Create a Product
                // -----------------------------------------------------------
                $productResponse = $client->request('POST', $baseUrl . '/products', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => [
                        'name'         => $nfcDetails->nfc_card_name . ' NFC Card',
                        'description'  => 'NFC card purchase for ' . $nfcDetails->nfc_card_name,
                        'tax_category' => 'saas',
                    ],
                ]);

                $productData = json_decode($productResponse->getBody(), true);

                if (!isset($productData['data']['id'])) {
                    Log::info('Paddle Billing NFC: failed to create product', ['response' => $productData]);
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
                }

                $paddleProductId = $productData['data']['id'];

                // -----------------------------------------------------------
                // STEP 2 — Create a one-time Price
                // -----------------------------------------------------------
                $priceResponse = $client->request('POST', $baseUrl . '/prices', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => [
                        'product_id'  => $paddleProductId,
                        'name'        => $nfcDetails->nfc_card_name . ' NFC Card',
                        'description' => 'NFC card purchase for ' . $nfcDetails->nfc_card_name,
                        'unit_price'  => [
                            'amount'        => $amountInLowestUnit,
                            'currency_code' => $currency,
                        ],
                        'tax_mode' => 'external',
                        'quantity' => [
                            'minimum' => 1,
                            'maximum' => 1,
                        ],
                    ],
                ]);

                $priceData = json_decode($priceResponse->getBody(), true);

                if (!isset($priceData['data']['id'])) {
                    Log::info('Paddle Billing NFC: failed to create price', ['response' => $priceData]);
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
                }

                $paddlePriceId = $priceData['data']['id'];
                
                // -----------------------------------------------------------
                // STEP 3 — Create the Transaction
                // -----------------------------------------------------------
                $transactionPayload = [
                    'items' => [
                        [
                            'price_id' => $paddlePriceId,
                            'quantity' => 1,
                        ],
                    ],
                    'checkout' => [
                        'url' => $returnUrl,
                    ],
                    'custom_data' => $customData,
                    'customer'    => [
                        'email' => Auth::user()->email,
                    ],
                ];

                Log::info('Paddle Billing NFC: creating transaction', [
                    'price_id'   => $paddlePriceId,
                    'product_id' => $paddleProductId,
                    'amount'     => $amountInLowestUnit,
                    'currency'   => $currency,
                ]);

                $response = $client->request('POST', $baseUrl . '/transactions', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => $transactionPayload,
                ]);

                dd($response);

                if ($response->getStatusCode() == 201) {
                    $data = json_decode($response->getBody(), true);

                    Log::info('Paddle Billing NFC: transaction created', ['response' => $data]);

                    if (isset($data['data']['id'])) {
                        $paddleTransactionIdForCheckout = $data['data']['id'];
                        return view('user.pages.order.nfc-card.checkout.pay-with-paddle', compact(
                            'settings',
                            'nfcDetails',
                            'nfcTransactionId',
                            'data',
                            'config',
                            'paddleTransactionIdForCheckout',
                            'quantity'
                        ));
                    } else {
                        Log::info('Paddle Billing NFC: unexpected response format', ['response' => $data]);
                        return redirect()->route('user.order.nfc.cards')->with('failed', trans('Unexpected payment response format.'));
                    }
                } else {
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'no response body';
                Log::info('Paddle Billing NFC API Client Error', [
                    'status'  => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
                    'body'    => $responseBody,
                    'message' => $e->getMessage(),
                ]);
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
            } catch (\Exception $e) {
                Log::info('Paddle Billing NFC Payment Error', ['exception' => $e->getMessage()]);
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function nfPaddlePaymentStatus(Request $request)
    {
        // Queries
        $config = DB::table('config')->get();

        if ($request->has('_ptxn')) {
            $paddleTransactionId = $request->get('_ptxn');

            $client  = new \GuzzleHttp\Client();
            $sandbox = $config[64]->config_value;
            $baseUrl = $this->getPaddleBillingBaseUrl($sandbox);
            $apiKey  = $config[66]->config_value;

            try {
                $response = $client->request('GET', $baseUrl . '/transactions/' . $paddleTransactionId, [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                ]);

                $data = json_decode($response->getBody(), true);

                Log::info('Paddle Billing NFC: payment status check', [
                    'paddle_txn_id' => $paddleTransactionId,
                    'status'        => $data['data']['status'] ?? 'unknown',
                    'custom_data'   => $data['data']['custom_data'] ?? [],
                ]);

                // Retrieve our internal IDs from custom_data
                $customData     = $data['data']['custom_data'] ?? [];
                $user_id        = isset($customData['user_id']) ? (int) $customData['user_id'] : null;
                $transaction_id = $customData['transaction_id'] ?? null;

                Log::info('Paddle Billing NFC: custom_data parsed', [
                    'user_id'         => $user_id,
                    'transaction_id'  => $transaction_id,
                    'raw_custom_data' => $customData,
                ]);

                if (!$user_id || !$transaction_id) {
                    Log::info('Paddle Billing NFC: missing custom_data on return', ['data' => $data]);
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment verification failed. Please contact support.'));
                }

                // Paddle Billing statuses: draft, ready, billed, paid, completed, canceled, past_due
                $paddleStatus = $data['data']['status'] ?? '';

                if (in_array($paddleStatus, ['billed', 'paid', 'completed'])) {
                    // Place order
                    $order = new OrderNFC();
                    $order->order($transaction_id, $data);

                    return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
                } else {
                    // Update the transaction status to FAILED
                    $order = new OrderNFC();
                    $order->paymentFailed($transaction_id);

                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
                }
            } catch (\Exception $e) {
                Log::info('Paddle Billing NFC: payment status error', ['exception' => $e->getMessage()]);
                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Something went wrong!'));
            }
        }

        return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed!'));
    }

    public function nfPaddlePaymentWebhook(Request $request)
    {
        $config           = DB::table('config')->get();
        $webhookSecretKey = $config[67]->config_value ?? '';

        $rawBody         = $request->getContent();
        $signatureHeader = $request->header('Paddle-Signature', '');

        if (!empty($webhookSecretKey) && !empty($signatureHeader)) {
            $parts = [];
            foreach (explode(';', $signatureHeader) as $part) {
                [$k, $v]   = explode('=', $part, 2);
                $parts[$k] = $v;
            }

            $ts            = $parts['ts'] ?? '';
            $receivedHmac  = $parts['h1'] ?? '';
            $signedPayload = $ts . ':' . $rawBody;
            $expectedHmac  = hash_hmac('sha256', $signedPayload, $webhookSecretKey);

            if (!hash_equals($expectedHmac, $receivedHmac)) {
                Log::warning('Paddle Billing NFC webhook signature mismatch');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        // Decode webhook payload
        $webhookData = json_decode($rawBody, true);

        $eventType       = $webhookData['event_type'] ?? '';
        $transactionData = $webhookData['data'] ?? [];
        $paddleStatus    = $transactionData['status'] ?? '';

        // Retrieve our internal IDs from custom_data
        $customData     = $transactionData['custom_data'] ?? [];
        $user_id        = isset($customData['user_id']) ? (int) $customData['user_id'] : null;
        $transaction_id = $customData['transaction_id'] ?? null;

        if (!$user_id || !$transaction_id) {
            Log::warning('Paddle Billing NFC webhook missing custom_data', ['payload' => $webhookData]);
            return response()->json(['error' => 'Missing custom data'], 400);
        }

        // Handle completed / paid transactions
        if ($eventType === 'transaction.completed' || in_array($paddleStatus, ['billed', 'paid', 'completed'])) {
            $order = new OrderNFC();
            $order->order($transaction_id, $webhookData);
        }

        // Handle payment failed
        if ($eventType === 'transaction.payment_failed') {
            $order = new OrderNFC();
            $order->paymentFailed($transaction_id);
        }

        // Handle canceled
        if ($eventType === 'transaction.canceled' || $paddleStatus === 'canceled') {
            $order = new OrderNFC();
            $order->paymentFailed($transaction_id);
        }

        // Handle past_due / error / pending
        if (in_array($paddleStatus, ['past_due']) || in_array($eventType, ['transaction.updated'])) {
            $order = new OrderNFC();
            $order->paymentFailed($transaction_id);
        }

        // Paddle Billing expects a 200 OK response to acknowledge receipt
        return response()->json(['success' => true], 200);
    }
}
