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
use App\User;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ToyyibpayController extends Controller
{
    protected $apiKey;
    protected $categoryCode;
    protected $baseUrl;

    public function __construct()
    {
        $config = DB::table('config')->get();

        $this->apiKey = $config[49]->config_value;
        $this->categoryCode = $config[50]->config_value;
        $this->baseUrl = "https://toyyibpay.com/";

        if ($config[54]->config_value == 'sandbox') {
            $this->baseUrl = "https://dev.toyyibpay.com/";
        }
    }

    /**
     * Prepare ToyyibPay Payment
     */
    public function prepareToyyibpay(Request $request, $planId, $couponId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $config = DB::table('config')->get();

        $userData = User::where('id', Auth::user()->id)->first();

        // AI Credits Plan
        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        if (!$plan_details) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Invalid plan!'));
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

        $amountToBePaidPaise = $amountToBePaid * 100;

        // Guzzle Client
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);

        // Bill Details
        $billDetails = [
            'billName' => 'AI Credits Payment',
            'billDescription' => 'AI Credits Payment',
            'billAmount' => $amountToBePaidPaise,
            'billReturnUrl' => route('ai.credits.toyyibpay.payment.success'),
            'billCallbackUrl' => route('ai.credits.toyyibpay.payment.status'),
            'billExternalReferenceNo' => $aiCreditsTransactionId,
            'userSecretKey' => $this->apiKey,
            'categoryCode' => $this->categoryCode,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billTo' => Auth::user()->name,
            'billEmail' => Auth::user()->email,
            'billPhone' => Auth::user()->billing_phone == null
                ? '9876543210'
                : Auth::user()->billing_phone,
        ];

        // Create Bill
        $response = $client->post('index.php/api/createBill', [
            'form_params' => $billDetails,
        ]);

        // Response
        $responseBody = json_decode($response->getBody(), true);

        // Success
        if (isset($responseBody[0]['BillCode'])) {

            $billCode = $responseBody[0]['BillCode'];

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
            $transaction->payment_transaction_id = $billCode;
            $transaction->user_id = Auth::user()->id;
            $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
            $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
            $transaction->payment_method = "Toyyibpay";
            $transaction->currency = $config[1]->config_value;
            $transaction->amount = $amountToBePaid;
            $transaction->invoice_details = json_encode($invoice_details);
            $transaction->payment_status = "pending";

            $transaction->save();

            // Save Coupon
            if ($couponId != " ") {

                $appliedCoupon = new AppliedCoupon();

                $appliedCoupon->applied_coupon_id = uniqid();
                $appliedCoupon->transaction_id = $aiCreditsTransactionId;
                $appliedCoupon->user_id = Auth::user()->id;
                $appliedCoupon->coupon_id = $couponId;
                $appliedCoupon->status = 0;

                $appliedCoupon->save();
            }

            return redirect()->to($this->baseUrl . $billCode);
        }

        return redirect()
            ->route('user.ai.credits.plans')
            ->with('failed', trans('Failed to initiate payment.'));
    }

    /**
     * Payment Status
     */
    public function toyyibpayPaymentStatus(Request $request)
    {
        $statusId = $request['status_id'];
        $billCode = $request['billcode'];
        $transactionId = $request['transaction_id'];

        $updatedData = $this->toyyibpayPaymentSuccessStatic(
            $statusId,
            $billCode,
            $transactionId
        );

        if (isset($updatedData['success']) && $updatedData['success']) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        } else {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed!'));
        }
    }

    /**
     * Payment Success
     */
    public function toyyibpayPaymentSuccess(Request $request)
    {
        $statusId = $request['status_id'];
        $billCode = $request['billcode'];
        $transactionId = $request['transaction_id'];

        $updatedData = $this->toyyibpayPaymentSuccessStatic(
            $statusId,
            $billCode,
            $transactionId
        );

        if (isset($updatedData['success']) && $updatedData['success']) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        } else {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed!'));
        }
    }

    /**
     * Payment Success Static
     */
    public function toyyibpayPaymentSuccessStatic($statusId, $billCode, $transactionId)
    {
        // Validation
        if ($billCode == null) {

            AiCreditsTransaction::where('payment_transaction_id', $billCode)
                ->update([
                    'payment_status' => 'failed'
                ]);

            return [
                'failed' => trans('Transaction not found.'),
            ];
        }

        // Success
        if ($statusId == 1) {

            // Config
            $config = DB::table('config')->get();

            // Transaction
            $transaction_details = AiCreditsTransaction::where(
                'payment_transaction_id',
                $billCode
            )->first();

            // Check
            if (!$transaction_details) {

                return [
                    'failed' => trans('Transaction not found.'),
                ];
            }

            // Already Paid
            if ($transaction_details->payment_status == 'paid') {

                return [
                    'success' => trans('Payment already completed.')
                ];
            }

            // Plan
            $planDetails = AiCreditsPlan::where(
                'ai_credits_plan_id',
                $transaction_details->ai_credits_plan_id
            )->first();

            // Invoice
            $invoice_count = AiCreditsTransaction::where(
                "invoice_prefix",
                $config[15]->config_value
            )->count();

            $invoice_number = $invoice_count + 1;

            // Update Transaction
            $transaction_details->payment_transaction_id = $transactionId;
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
                $transaction_details->ai_credits_transaction_id
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
                'gobiz_transaction_id' => $transactionId,
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

            return [
                'success' => trans('Payment successful!')
            ];
        }

        // Pending
        if ($statusId == 2 || $statusId == 4) {

            AiCreditsTransaction::where(
                'payment_transaction_id',
                $billCode
            )->update([
                'payment_status' => 'pending'
            ]);

            return [
                'failed' => trans('Payment pending'),
            ];
        }

        // Failed
        if ($statusId == 3) {

            AiCreditsTransaction::where(
                'payment_transaction_id',
                $billCode
            )->update([
                'payment_status' => 'failed'
            ]);

            return [
                'failed' => trans('Payment failed'),
            ];
        }
    }
}
