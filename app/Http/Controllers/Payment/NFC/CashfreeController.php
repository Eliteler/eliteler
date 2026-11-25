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
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CashfreeController extends Controller
{
    protected $appId;
    protected $secretKey;
    protected $baseUrl;
    protected $mode;

    public function __construct()
    {
        // Get API key and category code from config table
        $config = DB::table('config')->get();

        $this->appId = $config[85]->config_value;
        $this->secretKey = $config[86]->config_value;
        $this->baseUrl = $config[84]->config_value === 'test'
            ? 'https://sandbox.cashfree.com/pg'
            : 'https://api.cashfree.com/pg';
    }

    // Generate payment link
    public function nfcGeneratePaymentLink(Request $request, $nfcId, $couponId, $quantity = 1)
    {
        // Check if user is logged in
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        // Queries
        $config = DB::table('config')->get();
        $userData = User::where('id', Auth::user()->id)->first();

        // Quantity
        $quantity = max((int) $quantity, 1);

        // NFC Card
        $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)
            ->where('status', 1)
            ->first();

        // Check nfc card details
        if ($nfcDetails == null) {
            return view('errors.404');
        }

        // Check applied coupon
        $couponDetails = Coupon::where('used_for', 'nfc')
            ->where('coupon_code', $couponId)
            ->first();

        // Applied tax in total
        $appliedTaxInTotal = 0;

        // Discount price
        $discountPrice = 0;

        // Applied coupon
        $appliedCoupon = null;

        // Price
        $subtotal = $nfcDetails->nfc_card_price * $quantity;

        // NFC Card Order ID
        $nfcCardOrderId = 'OD' . preg_replace('/\D/', '', Str::uuid());
        $nfcTransactionId = 'TX' . preg_replace('/\D/', '', Str::uuid());

        /*
        |--------------------------------------------------------------------------
        | Coupon Calculation
        |--------------------------------------------------------------------------
        */

        // Price: same calculation as the checkout page (App\Services\SmartProducts\OrderQuote)
        $quote             = OrderQuote::make($nfcDetails, $quantity, $couponDetails);
        $subtotal          = $quote->subtotal;
        $appliedTaxInTotal = $quote->tax;
        $discountPrice     = $quote->discount;
        $appliedCoupon     = $quote->couponCode;
        $amountToBePaid    = $quote->total;

        $amountToBePaidPaise = (float) $amountToBePaid;

        $data = [
            'order_id' => $nfcTransactionId,
            'order_amount' => $amountToBePaidPaise,
            'order_currency' => $config[1]->config_value,
            'customer_details' => [
                'customer_id' => Auth::user()->user_id,
                'customer_name' => Auth::user()->name,
                'customer_email' => Auth::user()->email,
                'customer_phone' => Auth::user()->billing_country_code . Auth::user()->billing_phone,
            ],
            'order_meta' => [
                'return_url' => route('nfc.cashfree.payment.status') . '?order_id={order_id}',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-version' => '2022-01-01',
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
            ])->post("{$this->baseUrl}/orders", $data);

            $responseBody = $response->json();

            if (isset($responseBody['order_status']) && $responseBody['order_status'] === 'ACTIVE') {
                // Order ID
                $orderId = $responseBody['cf_order_id'];

                // Store order details before redirecting to Cashfree
                $nfcCardOrder = new NfcCardOrder();
                $nfcCardOrder->nfc_card_order_id = $nfcCardOrderId;
                $nfcCardOrder->user_id = Auth::id();
                $nfcCardOrder->nfc_card_id = $nfcId;
                $nfcCardOrder->nfc_card_order_transaction_id = $nfcTransactionId;
                $nfcCardOrder->order_details = json_encode($quote->orderDetails());
                $nfcCardOrder->delivery_address = json_encode(OrderQuote::deliveryAddress($userData));
                $nfcCardOrder->delivery_note = '-';
                $nfcCardOrder->order_status = 'pending';
                $nfcCardOrder->status = 1;
                $nfcCardOrder->save();

                // Store transaction details before redirecting to Cashfree
                $transaction = new NfcCardOrderTransaction();
                $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                $transaction->nfc_card_order_id = $nfcCardOrderId;
                $transaction->payment_transaction_id = $nfcTransactionId;
                $transaction->payment_method = 'Cashfree';
                $transaction->currency = $config[1]->config_value;
                $transaction->amount = $amountToBePaid;
                $transaction->invoice_details = json_encode($quote->invoiceDetails($userData));
                $transaction->payment_status = 'pending';
                $transaction->save();

                // Coupon is applied
                if ($couponId != ' ') {
                    $appliedCouponData = new AppliedCoupon();

                    $appliedCouponData->applied_coupon_id = uniqid();
                    $appliedCouponData->transaction_id = $orderId;
                    $appliedCouponData->user_id = Auth::user()->id;
                    $appliedCouponData->coupon_id = $couponId;
                    $appliedCouponData->status = 0;

                    $appliedCouponData->save();
                }

                // Redirect to payment link if available
                if (isset($responseBody['payment_link'])) {
                    return redirect()->to($responseBody['payment_link']);
                }
            }

            return redirect()
                ->route('user.order.nfc.cards')
                ->with('failed', trans('Order failed!'));
        } catch (\Exception $e) {
            return redirect()
                ->route('user.order.nfc.cards')
                ->with('failed', trans('Order failed!'));
        }
    }

    // Get payment status
    public function nfcCashfreePaymentStatus(Request $request)
    {
        // Order ID
        $orderId = $request->query('order_id');

        // Get last transaction id and user id
        $nfcTransactionDetails = NfcCardOrderTransaction::where('payment_transaction_id', $orderId)
            ->orderBy('id', 'desc')
            ->first();

        // Queries
        $config = DB::table('config')->get();

        $appId = $config[85]->config_value;
        $secretKey = $config[86]->config_value;
        $mode = $config[84]->config_value;

        $baseUrl = $mode === 'test'
            ? 'https://sandbox.cashfree.com/pg/orders/' . $orderId . '/payments'
            : 'https://api.cashfree.com/pg/orders/' . $orderId . '/payments';

        $response = Http::withHeaders([
            'x-client-id' => $appId,
            'x-client-secret' => $secretKey,
            'Content-Type' => 'application/json',
            'x-api-version' => '2022-09-01',
        ])->get($baseUrl);

        if ($response->successful()) {
            $paymentDetailsArray = json_decode($response->body(), true);

            // Check if response is valid and contains at least one element
            if (!is_array($paymentDetailsArray) || empty($paymentDetailsArray[0])) {
                return redirect()
                    ->route('user.order.nfc.cards')
                    ->with('failed', trans('Invalid payment response.'));
            }

            $paymentDetails = (object) $paymentDetailsArray[0];

            // Check payment status
            if (isset($paymentDetails->payment_status) && $paymentDetails->payment_status == 'SUCCESS') {
                // Get transaction ID
                $orderId = $paymentDetails->order_id ?? null;
                $transactionId = $paymentDetails->cf_payment_id ?? null;

                if (!$orderId || !$transactionId) {
                    return redirect()
                        ->route('user.order.nfc.cards')
                        ->with('failed', trans('Missing order or transaction ID.'));
                }

                // Place order
                $order = new OrderNFC();
                $order->order($orderId, $paymentDetails);

                // Update transaction id
                NfcCardOrderTransaction::where(
                    'payment_transaction_id',
                    $nfcTransactionDetails->payment_transaction_id
                )->update([
                    'payment_transaction_id' => $transactionId,
                ]);

                // Redirect
                return redirect()
                    ->route('user.order.nfc.cards')
                    ->with(
                        'success',
                        trans(
                            'Order has been successfully placed! If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'
                        )
                    );
            }

            // Payment failed
            $order = new OrderNFC();
            $order->paymentFailed($nfcTransactionDetails->payment_transaction_id);

            return redirect()
                ->route('user.order.nfc.cards')
                ->with('failed', trans('Order failed!'));
        }

        // Payment failed
        $order = new OrderNFC();
        $order->paymentFailed($nfcTransactionDetails->payment_transaction_id);

        return redirect()
            ->route('user.order.nfc.cards')
            ->with('failed', trans('Order failed!'));
    }
}
