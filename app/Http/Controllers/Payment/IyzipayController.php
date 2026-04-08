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
use App\Transaction;
use Iyzipay\Options;
use App\AppliedCoupon;
use Illuminate\Support\Str;
use App\Classes\UpgradePlan;
use Illuminate\Http\Request;
use App\Services\IyzicoService;
use Iyzipay\Model\CheckoutForm;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

class IyzipayController extends Controller
{
    protected $options;

    public function __construct()
    {
        // Queries
        $config = DB::table('config')->get();

        $this->options = new Options();
        $this->options->setApiKey($config[90]->config_value);
        $this->options->setSecretKey($config[91]->config_value);
        $this->options->setBaseUrl($config[89]->config_value == "sandbox" ? "https://sandbox-api.iyzipay.com" : "https://api.iyzipay.com");
    }

    public function generatePaymentLink($planId, $couponId)
    {
        if (Auth::user()) {
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();
            $plan_details = Plan::where('plan_id', $planId)->where('status', 1)->first();

            if (!$plan_details) {
                return view('errors.404');
            }

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
                    // Applied tax in total
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);

                    // Get discount in plan price
                    $discountPrice = $couponDetails->coupon_amount;

                    // Total
                    $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                    // Coupon is applied
                    $appliedCoupon = $couponDetails->coupon_code;
                } else {
                    // Applied tax in total
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);

                    // Get discount in plan price
                    $discountPrice = $plan_details->plan_price * $couponDetails->coupon_amount / 100;

