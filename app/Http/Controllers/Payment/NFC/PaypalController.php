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
use App\Coupon;
use App\NfcCardOrder;
use App\AppliedCoupon; 
use App\NfcCardDesign;
use App\Classes\OrderNFC;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PayPalHttp\HttpException;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PaypalController extends Controller
{
    protected $apiContext;

    public function __construct()
    {
        // Fetch PayPal configuration from database
        $paypalConfiguration = DB::table('config')->get();

        // Set up PayPal environment
        $clientId = $paypalConfiguration[4]->config_value;
        $clientSecret = $paypalConfiguration[5]->config_value;
        $mode = $paypalConfiguration[3]->config_value;

        if ($mode == "sandbox") {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        $this->apiContext = new PayPalHttpClient($environment);
    }

    public function nfcPayWithPayPal($nfcId, $couponId, $quantity = 1)
    {
        if (Auth::check()) {
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();
            $paypalConfiguration = DB::table('config')->get();
            $userData = Auth::user();

            // Quantity
            $quantity = max((int) $quantity, 1);

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

                // Construct PayPal order request
                $paypalRequest = new OrdersCreateRequest();
                $paypalRequest->prefer('return=representation');
                $paypalRequest->body = [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => $paypalConfiguration[1]->config_value,
                            'value' => $amountToBePaid,
                        ]
                    ]],
                    'application_context' => [
                        'cancel_url' => route('nfcpaypalPaymentStatus'),
                        'return_url' => route('nfcpaypalPaymentStatus'),
                    ]
                ];

                try {
                    // Create PayPal order
                    $response = $this->apiContext->execute($paypalRequest);
                    foreach ($response->result->links as $link) {
                        if ($link->rel == 'approve') {
                            $redirectUrl = $link->href;
                            break;
                        }
                    }

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
                    $transaction->payment_transaction_id = $response->result->id;
                    $transaction->payment_method = "PayPal";
                    $transaction->currency = $paypalConfiguration[1]->config_value;
                    $transaction->amount = $amountToBePaid;
                    $transaction->invoice_details = json_encode($quote->invoiceDetails($userData));
                    $transaction->payment_status = "pending";
                    $transaction->save();

                    // Coupon is not applied
                    if ($couponId != " ") {
                        // Save applied coupon
                        $appliedCoupon = new AppliedCoupon;
                        $appliedCoupon->applied_coupon_id = uniqid();
                        $appliedCoupon->transaction_id = $response->result->id;
                        $appliedCoupon->user_id = Auth::user()->id;
                        $appliedCoupon->coupon_id = $couponId;
                        $appliedCoupon->status = 0;
                        $appliedCoupon->save();
                    }

                    // Redirect to PayPal for payment
                    return Redirect::away($redirectUrl);
                } catch (\Exception $ex) {
                    if (config('app.debug')) {
                        return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', trans('Payment failed, Something went wrong!'));
                    } else {
                        return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', trans('Payment failed, Something went wrong!'));
                    }
                    return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', trans('Payment failed, Something went wrong!'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    // Update payment status
    public function nfcPaypalPaymentStatus(Request $request)
    {
        if (empty($request->PayerID) || empty($request->token)) {
            Session::put('error', trans('Payment cancelled!'));
            return redirect()->route('user.order.nfc.cards');
        }

        try {
            // Get the payment ID from the request
            $paymentId = $request->token;
            $transactionId = $paymentId;

            $request = new OrdersCaptureRequest($paymentId);
            $response = $this->apiContext->execute($request);

            if ($response->statusCode == 201) {

                // Place order
                $order = new OrderNFC();
                $order->order($transactionId, $response);

                return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
            } else {
                // Payment failed
                $order = new OrderNFC();
                $order->paymentFailed($paymentId);

                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment cancelled!'));
            }
        } catch (HttpException $e) { // Corrected class name
            // Handle the HTTP exception
            // Log the error or display an error message
            // Example: Log::error('PayPal HTTP Exception: ' . $e->getMessage());

            // Set an error message for the user
            Session::flash('failed', trans('An error occurred while communicating with PayPal. Please try again later.'));

            // Redirect back to the user nfc card checkout page or any other appropriate page
            return redirect()->route('user.order.nfc.cards');
        }
    }
}
