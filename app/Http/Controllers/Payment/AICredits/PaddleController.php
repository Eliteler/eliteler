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

namespace App\Http\Controllers\Payment\AICredits;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Coupon;
use App\Setting;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaddleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except([
            'paddlePaymentStatus',
            'paddlePaymentWebhook'
        ]);
    }

    /**
     * Paddle Base URL
     */
    private function getPaddleBillingBaseUrl($sandbox): string
    {
        return ($sandbox === 'true')
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    /**
     * Paddle Headers
     */
    private function getPaddleBillingHeaders($apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Generate Payment Link
     */
    public function generatePaymentLink(Request $request, $planId, $couponId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Config
        $config = DB::table('config')->get();

        // User
        $userData = User::where('id', Auth::user()->id)->first();

        // Settings
        $settings = Setting::where('status', 1)->first();

        // AI Credits Plan
        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        // Validation
        if (!$plan_details) {
            return view('errors.404');
        }

        // IDs
        $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());
        $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());

        // Coupon
        $couponDetails = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_id', $couponId)
            ->first();

        // Applied tax
        $appliedTaxInTotal = 0;

        // Discount
        $discountPrice = 0;

        // Applied coupon
        $appliedCoupon = null;

        // Coupon Logic
        if ($couponDetails != null) {
            if ($couponDetails->coupon_type == 'fixed') {
                // Tax
                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice = $couponDetails->coupon_amount;

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                // Coupon
                $appliedCoupon = $couponDetails->coupon_code;
            } else {
                // Tax
                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice = $plan_details->plan_price * $couponDetails->coupon_amount / 100;

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                // Coupon
                $appliedCoupon = $couponDetails->coupon_code;
            }
        } else {
            // Tax
            $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

            // Total
            $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
        }

        // Invoice Details
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
        $invoice_details['to_billing_phone'] = $userData->billing_phone;
        $invoice_details['to_billing_email'] = $userData->billing_email;
        $invoice_details['to_vat_number'] = $userData->vat_number;
        $invoice_details['subtotal'] = $plan_details->plan_price;
        $invoice_details['tax_name'] = $config[24]->config_value;
        $invoice_details['tax_type'] = $config[14]->config_value;
        $invoice_details['tax_value'] = $config[25]->config_value;
        $invoice_details['tax_amount'] = $appliedTaxInTotal;
        $invoice_details['applied_coupon'] = $appliedCoupon;
        $invoice_details['discounted_price'] = $discountPrice;
        $invoice_details['invoice_amount'] = $amountToBePaid;

        // Save Transaction
        $transaction = new AiCreditsTransaction();
        $transaction->ai_credits_transaction_id = $aiCreditsTransactionId;
        $transaction->ai_credits_order_id = $aiCreditsOrderId;
        $transaction->payment_transaction_id = $aiCreditsTransactionId;
        $transaction->user_id = Auth::user()->id;
        $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
        $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
        $transaction->payment_method = "Paddle";
        $transaction->currency = $config[1]->config_value;
        $transaction->amount = $amountToBePaid;
        $transaction->invoice_details = json_encode($invoice_details);
        $transaction->payment_status = "pending";
        $transaction->save();

        // Save Coupon
        if ($couponId != " ") {
            $appliedCouponRecord = new AppliedCoupon();
            $appliedCouponRecord->applied_coupon_id = uniqid();
            $appliedCouponRecord->transaction_id = $aiCreditsTransactionId;
            $appliedCouponRecord->user_id = Auth::user()->id;
            $appliedCouponRecord->coupon_id = $couponId;
            $appliedCouponRecord->status = 0;
            $appliedCouponRecord->save();
        }

        // Paddle
        $client = new \GuzzleHttp\Client();
        $sandbox = $config[64]->config_value;
        $baseUrl = $this->getPaddleBillingBaseUrl($sandbox);
        $apiKey = $config[66]->config_value;
        $amountInLowestUnit = (string) (int) round($amountToBePaid * 100);
        $customData = [
            'user_id' => Auth::user()->id,
            'transaction_id' => $aiCreditsTransactionId,
        ];
        $returnUrl = route('ai.credits.paddle.payment.status');

        try {
            // Create Product
            $productResponse = $client->request(
                'POST',
                $baseUrl . '/products',
                [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json' => [
                        'name' => $plan_details->plan_name . ' AI Credits Plan',
                        'description' => 'AI Credits Purchase',
                        'tax_category' => 'saas',
                    ],
                ]
            );

            $productData = json_decode($productResponse->getBody(), true);

            $paddleProductId = $productData['data']['id'];

            // Create Price
            $currency = strtoupper($config[1]->config_value);

            $priceResponse = $client->request(
                'POST',
                $baseUrl . '/prices',
                [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),

                    'json' => [
                        'product_id' => $paddleProductId,
                        'description' => $plan_details->plan_name . ' AI Credits',
                        'name' => $plan_details->plan_name . ' AI Credits',

                        'unit_price' => [
                            'amount' => $amountInLowestUnit,
                            'currency_code' => $currency,
                        ],

                        'tax_mode' => 'external',

                        'quantity' => [
                            'minimum' => 1,
                            'maximum' => 1,
                        ],
                    ],
                ]
            );
            $priceData = json_decode($priceResponse->getBody(), true);
            $paddlePriceId = $priceData['data']['id'];

            // Create Transaction
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
                'customer' => [
                    'email' => Auth::user()->email,
                ],
            ];

            $response = $client->request(
                'POST',
                $baseUrl . '/transactions',
                [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                    'json' => $transactionPayload,
                ]
            );

            $data = json_decode($response->getBody(), true);

            if (isset($data['data']['id'])) {

                $paddleTransactionIdForCheckout =
                    $data['data']['id'];

                return view(
                    'user.pages.ai-credits.pay-with-paddle',
                    compact(
                        'settings',
                        'plan_details',
                        'data',
                        'config',
                        'paddleTransactionIdForCheckout'
                    )
                );
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        } catch (\Exception $e) {
            Log::info(
                'Paddle Billing Payment Error',
                [
                    'exception' => $e->getMessage()
                ]
            );

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        }
    }

    /**
     * Paddle Payment Status
     */
    public function paddlePaymentStatus(Request $request)
    {
        // Config
        $config = DB::table('config')->get();

        // Validation
        if (!$request->has('_ptxn')) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed!'));
        }

        $paddleTransactionId = $request->get('_ptxn');

        $client = new \GuzzleHttp\Client();
        $sandbox = $config[64]->config_value;
        $baseUrl = $this->getPaddleBillingBaseUrl($sandbox);
        $apiKey = $config[66]->config_value;

        try {
            // Transaction Details
            $response = $client->request(
                'GET',
                $baseUrl . '/transactions/' . $paddleTransactionId,
                [
                    'headers' => $this->getPaddleBillingHeaders($apiKey),
                ]
            );

            $data = json_decode($response->getBody(), true);
            $customData = $data['data']['custom_data'] ?? [];
            $transactionId = $customData['transaction_id'] ?? null;

            if (!$transactionId) {
                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans('Payment verification failed.'));
            }

            // Transaction
            $transaction_details = AiCreditsTransaction::where(
                'ai_credits_transaction_id',
                $transactionId
            )->first();

            if (!$transaction_details) {
                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans('Transaction not found.'));
            }

            // Already Paid
            if ($transaction_details->payment_status == 'paid') {
                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('success', trans('Payment already completed.'));
            }

            // Status
            $paddleStatus = $data['data']['status'] ?? '';

            if (!in_array($paddleStatus, ['billed', 'paid', 'completed'])) {
                $transaction_details->payment_status = 'failed';
                $transaction_details->save();

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans('Payment failed!'));
            }

            // Plan Details
            $planDetails = AiCreditsPlan::where(
                'ai_credits_plan_id',
                $transaction_details->ai_credits_plan_id
            )->first();

            // Invoice
            $invoice_count = AiCreditsTransaction::where(
                'invoice_prefix',
                $config[15]->config_value
            )->count();

            $invoice_number = $invoice_count + 1;

            // Update Transaction
            $transaction_details->payment_transaction_id = $paddleTransactionId;
            $transaction_details->invoice_prefix = $config[15]->config_value;
            $transaction_details->invoice_number = $invoice_number;
            $transaction_details->payment_status = 'paid';
            $transaction_details->save();

            // Update AI credits
            $aiCredit = AiCredit::where('user_id', Auth::user()->user_id)->first();

            if ($aiCredit) {
                $aiCredit->credits += $planDetails->no_of_ai_credits;
                $aiCredit->save();
            } else {
                $aiCredit = new AiCredit();
                $aiCredit->user_id = $transaction_details->user_id;
                $aiCredit->credits = $planDetails->no_of_ai_credits;
                $aiCredit->save();
            }

            // Update Coupon
            AppliedCoupon::where(
                'transaction_id',
                $transactionId
            )->update([
                'status' => 1
            ]);

            // Invoice Details
            $encode = json_decode(
                $transaction_details->invoice_details,
                true
            );

            $details = [
                'from_billing_name' => $encode['from_billing_name'],
                'from_billing_email' => $encode['from_billing_email'],
                'from_billing_address' => $encode['from_billing_address'],
                'from_billing_city' => $encode['from_billing_city'],
                'from_billing_state' => $encode['from_billing_state'],
                'from_billing_country' => $encode['from_billing_country'],
                'from_billing_zipcode' => $encode['from_billing_zipcode'],
                'gobiz_transaction_id' => $paddleTransactionId,
                'to_billing_name' => $encode['to_billing_name'],
                'to_vat_number' => $encode['to_vat_number'],
                'invoice_currency' => $transaction_details->currency,
                'subtotal' => $encode['subtotal'],
                'tax_amount' => $encode['tax_amount'],
                'applied_coupon' => $encode['applied_coupon'],
                'discounted_price' => $encode['discounted_price'],
                'invoice_amount' => $encode['invoice_amount'],
                'invoice_id' => $config[15]->config_value . $invoice_number,
                'invoice_date' => $transaction_details->created_at,
                'description' => $transaction_details->purchase_details,
                'email_heading' => $config[27]->config_value,
                'email_footer' => $config[28]->config_value,
            ];

            // Send Invoice
            try {
                Mail::to($encode['to_billing_email'])
                    ->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        } catch (\Exception $e) {
            Log::info(
                'Paddle Billing Status Error',
                [
                    'exception' => $e->getMessage()
                ]
            );

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        }
    }

    /**
     * Paddle Payment Webhook
     */
    public function paddlePaymentWebhook(Request $request)
    {
        // Config
        $config = DB::table('config')->get();

        // Paddle Event
        $eventType = $request->input('event_type');

        // Event Data
        $eventData = $request->input('data');

        // Validation
        if (!$eventData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid webhook payload'
            ], 400);
        }

        try {
            // Payment Completed
            if (
                $eventType == 'transaction.completed' ||
                $eventType == 'transaction.paid'
            ) {
                // Custom Data
                $customData =
                    $eventData['custom_data'] ?? [];

                // Transaction ID
                $transactionId =
                    $customData['transaction_id'] ?? null;

                // Paddle Transaction ID
                $paddleTransactionId =
                    $eventData['id'] ?? null;

                // Validation
                if (!$transactionId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Transaction ID missing'
                    ], 400);
                }

                // Transaction
                $transaction_details =
                    AiCreditsTransaction::where(
                        'ai_credits_transaction_id',
                        $transactionId
                    )->first();

                // Validation
                if (!$transaction_details) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Transaction not found'
                    ], 404);
                }

                // Already Paid
                if (
                    $transaction_details->payment_status ==
                    'paid'
                ) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Already processed'
                    ]);
                }

                // Plan Details
                $planDetails =
                    AiCreditsPlan::where(
                        'ai_credits_plan_id',
                        $transaction_details->ai_credits_plan_id
                    )->first();

                // Invoice
                $invoice_count =
                    AiCreditsTransaction::where(
                        'invoice_prefix',
                        $config[15]->config_value
                    )->count();

                $invoice_number =
                    $invoice_count + 1;

                // Update Transaction
                $transaction_details->payment_transaction_id = $paddleTransactionId;
                $transaction_details->invoice_prefix = $config[15]->config_value;
                $transaction_details->invoice_number = $invoice_number;
                $transaction_details->payment_status = 'paid';
                $transaction_details->save();

                // Update AI Credits
                $aiCredit = AiCredit::where('user_id', Auth::user()->user_id)->first();

                if ($aiCredit) {
                    $aiCredit->credits += $planDetails->no_of_ai_credits;
                    $aiCredit->save();
                } else {
                    $aiCredit = new AiCredit();
                    $aiCredit->user_id = $transaction_details->user_id;
                    $aiCredit->credits = $planDetails->no_of_ai_credits;
                    $aiCredit->save();
                }

                // Update Coupon
                AppliedCoupon::where(
                    'transaction_id',
                    $transactionId
                )->update([
                    'status' => 1
                ]);

                // Invoice Details
                $encode = json_decode(
                    $transaction_details->invoice_details,
                    true
                );

                $details = [
                    'from_billing_name' => $encode['from_billing_name'],
                    'from_billing_email' => $encode['from_billing_email'],
                    'from_billing_address' => $encode['from_billing_address'],
                    'from_billing_city' => $encode['from_billing_city'],
                    'from_billing_state' => $encode['from_billing_state'],
                    'from_billing_country' => $encode['from_billing_country'],
                    'from_billing_zipcode' => $encode['from_billing_zipcode'],
                    'gobiz_transaction_id' => $paddleTransactionId,
                    'to_billing_name' => $encode['to_billing_name'],
                    'to_vat_number' => $encode['to_vat_number'],
                    'invoice_currency' => $transaction_details->currency,
                    'subtotal' => $encode['subtotal'],
                    'tax_amount' => $encode['tax_amount'],
                    'applied_coupon' => $encode['applied_coupon'],
                    'discounted_price' => $encode['discounted_price'],
                    'invoice_amount' => $encode['invoice_amount'],
                    'invoice_id' => $config[15]->config_value . $invoice_number,
                    'invoice_date' => $transaction_details->created_at,
                    'description' => $transaction_details->purchase_details,
                    'email_heading' => $config[27]->config_value,
                    'email_footer' => $config[28]->config_value,
                ];

                // Send Invoice
                try {
                    Mail::to($encode['to_billing_email'])
                        ->send(new \App\Mail\SendEmailInvoice($details));
                } catch (\Exception $e) {
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment processed'
                ]);
            }

            // Payment Failed
            if (
                $eventType == 'transaction.failed' ||
                $eventType == 'transaction.canceled'
            ) {
                // Custom Data
                $customData =
                    $eventData['custom_data'] ?? [];

                // Transaction ID
                $transactionId =
                    $customData['transaction_id'] ?? null;

                if ($transactionId) {
                    AiCreditsTransaction::where(
                        'ai_credits_transaction_id',
                        $transactionId
                    )->update([
                        'payment_status' => 'failed'
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment failed updated'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook ignored'
            ]);
        } catch (\Exception $e) {
            Log::info(
                'Paddle Webhook Error',
                [
                    'exception' => $e->getMessage()
                ]
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook failed'
            ], 500);
        }
    }
}
