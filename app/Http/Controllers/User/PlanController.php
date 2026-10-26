<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\User;

use App\Currency;
use App\EmailTemplate;
use App\Http\Controllers\Controller;
use App\NfcCardDesign;
use App\NfcCardKey;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Plan;
use App\Setting;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Email template IDs used when a plan is activated via an NFC card key.
     */
    private const EMAIL_TEMPLATE_CUSTOMER_ACTIVATION = '584922675209';
    private const EMAIL_TEMPLATE_OWNER_ACTIVATION    = '584922675211';

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Plans
    public function index()
    {
        // check modern_dashboard_enabled
        if (Schema::hasTable('modern_dashboard_settings')) {
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
            if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
                if (session()->has('success') || session()->has('failed')) {
                    session()->reflash();
                }
                return redirect()->route('user.dashboard.subscriptions');
            }
        }

        // Queries
        $plans = DB::table('plans')->where('is_private', 0)->where('status', 1)->get();
        $config = DB::table('config')->get();
        $free_plan = Transaction::where('user_id', Auth::user()->id)->where('transaction_amount', '0.00')->orWhere('transaction_amount', '0')->count();
        $userRecord = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($userRecord->plan_details);
        $settings = Setting::where('status', 1)->first();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
        $remaining_days = 0;

        $cancelSubscription = 0;
        $subscriptionId = null;

        // Check active plan in user
        if (isset($active_plan)) {
            // Customer plan validity
            $plan_validity = Carbon::createFromFormat('Y-m-d H:i:s', Auth::user()->plan_validity);

            $current_date = Carbon::now();

            // Calculate remaining days including time
            $remaining_days = $current_date->diffInDays($plan_validity, false);

            // Always round up for future dates
            $remaining_days = ceil($remaining_days);

            // Convert to integer
            $remaining_days = (int) $remaining_days;

            // check subscription is active
            $subscription = Transaction::where('user_id', Auth::user()->id)->where('payment_status', 'SUCCESS')->latest()->first();
            if ($subscription) {
                $subscriptionId = $subscription->transaction_id;
            }

            // check subscription is available
            if (Schema::hasTable('recurring_payment_gateways') && $subscription) {
                // recurring payment gateways
                $payment_gateway = DB::table('recurring_payment_gateways')->where('payment_gateway_name', $subscription->payment_gateway_name)->first();

                // chack payment gateway is recurring
                if (isset($payment_gateway) && Str::startsWith($payment_gateway->payment_gateway_id, 'rec')) {

                    // retrieve controller namespace
                    $controllerNamespace = "Plugins\\" . $payment_gateway->plugin_id . "\\Controllers\\" . $payment_gateway->plugin_id . "UserController";

                    // check if controller exists
                    if (class_exists($controllerNamespace)) {
                        $controller = app($controllerNamespace);

                        if (method_exists($controller, 'checkPaymentStatus')) {

                            // call checkPaymentStatus method
                            $status = $controller->checkPaymentStatus($subscription->transaction_id);

                            // check if status is not null and authorized
                            if ($status !== null && $status === '1') {
                                $cancelSubscription = 1;
                            }
                        }
                    } else {
                        $cancelSubscription = 0;
                    }
                }
            }
        }

        // Fetch the active plan record from plans table for the modal
        $user = Auth::user();
        $plan = $user->plan_id ? DB::table('plans')->where('plan_id', $user->plan_id)->first() : null;
        return view('user.pages.plans.plans', compact('plans', 'settings', 'currency', 'active_plan', 'remaining_days', 'config', 'free_plan', 'cancelSubscription', 'subscriptionId', 'user', 'plan'));
    }

    // Cancel subscription
    public function subscriptionCancel($subscriptionId)
    {
        // check subscription id
        if (empty($subscriptionId)) {
            return back()->with('failed', 'Invalid subscription id.');
        }

        // cancel subscription
        $subscription = Transaction::where('transaction_id', $subscriptionId)->first();

        if ($subscription) {
            // recurring payment gateways
            $payment_gateway = DB::table('recurring_payment_gateways')->where('payment_gateway_name', $subscription->payment_gateway_name)->first();

            // chack payment gateway is recurring
            if ($payment_gateway) {
                // retrieve controller namespace
                $controllerNamespace = "Plugins\\" . $payment_gateway->plugin_id . "\\Controllers\\" . $payment_gateway->plugin_id . "UserController";

                // check if controller exists
                if (class_exists($controllerNamespace)) {
                    $controller = app($controllerNamespace);

                    if (method_exists($controller, 'cancelSubscription')) {
                        // call cancelSubscription method
                        $status = $controller->cancelSubscription($subscriptionId);

                        if ($status) {
                            return back()->with('success', 'Subscription cancelled successfully.');
                        } else {
                            return back()->with('failed', 'Something went wrong while cancelling.');
                        }
                    }
                } else {
                    return back()->with('failed', 'Something went wrong.');
                }
            }
        }

        // return error
        return back()->with('failed', 'Invalid subscription id.');
    }

    // Activate NFC plan
    public function activateNfcPlan($nfc_ref)
    {
        // user
        $user = Auth::user();

        // query
        $queryParams = request()->query('status');

        // cancel
        if ($queryParams == 'cancel') {
            // remove session
            session()->forget('nfc_ref');

            // return back with error
            return back()->with('failed', 'Plan overwrite cancelled.');
        }

        // nfc key details
        $nfcKeyDetails = NfcCardKey::where('unqiue_key', $nfc_ref)->first();

        // prevent duplicate activation
        if (empty($nfcKeyDetails)) {
            return back()->with('failed', 'Invalid NFC Card Key Reference.');
        }

        // associated plan
        $associatedPlan = Plan::where('plan_id', $nfcKeyDetails->associated_plan_id)->first();

        // associated nfc card
        $associatedNfcCard = NfcCardDesign::where('nfc_card_id', $nfcKeyDetails->associated_nfc_card_id)->first();

        // if plan or nfc card is not found return error
        if (empty($associatedPlan) || empty($associatedNfcCard)) {
            return back()->with('failed', 'Invalid NFC Card Key Reference.');
        }

        // config
        $config = DB::table('config')->get();

        // Activate the plan for the customer
        $this->createNfcPlanTransaction($user, $associatedPlan, $config);
        $user = $this->assignPlanToUser($user, $associatedPlan);

        // Generate order / transaction reference ids
        $nfcCardOrderId   = $this->generateReferenceId('OD');
        $nfcTransactionId = $this->generateReferenceId('TX');

        // Pricing
        $subtotal       = $associatedNfcCard->nfc_card_price;
        $taxPercent     = (float) $config[25]->config_value;
        $amountToBePaid = $this->calculateAmountWithTax($subtotal, $taxPercent);

        // order details
        $orderDetails = $this->prepareOrderDetails($config, $amountToBePaid, $associatedNfcCard, 0, 1, $nfc_ref);

        // delivery details
        $deliveryDetails = $this->prepareDeliveryDetails($user);

        // invoice details
        $invoiceDetails = $this->prepareInvoiceDetails($config, $user, $amountToBePaid, $associatedNfcCard, 0, 1);

        // create order record
        $this->createNfcOrderRecord($nfcCardOrderId, $nfcTransactionId, $user, $associatedNfcCard, $orderDetails, $deliveryDetails);

        // create order transaction record
        $this->createNfcOrderTransactionRecord($nfcTransactionId, $nfcCardOrderId, $config, $amountToBePaid, $invoiceDetails);

        // decrement NFC stock
        $this->decrementNfcStock($associatedNfcCard->nfc_card_id);

        // expire NFC key
        $this->expireNfcKey($nfcKeyDetails);

        // send activation emails
        $this->sendActivationEmails($user, $nfcCardOrderId, $orderDetails, $invoiceDetails);

        // remove session
        session()->forget('nfc_ref');

        // return success
        return back()->with('success', trans('Your plan has been successfully activated/renewed.'));
    }

    /*
    |--------------------------------------------------------------------------
    | NFC plan activation helpers
    |--------------------------------------------------------------------------
    */

    // Create NFC plan transaction
    private function createNfcPlanTransaction(User $user, $plan, $config): Transaction
    {
        // create transaction
        $transaction                       = new Transaction();
        $transaction->gobiz_transaction_id = uniqid();
        $transaction->transaction_date     = now();
        $transaction->transaction_id       = uniqid();
        $transaction->user_id              = $user->id;
        $transaction->plan_id              = $plan->plan_id;
        $transaction->desciption           = $plan->plan_name . ' Plan';
        $transaction->payment_gateway_name = 'Bank Transfer';
        $transaction->transaction_amount   = 0;
        $transaction->transaction_currency = $config[1]->config_value;
        $transaction->invoice_details      = json_encode([]);
        $transaction->payment_status       = 'SUCCESS';
        $transaction->save();

        // return transaction
        return $transaction;
    }

    // Assign plan to user
    private function assignPlanToUser(User $user, $plan): User
    {
        // Term days
        $termDays = (int) $plan->validity;

        // Lifetime plan
        if ($plan->validity === "9999") {
            $planValidity = "2050-12-30 23:23:59";
        } else {
            $baseDate = Carbon::now();

            if (
                !empty($user->plan_id) && $user->plan_id == $plan->plan_id && Carbon::parse($user->plan_validity)->isFuture()
            ) {
                $baseDate = Carbon::parse($user->plan_validity);
            }

            $planValidity = $baseDate->addDays($termDays);
        }

        // assign plan to user
        $user                       = User::where('user_id', $user->user_id)->first();
        $user->plan_id              = $plan->plan_id;
        $user->term                 = $plan->validity;
        $user->plan_validity        = $planValidity;
        $user->plan_activation_date = now();
        $user->plan_details         = $plan;
        $user->save();

        // return user
        return $user;
    }

    // Generate reference ID
    private function generateReferenceId(string $prefix): string
    {
        // return reference id
        return $prefix . preg_replace('/\D/', '', Str::uuid());
    }

    // Calculate amount with tax
    private function calculateAmountWithTax(float $subtotal, float $taxPercent): float
    {
        // tax amount
        $taxAmount = ($subtotal * $taxPercent) / 100;

        // return amount with tax
        return (float) number_format($subtotal + $taxAmount, 2, '.', '');
    }

    // Create NFC order record
    private function createNfcOrderRecord(
        string $orderId,
        string $transactionId,
        User $user,
        $nfcCard,
        array $orderDetails,
        array $deliveryDetails
    ): NfcCardOrder {
        // create order
        $order                                = new NfcCardOrder();
        $order->nfc_card_order_id             = $orderId;
        $order->user_id                       = $user->id;
        $order->nfc_card_id                   = $nfcCard->nfc_card_id;
        $order->nfc_card_order_transaction_id = $transactionId;
        $order->order_details                 = json_encode($orderDetails);
        $order->delivery_address              = json_encode($deliveryDetails);
        $order->delivery_note                 = '-';
        $order->order_status                  = 'delivered';
        $order->status                        = 1;
        $order->save();

        // return order
        return $order;
    }

    // Create NFC order transaction record
    private function createNfcOrderTransactionRecord(
        string $transactionId,
        string $orderId,
        $config,
        float $amount,
        array $invoiceDetails
    ): NfcCardOrderTransaction {
        // invoice prefix
        $invoicePrefix = $config[15]->config_value;

        // invoice number
        $invoiceNumber = NfcCardOrderTransaction::where('invoice_prefix', $invoicePrefix)->count() + 1;

        // create transaction
        $transaction                                = new NfcCardOrderTransaction();
        $transaction->nfc_card_order_transaction_id = $transactionId;
        $transaction->nfc_card_order_id             = $orderId;
        $transaction->payment_transaction_id        = $transactionId;
        $transaction->payment_method                = 'Bank Transfer';
        $transaction->currency                      = $config[1]->config_value;
        $transaction->amount                        = $amount;
        $transaction->invoice_details               = json_encode($invoiceDetails);
        $transaction->invoice_prefix                = $invoicePrefix;
        $transaction->invoice_number                = $invoiceNumber;
        $transaction->payment_status                = 'success';
        $transaction->save();

        // return transaction
        return $transaction;
    }

    // Decrement NFC stock
    private function decrementNfcStock(string $nfcCardId): void
    {
        $nfcCardDesign                   = NfcCardDesign::where('nfc_card_id', $nfcCardId)->first();
        $nfcCardDesign->available_stocks = (int) $nfcCardDesign->available_stocks - 1;
        $nfcCardDesign->save();
    }

    // Expire NFC key
    private function expireNfcKey(NfcCardKey $nfcKey): void
    {
        $nfcKey->key_status = 'expired';
        $nfcKey->save();
    }

    // Get billing phone
    private function getBillingPhone(User $user)
    {

        if (! empty($user->billing_country_code) && ! empty($user->billing_phone)) {
            return $user->billing_country_code . $user->billing_phone;
        } elseif (! empty($user->country_code) && ! empty($user->mobile_number)) {
            return $user->country_code . $user->mobile_number;
        }

        // return null if no phone number is found
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Activation emails
    |--------------------------------------------------------------------------
    */

    private function buildActivationEmailPayload(
        EmailTemplate $template,
        string $orderId,
        string $orderPageUrl,
        array $orderDetails,
        array $invoiceDetails
    ): array {
        // return payload
        return [
            'emailSubject'   => $template->email_template_subject,
            'emailContent'   => $template->email_template_content,
            'orderid'        => $orderId,
            'cardname'       => $orderDetails['order_item'],
            'cardprice'      => $orderDetails['price'],
            'paymentstatus'  => 'success',
            'deliverystatus' => 'delivered',
            'quantity'       => 1,
            'trackingnumber' => $orderDetails['tracking_number'] ?? '-',
            'courierpartner' => $orderDetails['courier_partner'] ?? '-',
            'orderpageurl'   => $orderPageUrl,
            'totalprice'     => formatCurrency($invoiceDetails['invoice_amount']),
            'supportemail'   => $invoiceDetails['from_billing_email'],
            'supportphone'   => $invoiceDetails['from_billing_phone'],
        ];
    }

    private function sendActivationEmails(User $user, string $orderId, array $orderDetails, array $invoiceDetails): void
    {
        // customer template
        $customerTemplate = EmailTemplate::where('email_template_id', self::EMAIL_TEMPLATE_CUSTOMER_ACTIVATION)->first();

        // owner template
        $ownerTemplate = EmailTemplate::where('email_template_id', self::EMAIL_TEMPLATE_OWNER_ACTIVATION)->first();

        // customer payload
        $customerPayload = $this->buildActivationEmailPayload(
            $customerTemplate,
            $orderId,
            route('user.order.nfc.card.view', $orderId),
            $orderDetails,
            $invoiceDetails
        );

        // owner payload
        $ownerPayload = $this->buildActivationEmailPayload(
            $ownerTemplate,
            $orderId,
            route('admin.order.show', $orderId),
            $orderDetails,
            $invoiceDetails
        ) + [
            'customerName'  => $user->name,
            'customeremail' => $user->email,
        ];

        try {
            Mail::to($invoiceDetails['to_billing_email'])->send(new \App\Mail\AppointmentMail($customerPayload));
            Mail::to(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\AppointmentMail($ownerPayload));
        } catch (\Exception $e) {
            // Silently ignore mail failures, same as original behaviour
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Order Details
    |--------------------------------------------------------------------------
    */

    private function prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $discountPrice, $quantity, $nfc_ref)
    {
        // subtotal
        $subtotal = $nfcDetails->nfc_card_price * $quantity;

        // return order details
        return [
            'nfc_card_id'       => $nfcDetails->nfc_card_id,
            'order_item'        => $nfcDetails->nfc_card_name,
            'order_description' => $nfcDetails->nfc_card_description,
            'order_quantity'    => $quantity,
            'price'             => $nfcDetails->nfc_card_price,
            'invoice_amount'    => $amountToBePaid,
            'tax_name'          => $config[24]->config_value,
            'tax_type'          => $config[14]->config_value,
            'tax_value'         => $config[25]->config_value,
            'tax_amount'        => ($subtotal * (float) $config[25]->config_value) / 100,
            'applied_coupon'    => null,
            'discounted_price'  => $discountPrice,
            'subtotal'          => $subtotal,
            'unique_key'        => [$nfc_ref],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Address
    |--------------------------------------------------------------------------
    */

    private function prepareDeliveryDetails($userData)
    {
        // return delivery address
        return [
            'billing_name'     => $userData->billing_name ?? $userData->name,
            'billing_address'  => $userData->billing_address,
            'billing_city'     => $userData->billing_city,
            'billing_state'    => $userData->billing_state,
            'billing_zipcode'  => $userData->billing_zipcode,
            'billing_country'  => $userData->billing_country,
            'billing_phone'    => $this->getBillingPhone($userData),
            'billing_email'    => $userData->billing_email ?? $userData->email,
            'shipping_name'    => $userData->billing_name ?? $userData->name,
            'shipping_address' => $userData->billing_address,
            'shipping_city'    => $userData->billing_city,
            'shipping_state'   => $userData->billing_state,
            'shipping_zipcode' => $userData->billing_zipcode,
            'shipping_country' => $userData->billing_country,
            'shipping_phone'   => $this->getBillingPhone($userData),
            'shipping_email'   => $userData->billing_email ?? $userData->email,
            'type'             => $userData->type,
            'vat_number'       => $userData->vat_number,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Details
    |--------------------------------------------------------------------------
    */

    private function prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $discountPrice, $quantity)
    {
        // subtotal
        $subtotal = $nfcDetails->nfc_card_price * $quantity;

        // return invoice details
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
            'to_billing_name'      => $userData->billing_name ?? $userData->name,
            'to_billing_address'   => $userData->billing_address,
            'to_billing_city'      => $userData->billing_city,
            'to_billing_state'     => $userData->billing_state,
            'to_billing_zipcode'   => $userData->billing_zipcode,
            'to_billing_country'   => $userData->billing_country,
            'to_billing_phone'     => $this->getBillingPhone($userData),
            'to_billing_email'     => $userData->billing_email ?? $userData->email,
            'to_vat_number'        => $userData->vat_number,
            'tax_name'             => $config[24]->config_value,
            'tax_type'             => $config[14]->config_value,
            'tax_value'            => $config[25]->config_value,
            'applied_coupon'       => null,
            'discounted_price'     => $discountPrice,
            'invoice_amount'       => $amountToBePaid,
            'subtotal'             => $subtotal,
            'tax_amount'           => ($subtotal * (float) $config[25]->config_value) / 100,
        ];
    }
}
