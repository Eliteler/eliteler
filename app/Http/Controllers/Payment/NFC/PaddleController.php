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

    // -------------------------------------------------------------------------
    // PADDLE BILLING HELPERS
    // -------------------------------------------------------------------------

    /**
     * Return the Paddle Billing base API URL depending on sandbox flag.
     * Paddle Billing sandbox: https://sandbox-api.paddle.com
     * Paddle Billing live:    https://api.paddle.com
     */
    private function getPaddleBillingBaseUrl($sandbox): string
    {
        return ($sandbox === 'true')
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    /**
     * Build Guzzle headers for Paddle Billing (Bearer token auth).
     * $config[66] holds the Paddle API key (Bearer token).
     */
    private function getPaddleBillingHeaders($apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    // -------------------------------------------------------------------------
    // GENERATE PAYMENT LINK (Paddle Billing: create a checkout transaction)
    // -------------------------------------------------------------------------

    public function nfcGeneratePaymentLink(Request $request, $nfcId, $couponId)
    {
        if (Auth::user()) {
            // Queries
            $config   = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();
            $userData = User::where('id', Auth::user()->id)->first();

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

            // NFC Card Order ID
            $nfcCardOrderId   = "OD" . preg_replace('/\D/', '', Str::uuid());
            $nfcTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            // Check coupon type
            if ($couponDetails != null) {
                if ($couponDetails->coupon_type == 'fixed') {
                    $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);
                    $discountPrice     = $couponDetails->coupon_amount;
                    $amountToBePaid    = ($nfcDetails->nfc_card_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid    = (float)number_format($amountToBePaid, 2, '.', '');
                    $appliedCoupon     = $couponDetails->coupon_code;
                } else {
                    $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);
                    $discountPrice     = $nfcDetails->nfc_card_price * $couponDetails->coupon_amount / 100;
                    $amountToBePaid    = ($nfcDetails->nfc_card_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid    = (float)number_format($amountToBePaid, 2, '.', '');
                    $appliedCoupon     = $couponDetails->coupon_code;
                }
            } else {
                $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);
                $amountToBePaid    = ($nfcDetails->nfc_card_price + $appliedTaxInTotal);
            }

            // Store transaction details in nfc_card_order_id table before redirecting to Paddle
            $nfcCardOrder                                    = new NfcCardOrder();
            $nfcCardOrder->nfc_card_order_id                 = $nfcCardOrderId;
            $nfcCardOrder->user_id                           = Auth::id();
            $nfcCardOrder->nfc_card_id                       = $nfcId;
            $nfcCardOrder->nfc_card_order_transaction_id     = $nfcTransactionId;
            $nfcCardOrder->order_details                     = json_encode($this->prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice));
            $nfcCardOrder->delivery_address                  = json_encode($this->prepareDeliveryAddress($userData));
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
            $transaction->invoice_details                    = json_encode($this->prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice));
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
            $amountInLowestUnit = (string)(int)round($amountToBePaid * 100);

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
                            'paddleTransactionIdForCheckout'
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

    // -------------------------------------------------------------------------
    // PAYMENT STATUS (return URL handler)
    // Paddle Billing appends ?_ptxn=<paddle_transaction_id> to the return URL.
    // -------------------------------------------------------------------------

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
                // ---------------------------------------------------------------
                // PADDLE BILLING – Retrieve transaction by ID
                // GET /transactions/{id}
                // custom_data contains our internal user_id and transaction_id
                // ---------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // PADDLE BILLING WEBHOOK
    // Paddle Billing sends JSON with an "event_type" field.
    // Docs: https://developer.paddle.com/webhooks/overview
    // -------------------------------------------------------------------------

    public function nfPaddlePaymentWebhook(Request $request)
    {
        // -----------------------------------------------------------------------
        // PADDLE BILLING – Verify webhook signature
        // Signature header: Paddle-Signature
        // Format:           ts=<timestamp>;h1=<hmac-sha256-hex>
        // Store webhook secret in config[67]
        // -----------------------------------------------------------------------
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

        // -----------------------------------------------------------------------
        // Paddle Billing event types:
        //   transaction.completed      → payment succeeded
        //   transaction.payment_failed → payment failed
        //   transaction.canceled       → transaction canceled
        // -----------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS (unchanged)
    // -------------------------------------------------------------------------

    private function prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice)
    {
        return [
            'from_billing_name'    => $config[16]->config_value,
            'from_billing_address' => $config[19]->config_value,
            'from_billing_city'    => $config[20]->config_value,
            'from_billing_state'   => $config[21]->config_value,
            'from_billing_zipcode' => $config[22]->config_value,
            'from_billing_country' => $config[23]->config_value,
            'from_vat_number'      => $config[26]->config_value,
            'from_billing_phone'   => $config[18]->config_value,
            'from_billing_email'   => $config[17]->config_value,
            'to_billing_name'      => $userData->billing_name,
            'to_billing_address'   => $userData->billing_address,
            'to_billing_city'      => $userData->billing_city,
            'to_billing_state'     => $userData->billing_state,
            'to_billing_zipcode'   => $userData->billing_zipcode,
            'to_billing_country'   => $userData->billing_country,
            'to_billing_phone'     => $userData->billing_phone,
            'to_billing_email'     => $userData->billing_email,
            'to_vat_number'        => $userData->vat_number,
            'tax_name'             => $config[24]->config_value,
            'tax_type'             => $config[14]->config_value,
            'tax_value'            => $config[25]->config_value,
            'applied_coupon'       => $appliedCoupon,
            'discounted_price'     => $discountPrice,
            'invoice_amount'       => $amountToBePaid,
            'subtotal'             => $nfcDetails->nfc_card_price,
            'tax_amount'           => (float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100,
        ];
    }

    private function prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice)
    {
        return [
            'nfc_card_id'       => $nfcDetails->nfc_card_id,
            'order_item'        => $nfcDetails->nfc_card_name,
            'order_description' => $nfcDetails->nfc_card_description,
            'order_quantity'    => 1,
            'price'             => $nfcDetails->nfc_card_price,
            'invoice_amount'    => $amountToBePaid,
            'tax_name'          => $config[24]->config_value,
            'tax_type'          => $config[14]->config_value,
            'tax_value'         => $config[25]->config_value,
            'tax_amount'        => (float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100,
            'applied_coupon'    => $appliedCoupon,
            'discounted_price'  => $discountPrice,
            'subtotal'          => $nfcDetails->nfc_card_price,
        ];
    }

    private function prepareDeliveryAddress($userData)
    {
        return [
            'billing_name'     => $userData->billing_name,
            'billing_address'  => $userData->billing_address,
            'billing_city'     => $userData->billing_city,
            'billing_state'    => $userData->billing_state,
            'billing_zipcode'  => $userData->billing_zipcode,
            'billing_country'  => $userData->billing_country,
            'billing_phone'    => $userData->billing_phone,
            'billing_email'    => $userData->billing_email,
            'shipping_name'    => $userData->billing_name,
            'shipping_address' => $userData->billing_address,
            'shipping_city'    => $userData->billing_city,
            'shipping_state'   => $userData->billing_state,
            'shipping_zipcode' => $userData->billing_zipcode,
            'shipping_country' => $userData->billing_country,
            'shipping_phone'   => $userData->billing_phone,
            'shipping_email'   => $userData->billing_email,
            'type'             => $userData->type,
            'vat_number'       => $userData->vat_number,
        ];
    }
}
