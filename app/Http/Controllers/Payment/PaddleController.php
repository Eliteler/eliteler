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

namespace App\Http\Controllers\Payment;

use App\Plan;
use App\User;
use App\Coupon;
use App\Setting;
use App\Referral;
use Carbon\Carbon;
use App\Transaction;
use App\BusinessCard;
use App\AppliedCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaddleController extends Controller
{
    public function __construct()
    {
        // Exclude paddlePaymentStatus and paddlePaymentWebhook from auth middleware.
        // paddlePaymentStatus is a redirect landing page — the session may not be
        // fully restored by the time Paddle redirects back, causing auth failures.
        // We use custom_data from the Paddle API (not Auth::user()) in that method.
        // paddlePaymentWebhook is called by Paddle's servers — no session exists.
        $this->middleware('auth')->except(['paddlePaymentStatus', 'paddlePaymentWebhook']);
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
     * $config[66] still holds the Paddle API key (previously vendor_auth_code).
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
    // GENERATE PAYMENT LINK  (Paddle Billing: create a checkout transaction)
    // -------------------------------------------------------------------------

    public function generatePaymentLink(Request $request, $planId, $couponId)
    {
        if (Auth::user()) {
            // Queries
            $config   = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();
            $plan_details = Plan::where('plan_id', $planId)->where('status', 1)->first();
            $settings = Setting::where('status', 1)->first();

            // Check plan details
            if ($plan_details == null) {
                return view('errors.404');
            }

            $gobiz_transaction_id = uniqid();

            // Check applied coupon
            $couponDetails = Coupon::where('used_for', 'plan')->where('coupon_id', $couponId)->first();

            // Applied tax in total
            $appliedTaxInTotal = 0;

            // Discount price
            $discountPrice = 0;

            // Applied coupon
            $appliedCoupon = null;

            // Check coupon type
            if ($couponDetails != null) {
                if ($couponDetails->coupon_type == 'fixed') {
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                    $discountPrice     = $couponDetails->coupon_amount;
                    $amountToBePaid    = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid    = (float)number_format($amountToBePaid, 2, '.', '');
                    $appliedCoupon     = $couponDetails->coupon_code;
                } else {
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                    $discountPrice     = $plan_details->plan_price * $couponDetails->coupon_amount / 100;
                    $amountToBePaid    = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid    = (float)number_format($amountToBePaid, 2, '.', '');
                    $appliedCoupon     = $couponDetails->coupon_code;
                }
            } else {
                $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                $amountToBePaid    = ($plan_details->plan_price + $appliedTaxInTotal);
            }

            // Transaction ID
            $transactionId = uniqid();

            // Generate JSON
            $invoice_details = [];

            $invoice_details['from_billing_name']    = $config[16]->config_value;
            $invoice_details['from_billing_address'] = $config[19]->config_value;
            $invoice_details['from_billing_city']    = $config[20]->config_value;
            $invoice_details['from_billing_state']   = $config[21]->config_value;
            $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
            $invoice_details['from_billing_country'] = $config[23]->config_value;
            $invoice_details['from_vat_number']      = $config[26]->config_value;
            $invoice_details['from_billing_phone']   = $config[18]->config_value;
            $invoice_details['from_billing_email']   = $config[17]->config_value;
            $invoice_details['to_billing_name']      = $userData->billing_name;
            $invoice_details['to_billing_address']   = $userData->billing_address;
            $invoice_details['to_billing_city']      = $userData->billing_city;
            $invoice_details['to_billing_state']     = $userData->billing_state;
            $invoice_details['to_billing_zipcode']   = $userData->billing_zipcode;
            $invoice_details['to_billing_country']   = $userData->billing_country;
            $invoice_details['to_billing_phone']     = $userData->billing_phone;
            $invoice_details['to_billing_email']     = $userData->billing_email;
            $invoice_details['to_vat_number']        = $userData->vat_number;
            $invoice_details['subtotal']             = $plan_details->plan_price;
            $invoice_details['tax_name']             = $config[24]->config_value;
            $invoice_details['tax_type']             = $config[14]->config_value;
            $invoice_details['tax_value']            = $config[25]->config_value;
            $invoice_details['tax_amount']           = $appliedTaxInTotal;
            $invoice_details['applied_coupon']       = $appliedCoupon;
            $invoice_details['discounted_price']     = $discountPrice;
            $invoice_details['invoice_amount']       = $amountToBePaid;

            // Save transactions
            $transaction = new Transaction();
            $transaction->gobiz_transaction_id    = $gobiz_transaction_id;
            $transaction->transaction_date        = now();
            $transaction->transaction_id          = $transactionId;
            $transaction->user_id                 = Auth::user()->id;
            $transaction->plan_id                 = $plan_details->plan_id;
            $transaction->desciption              = $plan_details->plan_name . " Plan";
            $transaction->payment_gateway_name    = "Paddle";
            $transaction->transaction_amount      = $amountToBePaid;
            $transaction->transaction_currency    = $config[1]->config_value;
            $transaction->invoice_details         = json_encode($invoice_details);
            $transaction->payment_status          = "PENDING";
            $transaction->save();

            // Save applied coupon
            if ($couponId != " ") {
                $appliedCouponRecord                    = new AppliedCoupon;
                $appliedCouponRecord->applied_coupon_id = uniqid();
                $appliedCouponRecord->transaction_id    = $transactionId;
                $appliedCouponRecord->user_id           = Auth::user()->id;
                $appliedCouponRecord->coupon_id         = $couponId;
                $appliedCouponRecord->status            = 0;
                $appliedCouponRecord->save();
            }

            // ---------------------------------------------------------------
            // PADDLE BILLING – Create a checkout transaction
            // POST /transactions
            // Docs: https://developer.paddle.com/api-reference/transactions/create-transaction
            // ---------------------------------------------------------------
            $client  = new \GuzzleHttp\Client();
            $sandbox = $config[64]->config_value;
            $baseUrl = $this->getPaddleBillingBaseUrl($sandbox);
            $apiKey  = $config[66]->config_value; // Paddle API key (Bearer token)

            // Amount must be in the lowest currency unit as a string (e.g. cents).
            // Paddle Billing expects unit_price.amount as a string in the lowest
            // denomination (e.g. "1000" = $10.00 for USD).
            $amountInLowestUnit = (string)(int)round($amountToBePaid * 100);

            // Custom data carries our internal IDs so we can match on webhook/return.
            $customData = [
                'user_id'        => Auth::user()->id,
                'transaction_id' => $transactionId,
            ];

            // Return URL — Paddle Billing REPLACES the entire query string with
            // ?_ptxn=<paddle_transaction_id> on redirect. Do NOT rely on any
            // custom query params here — they will be lost.
            // We retrieve our internal IDs from custom_data via the Paddle API instead.
            $returnUrl = route('paddle.payment.status');

            try {
                // ---------------------------------------------------------------
                // STEP 1 — Create a Product in Paddle Billing
                // POST /products
                // Paddle Billing does not allow fully inline product+price inside
                // /transactions. We must create a product first, then a price,
                // then attach the price_id to the transaction.
                // ---------------------------------------------------------------
                $productResponse = $client->request('POST', $baseUrl . '/products', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => [
                        'name'         => $plan_details->plan_name . ' Plan',
                        'description'  => 'Plan purchase for ' . $plan_details->plan_name,
                        'tax_category' => 'saas',
                    ],
                ]);

                $productData = json_decode($productResponse->getBody(), true);

                if (!isset($productData['data']['id'])) {
                    Log::info('Paddle Billing: failed to create product', ['response' => $productData]);
                    return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
                }

                $paddleProductId = $productData['data']['id'];

                // ---------------------------------------------------------------
                // STEP 2 — Create a one-time Price linked to that Product
                // POST /prices
                // amount must be a STRING in the lowest currency unit (e.g. cents)
                // "1000" = $10.00 USD
                // ---------------------------------------------------------------
                // Currency must be a valid ISO 4217 code supported by Paddle Billing.
                // Paddle Billing sandbox supports: USD, EUR, GBP, AUD, CAD, CHF,
                // DKK, HUF, NOK, PLN, SEK, SGD, ZAR, MXN, BRL, INR, JPY, KRW, TRY
                // If your config currency is not in this list, override to USD.
                $currency = strtoupper($config[1]->config_value);

                $priceResponse = $client->request('POST', $baseUrl . '/prices', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => [
                        'product_id'  => $paddleProductId,
                        'description' => $plan_details->plan_name . ' Plan — one-time',
                        'name'        => $plan_details->plan_name . ' Plan',
                        'unit_price'  => [
                            // amount is a STRING in the lowest currency unit
                            // e.g. USD/EUR/GBP: cents → "1000" = $10.00
                            // JPY/KRW: already whole units → multiply by 1 not 100
                            'amount'        => $amountInLowestUnit,
                            'currency_code' => $currency,
                        ],
                        // tax_mode:
                        //   'inclusive' = tax is included in unit_price (most common)
                        //   'external'  = you handle tax yourself, Paddle shows price as-is
                        // Use 'external' so Paddle does not recalculate or add tax on top
                        'tax_mode' => 'external',
                        // Omit billing_cycle entirely for one-time (non-recurring) prices
                        'quantity' => [
                            'minimum' => 1,
                            'maximum' => 1,
                        ],
                    ],
                ]);

                $priceData = json_decode($priceResponse->getBody(), true);

                if (!isset($priceData['data']['id'])) {
                    Log::info('Paddle Billing: failed to create price', ['response' => $priceData]);
                    return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
                }

                $paddlePriceId = $priceData['data']['id'];

                // ---------------------------------------------------------------
                // STEP 3 — Create the Transaction using the price_id
                // POST /transactions
                // Now items uses price_id (not an inline price object)
                // ---------------------------------------------------------------
                // Build transaction payload
                // NOTE: Do NOT pass currency_code on the transaction — Paddle
                // derives it from the price. Passing it can cause a mismatch
                // that results in allowed_payment_methods: [] (no payment methods).
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
                ];

                // Only add customer email — do NOT pass address/country here
                // as it can restrict available payment methods in sandbox
                $transactionPayload['customer'] = [
                    'email' => Auth::user()->email,
                ];

                Log::info('Paddle Billing: creating transaction', [
                    'price_id'   => $paddlePriceId,
                    'product_id' => $paddleProductId,
                    'amount'     => $amountInLowestUnit,
                    'currency'   => strtoupper($config[1]->config_value),
                    'payload'    => $transactionPayload,
                ]);

                $response = $client->request('POST', $baseUrl . '/transactions', [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json'    => $transactionPayload,
                ]);

                if ($response->getStatusCode() == 201) {
                    $data = json_decode($response->getBody(), true);

                    Log::info('Paddle Billing: transaction created', ['response' => $data]);

                    // Paddle Billing transaction ID (e.g. txn_01xxx) — used by JS overlay
                    // The checkout URL is used only for redirect-based checkout (not overlay)
                    if (isset($data['data']['id'])) {
                        $paddleTransactionIdForCheckout = $data['data']['id'];
                        return view('user.pages.checkout.pay-with-paddle', compact(
                            'settings',
                            'plan_details',
                            'gobiz_transaction_id',
                            'data',
                            'config',
                            'paddleTransactionIdForCheckout'
                        ));
                    } else {
                        Log::info('Unexpected Paddle Billing response format', ['response' => $data]);
                        return redirect()->route('user.plans')->with('failed', trans('Unexpected payment response format.'));
                    }
                } else {
                    return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // 4xx – log Paddle's full validation error body for debugging
                $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'no response body';
                Log::info('Paddle Billing API Client Error', [
                    'status'  => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
                    'body'    => $responseBody,
                    'message' => $e->getMessage(),
                ]);
                return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
            } catch (\Exception $e) {
                Log::info('Paddle Billing Payment Error', ['exception' => $e->getMessage()]);
                return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    // -------------------------------------------------------------------------
    // PAYMENT STATUS (return URL handler)
    // Paddle Billing appends ?_ptxn=<paddle_transaction_id> to the return URL.
    // -------------------------------------------------------------------------

    public function paddlePaymentStatus(Request $request)
    {
        // Queries
        $config = DB::table('config')->get();

        // Paddle Billing appends ?_ptxn=<paddle_transaction_id> to the return URL.
        // All other query params are stripped — do NOT use passthrough here.
        // We fetch our internal IDs from custom_data via the Paddle API instead.
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

                Log::info('Paddle Billing: payment status check', [
                    'paddle_txn_id' => $paddleTransactionId,
                    'status'        => $data['data']['status'] ?? 'unknown',
                    'custom_data'   => $data['data']['custom_data'] ?? [],
                ]);

                // Retrieve our internal IDs from custom_data (set during transaction creation)
                $customData = $data['data']['custom_data'] ?? [];

                // Cast user_id to int — Paddle returns custom_data values as strings
                $user_id        = isset($customData['user_id']) ? (int) $customData['user_id'] : null;
                $transaction_id = $customData['transaction_id'] ?? null;

                Log::info('Paddle Billing: custom_data parsed', [
                    'user_id'         => $user_id,
                    'transaction_id'  => $transaction_id,
                    'raw_custom_data' => $customData,
                ]);

                if (!$user_id || !$transaction_id) {
                    Log::info('Paddle Billing: missing custom_data on return', ['data' => $data]);
                    return redirect()->route('user.plans')->with('failed', trans('Payment verification failed. Please contact support.'));
                }

                // Paddle Billing transaction statuses: draft, ready, billed, paid, completed, canceled, past_due
                $paddleStatus = $data['data']['status'] ?? '';

                if (in_array($paddleStatus, ['billed', 'paid', 'completed'])) {
                    // Get transaction details based on the transaction_id
                    $transaction_details = Transaction::where('transaction_id', $transaction_id)->first();

                    if (!$transaction_details) {
                        return redirect()->route('user.plans')->with('failed', trans('Transaction not found or already processed.'));
                    }

                    // Get user details
                    $user_details = User::find($user_id);

                    // Get plan data
                    $plan_data = Plan::where('plan_id', $transaction_details->plan_id)->first();
                    $term_days = (int) $plan_data->validity;

                    if ($config[80]->config_value == '1') {
                        $referralCalculation                   = [];
                        $referralCalculation['referral_type']  = $config[81]->config_value;
                        $referralCalculation['referral_value'] = $config[82]->config_value;

                        if ($config[81]->config_value == '0') {
                            $base_amount                            = (float) $transaction_details->transaction_amount;
                            $referralCalculation['referral_amount'] = ($base_amount * $referralCalculation['referral_value']) / 100;
                        } else {
                            $referralCalculation['referral_amount'] = $referralCalculation['referral_value'];
                        }

                        // Use $user_id from custom_data — Auth::user() is not available here
                        if (Referral::where('user_id', $user_id)->where('is_subscribed', 0)->first()) {
                            Referral::where('user_id', $user_id)->update([
                                'is_subscribed'   => 1,
                                'referral_scheme' => json_encode($referralCalculation),
                                'updated_at'      => now(),
                            ]);
                        }
                    }

                    if ($user_details->plan_validity == "") {
                        if ($term_days == "9999") {
                            $plan_validity = "2050-12-30 23:23:59";
                        } else {
                            $plan_validity = Carbon::now();
                            $plan_validity->addDays($term_days);
                        }

                        $invoice_count  = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
                        $invoice_number = $invoice_count + 1;

                        Transaction::where('transaction_id', $transaction_id)->update([
                            'transaction_id' => $paddleTransactionId,
                            'invoice_prefix' => $config[15]->config_value,
                            'invoice_number' => $invoice_number,
                            'payment_status' => 'SUCCESS',
                            'updated_at'     => now(),
                        ]);

                        if ($user_details) {
                            $user_details->plan_id              = $transaction_details->plan_id;
                            $user_details->term                 = $term_days;
                            $user_details->plan_validity        = $plan_validity;
                            $user_details->plan_activation_date = now();
                            $user_details->plan_details         = $plan_data;
                            $user_details->save();
                        }

                        AppliedCoupon::where('transaction_id', $transaction_id)->update(['status' => 1]);

                        $message = trans('Plan activation success!');
                    } else {
                        $plan_validity  = Carbon::createFromFormat('Y-m-d H:i:s', $user_details->plan_validity);
                        $current_date   = Carbon::now();
                        $remaining_days = $current_date->diffInDays($plan_validity, false);

                        // Check plan id
                        if ($user_details->plan_id == $transaction_details->plan_id) {
                            if ($remaining_days > 0) {
                                if ($term_days == "9999") {
                                    $plan_validity = "2050-12-30 23:23:59";
                                } else {
                                    $plan_validity = Carbon::parse($user_details->plan_validity);
                                    $plan_validity->addDays($term_days);
                                }
                                $message = trans('Plan renewed!');
                            } else {
                                if ($term_days == "9999") {
                                    $plan_validity = "2050-12-30 23:23:59";
                                } else {
                                    $plan_validity = Carbon::parse($user_details->plan_validity);
                                    $plan_validity->addDays($term_days);
                                }
                                $message = trans('Plan activated!');
                            }
                        } else {
                            // Add days
                            if ($term_days == "9999") {
                                $plan_validity = "2050-12-30 23:23:59";
                                $message       = trans("Plan activated!");
                            } else {
                                $plan_validity = Carbon::now();
                                $plan_validity->addDays($term_days);
                                $message = trans("Plan activated!");
                            }
                        }

                        $invoice_count  = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
                        $invoice_number = $invoice_count + 1;

                        Transaction::where('transaction_id', $transaction_id)->update([
                            'transaction_id' => $paddleTransactionId,
                            'invoice_prefix' => $config[15]->config_value,
                            'invoice_number' => $invoice_number,
                            'payment_status' => 'SUCCESS',
                            'updated_at'     => now(),
                        ]);

                        if ($user_details) {
                            $user_details->plan_id              = $transaction_details->plan_id;
                            $user_details->term                 = $term_days;
                            $user_details->plan_validity        = $plan_validity;
                            $user_details->plan_activation_date = now();
                            $user_details->plan_details         = $plan_data;
                            $user_details->save();
                        }

                        AppliedCoupon::where('transaction_id', $transaction_id)->update(['status' => 1]);
                    }

                    // Making all cards inactive, For Plan change
                    // Use DB::table with (int)$user_id to guarantee correct SQL quoting
                    BusinessCard::where('user_id', Auth::user()->user_id)->update([
                        'card_status' => 'inactive',
                    ]);

                    // Generate and send invoice details
                    $encode = json_decode($transaction_details->invoice_details, true);

                    $details = [
                        'from_billing_name'    => $encode['from_billing_name'],
                        'from_billing_email'   => $encode['from_billing_email'],
                        'from_billing_address' => $encode['from_billing_address'],
                        'from_billing_city'    => $encode['from_billing_city'],
                        'from_billing_state'   => $encode['from_billing_state'],
                        'from_billing_country' => $encode['from_billing_country'],
                        'from_billing_zipcode' => $encode['from_billing_zipcode'],
                        'gobiz_transaction_id' => $paddleTransactionId,
                        'to_billing_name'      => $encode['to_billing_name'],
                        'to_vat_number'        => $encode['to_vat_number'],
                        'invoice_currency'     => $transaction_details->transaction_currency,
                        'subtotal'             => $encode['subtotal'],
                        'tax_amount'           => (float) ($plan_data->plan_price) * (float) ($config[25]->config_value) / 100,
                        'applied_coupon'       => $encode['applied_coupon'],
                        'discounted_price'     => $encode['discounted_price'],
                        'invoice_amount'       => $encode['invoice_amount'],
                        'invoice_id'           => $config[15]->config_value . $invoice_number,
                        'invoice_date'         => $transaction_details->created_at,
                        'description'          => $transaction_details->desciption,
                        'email_heading'        => $config[27]->config_value,
                        'email_footer'         => $config[28]->config_value,
                    ];

                    try {
                        Mail::to($encode['to_billing_email'])->send(new \App\Mail\SendEmailInvoice($details));
                    } catch (\Exception $e) {
                        // Handle email sending failure if needed
                    }

                    return redirect()->route('user.plans')->with('success', $message);
                } else {
                    Transaction::where('transaction_id', $transaction_id)->update(['payment_status' => 'FAILED', 'updated_at' => now()]);
                    return redirect()->route('user.plans')->with('failed', trans('Payment failed!'));
                }
            } catch (\Exception $e) {
                Log::info('Paddle Billing: payment status error', ['exception' => $e->getMessage()]);
                return redirect()->route('user.plans')->with('failed', trans('Something went wrong!'));
            }
        }

        return redirect()->route('user.plans')->with('failed', trans('Payment failed!'));
    }

    // -------------------------------------------------------------------------
    // PADDLE BILLING WEBHOOK
    // Paddle Billing sends JSON with an "event_type" field.
    // Docs: https://developer.paddle.com/webhooks/overview
    // -------------------------------------------------------------------------

    public function paddlePaymentWebhook(Request $request)
    {
        // -----------------------------------------------------------------------
        // PADDLE BILLING – Verify webhook signature (recommended for production).
        // Paddle Billing signs every webhook with a secret key configured in your
        // Paddle dashboard (Notifications → Destinations → Secret key).
        // Store this in config[67] (or a dedicated env variable).
        //
        // Signature header: Paddle-Signature
        // Format:           ts=<timestamp>;h1=<hmac-sha256-hex>
        // -----------------------------------------------------------------------
        $config           = DB::table('config')->get();
        $webhookSecretKey = $config[67]->config_value ?? ''; // Paddle Billing webhook secret

        $rawBody         = $request->getContent();
        $signatureHeader = $request->header('Paddle-Signature', '');

        if (!empty($webhookSecretKey) && !empty($signatureHeader)) {
            // Parse ts and h1 from header
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
                Log::warning('Paddle Billing webhook signature mismatch');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        // Decode webhook payload
        $webhookData = json_decode($rawBody, true);

        // -----------------------------------------------------------------------
        // Paddle Billing event types for one-time transactions:
        //   transaction.completed      → payment succeeded (previously 'success')
        //   transaction.payment_failed → payment failed
        //   transaction.canceled       → transaction canceled
        //   transaction.updated        → general update (check status inside)
        // -----------------------------------------------------------------------
        $eventType = $webhookData['event_type'] ?? '';

        // The transaction object lives under webhookData['data']
        $transactionData = $webhookData['data'] ?? [];
        $paddleStatus    = $transactionData['status'] ?? '';

        // Retrieve our internal IDs from custom_data (set during transaction creation)
        $customData     = $transactionData['custom_data'] ?? [];
        $user_id        = isset($customData['user_id']) ? (int) $customData['user_id'] : null;
        $transaction_id = $customData['transaction_id'] ?? null;
        $paymentId      = $transactionData['id'] ?? null; // Paddle Billing transaction ID

        if (!$user_id || !$transaction_id) {
            Log::warning('Paddle Billing webhook missing custom_data', ['payload' => $webhookData]);
            return response()->json(['error' => 'Missing custom data'], 400);
        }

        // Handle completed / paid transactions
        if ($eventType === 'transaction.completed' || in_array($paddleStatus, ['billed', 'paid', 'completed'])) {

            // Get transaction details based on the transaction_id
            $transaction_details = Transaction::where('transaction_id', $transaction_id)->first();

            if (!$transaction_details) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Get user details
            $user_details = User::find($user_id);

            // Get plan data
            $plan_data = Plan::where('plan_id', $transaction_details->plan_id)->first();
            $term_days = (int) $plan_data->validity;

            if ($config[80]->config_value == '1') {
                $referralCalculation                   = [];
                $referralCalculation['referral_type']  = $config[81]->config_value;
                $referralCalculation['referral_value'] = $config[82]->config_value;

                if ($config[81]->config_value == '0') {
                    $base_amount                            = (float) $transaction_details->transaction_amount;
                    $referralCalculation['referral_amount'] = ($base_amount * $referralCalculation['referral_value']) / 100;
                } else {
                    $referralCalculation['referral_amount'] = $referralCalculation['referral_value'];
                }

                // Note: webhook runs outside a user session, so we look up by user_id directly
                if (Referral::where('user_id', $user_id)->where('is_subscribed', 0)->first()) {
                    Referral::where('user_id', $user_id)->update([
                        'is_subscribed'   => 1,
                        'referral_scheme' => json_encode($referralCalculation),
                        'updated_at'      => now(),
                    ]);
                }
            }

            if ($user_details->plan_validity == "") {
                if ($term_days == "9999") {
                    $plan_validity = "2050-12-30 23:23:59";
                } else {
                    $plan_validity = Carbon::now();
                    $plan_validity->addDays($term_days);
                }

                $invoice_count  = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
                $invoice_number = $invoice_count + 1;

                Transaction::where('transaction_id', $transaction_id)->update([
                    'transaction_id' => $paymentId,
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'SUCCESS',
                    'updated_at'     => now(),
                ]);

                if ($user_details) {
                    $user_details->plan_id              = $transaction_details->plan_id;
                    $user_details->term                 = $term_days;
                    $user_details->plan_validity        = $plan_validity;
                    $user_details->plan_activation_date = now();
                    $user_details->plan_details         = $plan_data;
                    $user_details->save();
                }

                AppliedCoupon::where('transaction_id', $transaction_id)->update(['status' => 1]);
            } else {
                $plan_validity  = Carbon::createFromFormat('Y-m-d H:i:s', $user_details->plan_validity);
                $current_date   = Carbon::now();
                $remaining_days = $current_date->diffInDays($plan_validity, false);

                if ($remaining_days > 0) {
                    if ($term_days == "9999") {
                        $plan_validity = "2050-12-30 23:23:59";
                    } else {
                        $plan_validity = Carbon::parse($user_details->plan_validity);
                        $plan_validity->addDays($term_days);
                    }
                } else {
                    if ($term_days == "9999") {
                        $plan_validity = "2050-12-30 23:23:59";
                    } else {
                        $plan_validity = Carbon::parse($user_details->plan_validity);
                        $plan_validity->addDays($term_days);
                    }
                }

                $invoice_count  = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
                $invoice_number = $invoice_count + 1;

                Transaction::where('transaction_id', $transaction_id)->update([
                    'transaction_id' => $paymentId,
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'SUCCESS',
                    'updated_at'     => now(),
                ]);

                if ($user_details) {
                    $user_details->plan_id              = $transaction_details->plan_id;
                    $user_details->term                 = $term_days;
                    $user_details->plan_validity        = $plan_validity;
                    $user_details->plan_activation_date = now();
                    $user_details->plan_details         = $plan_data;
                    $user_details->save();
                }

                AppliedCoupon::where('transaction_id', $transaction_id)->update(['status' => 1]);
            }

            // Making all cards inactive, For Plan change
            BusinessCard::where('user_id', Auth::user()->user_id)->update([
                'card_status' => 'inactive',
            ]);

            // Generate and send invoice details
            $encode = json_decode($transaction_details->invoice_details, true);

            $details = [
                'from_billing_name'    => $encode['from_billing_name'],
                'from_billing_email'   => $encode['from_billing_email'],
                'from_billing_address' => $encode['from_billing_address'],
                'from_billing_city'    => $encode['from_billing_city'],
                'from_billing_state'   => $encode['from_billing_state'],
                'from_billing_country' => $encode['from_billing_country'],
                'from_billing_zipcode' => $encode['from_billing_zipcode'],
                'gobiz_transaction_id' => $paymentId,
                'to_billing_name'      => $encode['to_billing_name'],
                'to_vat_number'        => $encode['to_vat_number'],
                'invoice_currency'     => $transaction_details->transaction_currency,
                'subtotal'             => $encode['subtotal'],
                'tax_amount'           => (float) ($plan_data->plan_price) * (float) ($config[25]->config_value) / 100,
                'applied_coupon'       => $encode['applied_coupon'],
                'discounted_price'     => $encode['discounted_price'],
                'invoice_amount'       => $encode['invoice_amount'],
                'invoice_id'           => $config[15]->config_value . $invoice_number,
                'invoice_date'         => $transaction_details->created_at,
                'description'          => $transaction_details->desciption,
                'email_heading'        => $config[27]->config_value,
                'email_footer'         => $config[28]->config_value,
            ];

            try {
                Mail::to($encode['to_billing_email'])->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
                // Handle email sending failure if needed
            }
        }

        // Handle payment failed
        if ($eventType === 'transaction.payment_failed') {
            Transaction::where('transaction_id', $transaction_id)->update(['payment_status' => 'FAILED', 'updated_at' => now()]);
        }

        // Handle canceled
        if ($eventType === 'transaction.canceled' || $paddleStatus === 'canceled') {
            Transaction::where('transaction_id', $transaction_id)->update(['payment_status' => 'CANCELED', 'updated_at' => now()]);
        }

        // Handle past_due / other pending states
        if ($paddleStatus === 'past_due') {
            Transaction::where('transaction_id', $transaction_id)->update(['payment_status' => 'PENDING', 'updated_at' => now()]);
        }

        // Paddle Billing expects a 200 OK response to acknowledge receipt
        return response()->json(['success' => true], 200);
    }
}