                    // Total
                    $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                    // Coupon is applied
                    $appliedCoupon = $couponDetails->coupon_code;
                }
            } else {
                // Applied tax in total
                $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
            }

            $amountToBePaidPaise = $amountToBePaid;

            // Generate a unique transaction ID
            $transactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            // Callback URL
            $callbackUrl = route('iyzipay.payment.status');

            try {
                $request = new CreateCheckoutFormInitializeRequest();
                $request->setLocale('en');
                $conversationId = 'order_' . uniqid();
                $request->setConversationId($conversationId);
                $request->setPrice($amountToBePaidPaise);
                $request->setPaidPrice($amountToBePaidPaise);
                $request->setCurrency($config[1]->config_value);
                $request->setBasketId($transactionId);
                $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                $request->setCallbackUrl($callbackUrl . "?user_id=" . auth()->id());

                $buyer = new \Iyzipay\Model\Buyer();
                $buyer->setId(Auth::user()->id);
                $buyer->setName(Auth::user()->name);
                $buyer->setSurname(Auth::user()->name);
                $buyer->setGsmNumber(Auth::user()->vat_number);
                $buyer->setEmail(Auth::user()->email);
                $buyer->setIdentityNumber(Auth::user()->billing_phone);
                $buyer->setRegistrationAddress(Auth::user()->billing_address);
                $buyer->setIp(request()->ip());
                $buyer->setCity(Auth::user()->billing_city);
                $buyer->setCountry(Auth::user()->billing_country);
                $buyer->setZipCode(Auth::user()->billing_zipcode);
                $request->setBuyer($buyer);

                $address = new \Iyzipay\Model\Address();
                $address->setContactName(Auth::user()->name);
                $address->setCity(Auth::user()->billing_city);
                $address->setCountry(Auth::user()->billing_country);
                $address->setAddress(Auth::user()->billing_address);
                $address->setZipCode(Auth::user()->billing_zipcode);
                $request->setShippingAddress($address);
                $request->setBillingAddress($address);

                $basketItems = [];
                $item = new \Iyzipay\Model\BasketItem();
                $item->setId($plan_details->plan_id);
                $item->setName($plan_details->plan_name);
                $item->setCategory1("Plan");
                $item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $item->setPrice($amountToBePaidPaise);
                $basketItems[] = $item;

                $request->setBasketItems($basketItems);

                try {
                    $checkoutForm = \Iyzipay\Model\CheckoutFormInitialize::create($request, $this->options);
                    $rawResult = json_decode($checkoutForm->getRawResult(), true);

                    // Get JSON response
                    if (isset($rawResult['status']) && $rawResult['status'] === 'success' && isset($rawResult['paymentPageUrl'])) {
                        
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

                        // Save transactions
                        $transaction = new Transaction();
                        $transaction->gobiz_transaction_id = $transactionId;
                        $transaction->transaction_date = now();
                        $transaction->transaction_id = $rawResult['token'];
                        $transaction->user_id = Auth::user()->id;
                        $transaction->plan_id = $plan_details->plan_id;
                        $transaction->desciption = $plan_details->plan_name . " Plan";
                        $transaction->payment_gateway_name = "Iyzipay";
                        $transaction->transaction_amount = $amountToBePaid;
                        $transaction->transaction_currency = $config[1]->config_value;
                        $transaction->invoice_details = json_encode($invoice_details);
                        $transaction->payment_status = "PENDING";
                        $transaction->save();

                        // Coupon is not applied
                        if ($couponId != " ") {
                            // Save applied coupon
                            $appliedCoupon = new AppliedCoupon;
                            $appliedCoupon->applied_coupon_id = uniqid();
                            $appliedCoupon->transaction_id = $transactionId;
                            $appliedCoupon->user_id = Auth::user()->id;
                            $appliedCoupon->coupon_id = $couponId;
                            $appliedCoupon->status = 0;
                            $appliedCoupon->save();
                        }

                        return Redirect::away($rawResult['paymentPageUrl']);
                    } else {
                        return redirect()->route('user.plans')->with('failed', trans('Payment failed.'));
                    }
                } catch (\Throwable $e) {
                    return redirect()->route('user.plans')->with('failed', trans('Payment failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->route('user.plans')->with('failed', trans('Payment failed.'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function iyzipayPaymentStatus(Request $request)
    {
        // Iyzipay sends back the token
        $token = $request->get('token');

        // Retrieve user from session
        $userId = $request->query('user_id');

        if ($userId) {
            Auth::loginUsingId($userId); // Restore the user session if needed
        }

        // Get transaction details based on the transactionId
        $transaction_details = Transaction::where('transaction_id', $token)->first();

        if (!$transaction_details) {
            return redirect()->route('user.plans')->with('failed', trans('Transaction not found or already processed.'));
        }

        // Retrieve the checkout form details using the token
        $retrieveRequest = new RetrieveCheckoutFormRequest();
        $retrieveRequest->setLocale('en');
        $retrieveRequest->setToken($token);

        try {
            $checkoutForm = CheckoutForm::retrieve($retrieveRequest, $this->options);
            $rawResult = json_decode($checkoutForm->getRawResult(), true);

            if ($rawResult && isset($rawResult['paymentStatus'])) {
                // Plan upgrade
                $upgradePlan = new UpgradePlan;
                $upgradePlan->upgrade($transaction_details->transaction_id, "PAID");

                // Update transaction id
                Transaction::where('transaction_id', $transaction_details->transaction_id)->update(['transaction_id' => $rawResult['paymentId']]);

                return redirect()->route('user.plans')->with('success', trans('Your payment has been successful.'));
            } else {
                // Update payment status
                Transaction::where('transaction_id', $transaction_details->transaction_id)->update(['payment_status' => 'FAILED']);

                return redirect()->route('user.plans')->with('failed', trans('Your payment has failed.'));
            }
        } catch (\Throwable $e) {
            // Update payment status
            Transaction::where('transaction_id', $transaction_details->transaction_id)->update(['payment_status' => 'FAILED']);

            return redirect()->route('user.plans')->with('failed', trans('Transaction not found or already processed.'));
        }

        // Update payment status
        Transaction::where('transaction_id', $transaction_details->transaction_id)->update(['payment_status' => 'FAILED']);

        return redirect()->route('user.plans')->with('failed', trans('Payment failed.'));
    }
}
