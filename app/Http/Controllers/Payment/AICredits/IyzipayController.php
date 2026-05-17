<?php

namespace App\Http\Controllers\Payment\AICredits;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Coupon;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;

class IyzipayController extends Controller
{
    protected $options;

    public function __construct()
    {
        // Config
        $config = DB::table('config')->get();

        $this->options = new Options();

        $this->options->setApiKey(
            $config[90]->config_value
        );

        $this->options->setSecretKey(
            $config[91]->config_value
        );

        $this->options->setBaseUrl(
            $config[89]->config_value == "sandbox"
                ? "https://sandbox-api.iyzipay.com"
                : "https://api.iyzipay.com"
        );
    }

    /**
     * Generate Payment Link
     */
    public function generatePaymentLink($planId, $couponId)
    {
        // Login Check
        if (!Auth::user()) {

            return redirect()
                ->route('login');
        }

        // Settings
        $settings = Setting::where(
            'status',
            1
        )->first();

        // Config
        $config = DB::table('config')->get();

        // User
        $userData = User::where(
            'id',
            Auth::user()->id
        )->first();

        // Plan
        $plan_details = AiCreditsPlan::where(
            'ai_credits_plan_id',
            $planId
        )
            ->where(
                'status',
                'active'
            )
            ->first();

        // Validation
        if (!$plan_details) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Invalid plan!')
                );
        }

        // Coupon
        $couponDetails = Coupon::where(
            'used_for',
            'ai_credits'
        )
            ->where(
                'coupon_id',
                $couponId
            )
            ->first();

        // Tax
        $appliedTaxInTotal = 0;

        // Discount
        $discountPrice = 0;

        // Applied Coupon
        $appliedCoupon = null;

        // Coupon Logic
        if ($couponDetails != null) {

            if (
                $couponDetails->coupon_type ==
                'fixed'
            ) {

                // Tax
                $appliedTaxInTotal =
                    (
                        (float)
                        $plan_details->plan_price *
                        (float)
                        $config[25]->config_value
                    ) / 100;

                // Discount
                $discountPrice =
                    $couponDetails->coupon_amount;

                // Total
                $amountToBePaid =
                    (
                        $plan_details->plan_price +
                        $appliedTaxInTotal
                    ) -
                    $discountPrice;

                $amountToBePaid =
                    (float)
                    number_format(
                        $amountToBePaid,
                        2,
                        '.',
                        ''
                    );

                // Coupon
                $appliedCoupon =
                    $couponDetails->coupon_code;
            } else {

                // Tax
                $appliedTaxInTotal =
                    (
                        (float)
                        $plan_details->plan_price *
                        (float)
                        $config[25]->config_value
                    ) / 100;

                // Discount
                $discountPrice =
                    $plan_details->plan_price *
                    $couponDetails->coupon_amount /
                    100;

                // Total
                $amountToBePaid =
                    (
                        $plan_details->plan_price +
                        $appliedTaxInTotal
                    ) -
                    $discountPrice;

                $amountToBePaid =
                    (float)
                    number_format(
                        $amountToBePaid,
                        2,
                        '.',
                        ''
                    );

                // Coupon
                $appliedCoupon =
                    $couponDetails->coupon_code;
            }
        } else {

            // Tax
            $appliedTaxInTotal =
                (
                    (float)
                    $plan_details->plan_price *
                    (float)
                    $config[25]->config_value
                ) / 100;

            // Total
            $amountToBePaid =
                $plan_details->plan_price +
                $appliedTaxInTotal;
        }

        // Transaction ID
        $transactionId =
            "TX" .
            preg_replace(
                '/\D/',
                '',
                Str::uuid()
            );

        // Order ID
        $orderId =
            "OD" .
            preg_replace(
                '/\D/',
                '',
                Str::uuid()
            );

        // Callback URL
        $callbackUrl =
            route('ai.credits.iyzipay.payment.status');

        try {

            // Request
            $request =
                new CreateCheckoutFormInitializeRequest();

            $request->setLocale('en');

            $conversationId =
                'order_' . uniqid();

            $request->setConversationId(
                $conversationId
            );

            $request->setPrice(
                $amountToBePaid
            );

            $request->setPaidPrice(
                $amountToBePaid
            );

            $request->setCurrency(
                $config[1]->config_value
            );

            $request->setBasketId(
                $transactionId
            );

            $request->setPaymentGroup(
                PaymentGroup::PRODUCT
            );

            $request->setCallbackUrl(
                $callbackUrl .
                    "?user_id=" .
                    auth()->id()
            );

            // Buyer
            $buyer =
                new \Iyzipay\Model\Buyer();

            $buyer->setId(
                Auth::user()->id
            );

            $buyer->setName(
                Auth::user()->name
            );

            $buyer->setSurname(
                Auth::user()->name
            );

            $buyer->setGsmNumber(
                Auth::user()->billing_phone
            );

            $buyer->setEmail(
                Auth::user()->email
            );

            $buyer->setIdentityNumber(
                Auth::user()->billing_phone
            );

            $buyer->setRegistrationAddress(
                Auth::user()->billing_address
            );

            $buyer->setIp(
                request()->ip()
            );

            $buyer->setCity(
                Auth::user()->billing_city
            );

            $buyer->setCountry(
                Auth::user()->billing_country
            );

            $buyer->setZipCode(
                Auth::user()->billing_zipcode
            );

            $request->setBuyer($buyer);

            // Address
            $address =
                new \Iyzipay\Model\Address();

            $address->setContactName(
                Auth::user()->name
            );

            $address->setCity(
                Auth::user()->billing_city
            );

            $address->setCountry(
                Auth::user()->billing_country
            );

            $address->setAddress(
                Auth::user()->billing_address
            );

            $address->setZipCode(
                Auth::user()->billing_zipcode
            );

            $request->setShippingAddress(
                $address
            );

            $request->setBillingAddress(
                $address
            );

            // Basket Item
            $basketItems = [];

            $item =
                new \Iyzipay\Model\BasketItem();

            $item->setId(
                $plan_details->ai_credits_plan_id
            );

            $item->setName(
                $plan_details->plan_name
            );

            $item->setCategory1(
                "AI Credits"
            );

            $item->setItemType(
                BasketItemType::PHYSICAL
            );

            $item->setPrice(
                $amountToBePaid
            );

            $basketItems[] = $item;

            $request->setBasketItems(
                $basketItems
            );

            // Checkout Form
            $checkoutForm =
                CheckoutFormInitialize::create(
                    $request,
                    $this->options
                );

            $rawResult =
                json_decode(
                    $checkoutForm->getRawResult(),
                    true
                );

            // Success
            if (
                isset($rawResult['status']) &&
                $rawResult['status'] === 'success' &&
                isset($rawResult['paymentPageUrl'])
            ) {

                // Invoice Details
                $invoice_details = [
                    'from_billing_name' => $config[16]->config_value,
                    'from_billing_address' => $config[19]->config_value,
                    'from_billing_city' => $config[20]->config_value,
                    'from_billing_state' => $config[21]->config_value,
                    'from_billing_zipcode' => $config[22]->config_value,
                    'from_billing_country' => $config[23]->config_value,
                    'from_vat_number' => $config[26]->config_value,
                    'from_billing_phone' => $config[18]->config_value,
                    'from_billing_email' => $config[17]->config_value,
                    'to_billing_name' => $userData->billing_name,
                    'to_billing_address' => $userData->billing_address,
                    'to_billing_city' => $userData->billing_city,
                    'to_billing_state' => $userData->billing_state,
                    'to_billing_zipcode' => $userData->billing_zipcode,
                    'to_billing_country' => $userData->billing_country,
                    'to_billing_phone' => $userData->billing_phone,
                    'to_billing_email' => $userData->billing_email,
                    'to_vat_number' => $userData->vat_number,
                    'subtotal' => $plan_details->plan_price,
                    'tax_name' => $config[24]->config_value,
                    'tax_type' => $config[14]->config_value,
                    'tax_value' => $config[25]->config_value,
                    'tax_amount' => $appliedTaxInTotal,
                    'applied_coupon' => $appliedCoupon,
                    'discounted_price' => $discountPrice,
                    'invoice_amount' => $amountToBePaid,
                ];

                // Save Transaction
                $transaction =
                    new AiCreditsTransaction();

                $transaction->ai_credits_transaction_id =
                    $transactionId;

                $transaction->ai_credits_order_id =
                    $orderId;

                $transaction->payment_transaction_id =
                    $rawResult['token'];

                $transaction->user_id =
                    Auth::user()->id;

                $transaction->ai_credits_plan_id =
                    $plan_details->ai_credits_plan_id;

                $transaction->purchase_details =
                    $plan_details->plan_name .
                    " AI Credits Plan";

                $transaction->payment_method =
                    "Iyzipay";

                $transaction->currency =
                    $config[1]->config_value;

                $transaction->amount =
                    $amountToBePaid;

                $transaction->invoice_details =
                    json_encode(
                        $invoice_details
                    );

                $transaction->payment_status =
                    "pending";

                $transaction->save();

                // Save Coupon
                if ($couponId != " ") {

                    $appliedCouponRecord =
                        new AppliedCoupon();

                    $appliedCouponRecord->applied_coupon_id =
                        uniqid();

                    $appliedCouponRecord->transaction_id =
                        $transactionId;

                    $appliedCouponRecord->user_id =
                        Auth::user()->id;

                    $appliedCouponRecord->coupon_id =
                        $couponId;

                    $appliedCouponRecord->status =
                        0;

                    $appliedCouponRecord->save();
                }

                return Redirect::away(
                    $rawResult['paymentPageUrl']
                );
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Payment failed.')
                );
        } catch (\Throwable $e) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Payment failed.')
                );
        }
    }

    /**
     * Iyzipay Payment Status
     */
    public function iyzipayPaymentStatus(Request $request)
    {
        // Token
        $token =
            $request->get('token');

        // Restore Session
        $userId =
            $request->query('user_id');

        if ($userId) {
            Auth::loginUsingId($userId);
        }

        // Transaction
        $transaction_details =
            AiCreditsTransaction::where(
                'payment_transaction_id',
                $token
            )->first();

        // Validation
        if (!$transaction_details) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans(
                        'Transaction not found or already processed.'
                    )
                );
        }

        // Retrieve Checkout
        $retrieveRequest =
            new RetrieveCheckoutFormRequest();

        $retrieveRequest->setLocale('en');

        $retrieveRequest->setToken(
            $token
        );

        try {

            $checkoutForm =
                CheckoutForm::retrieve(
                    $retrieveRequest,
                    $this->options
                );

            $rawResult =
                json_decode(
                    $checkoutForm->getRawResult(),
                    true
                );

            // Success
            if (
                $rawResult &&
                isset(
                    $rawResult['paymentStatus']
                )
            ) {

                // Config
                $config =
                    DB::table('config')->get();

                // Plan
                $planDetails =
                    AiCreditsPlan::where(
                        'ai_credits_plan_id',
                        $transaction_details->ai_credits_plan_id
                    )->first();

                // Invoice Count
                $invoice_count =
                    AiCreditsTransaction::where(
                        'invoice_prefix',
                        $config[15]->config_value
                    )->count();

                $invoice_number =
                    $invoice_count + 1;

                // Update Transaction
                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $token
                )->update([
                    'payment_transaction_id' => $rawResult['paymentId'],
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'paid',
                ]);

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
                    $transaction_details->ai_credits_transaction_id
                )->update([
                    'status' => 1
                ]);

                // Send invoice email
                $encode = json_decode($transaction_details->invoice_details, true);

                $details = [
                    'from_billing_name' => $encode['from_billing_name'],
                    'from_billing_email' => $encode['from_billing_email'],
                    'from_billing_address' => $encode['from_billing_address'],
                    'from_billing_city' => $encode['from_billing_city'],
                    'from_billing_state' => $encode['from_billing_state'],
                    'from_billing_country' => $encode['from_billing_country'],
                    'from_billing_zipcode' => $encode['from_billing_zipcode'],
                    'gobiz_transaction_id' => $transaction_details->ai_credits_transaction_id,
                    'to_billing_name' => $encode['to_billing_name'],
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

                try {
                    Mail::to($encode['to_billing_email'])
                        ->send(new \App\Mail\SendEmailInvoice($details));
                } catch (\Exception $e) {
                }

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'success',
                        trans(
                            'Your payment has been successful.'
                        )
                    );
            } else {

                // Failed
                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $token
                )->update([
                    'payment_status' => 'failed'
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans(
                            'Your payment has failed.'
                        )
                    );
            }
        } catch (\Throwable $e) {

            // Failed
            AiCreditsTransaction::where(
                'payment_transaction_id',
                $token
            )->update([
                'payment_status' => 'failed'
            ]);

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans(
                        'Transaction not found or already processed.'
                    )
                );
        }
    }
}
