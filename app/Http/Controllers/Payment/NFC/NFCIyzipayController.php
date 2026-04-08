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

use App\User;
use App\Coupon;
use Iyzipay\Options;
use App\NfcCardOrder;
use App\AppliedCoupon;
use App\NfcCardDesign;
use App\Classes\OrderNFC;
use Illuminate\Support\Str;
use App\Classes\UpgradePlan;
use Illuminate\Http\Request;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\PaymentGroup;
use App\NfcCardOrderTransaction;
use Iyzipay\Model\BasketItemType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

class NFCIyzipayController extends Controller
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

    public function nfcGeneratePaymentLink($nfcId, $couponId)
    {
        if (Auth::user()) {
            // Queries
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();
            $nfcDetails = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

            // Check nfc card details
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

                // NFC Card Order ID
                $nfcCardOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());
                $nfcTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

                // Callback URL
                $callbackUrl = route('nfc.iyzipay.payment.status');

                // Check coupon type
                if ($couponDetails != null) {
                    if ($couponDetails->coupon_type == 'fixed') {
                        // Applied tax in total
                        $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);

                        // Get discount in nfc card price
                        $discountPrice = $couponDetails->coupon_amount;

                        // Total
                        $amountToBePaid = ($nfcDetails->nfc_card_price + $appliedTaxInTotal) - $discountPrice;
                        $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                        // Coupon is applied
                        $appliedCoupon = $couponDetails->coupon_code;
                    } else {
                        // Applied tax in total
                        $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);

                        // Get discount in nfc card price
                        $discountPrice = $nfcDetails->nfc_card_price * $couponDetails->coupon_amount / 100;

                        // Total
                        $amountToBePaid = ($nfcDetails->nfc_card_price + $appliedTaxInTotal) - $discountPrice;
                        $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                        // Coupon is applied
                        $appliedCoupon = $couponDetails->coupon_code;
                    }
                } else {
                    // Applied tax in total
                    $appliedTaxInTotal = ((float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100);

                    // Total
                    $amountToBePaid = ($nfcDetails->nfc_card_price + $appliedTaxInTotal);
                }

                $amountToBePaidPaise = $amountToBePaid;

                try {
                    $request = new CreateCheckoutFormInitializeRequest();
                    $request->setLocale('en');
                    $conversationId = 'order_' . uniqid();
                    $request->setConversationId($conversationId);
                    $request->setPrice($amountToBePaidPaise);
                    $request->setPaidPrice($amountToBePaidPaise);
                    $request->setCurrency($config[1]->config_value);
                    $request->setBasketId($nfcTransactionId);
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
                    $item->setId($nfcDetails->nfc_card_id);
                    $item->setName($nfcDetails->nfc_card_name);
                    $item->setCategory1("NFC Card");
                    $item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                    $item->setPrice($amountToBePaidPaise);
                    $basketItems[] = $item;

                    $request->setBasketItems($basketItems);

                    try {
                        $checkoutForm = \Iyzipay\Model\CheckoutFormInitialize::create($request, $this->options);
                        $rawResult = json_decode($checkoutForm->getRawResult(), true);

                        // Get JSON response
                        if (isset($rawResult['status']) && $rawResult['status'] === 'success' && isset($rawResult['paymentPageUrl'])) {
                            // Generate JSON
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
                            $invoice_details['subtotal'] = $nfcDetails->nfc_card_price;
                            $invoice_details['tax_name'] = $config[24]->config_value;
                            $invoice_details['tax_type'] = $config[14]->config_value;
                            $invoice_details['tax_value'] = $config[25]->config_value;
                            $invoice_details['tax_amount'] = $appliedTaxInTotal;
                            $invoice_details['applied_coupon'] = $appliedCoupon;
                            $invoice_details['discounted_price'] = $discountPrice;
                            $invoice_details['invoice_amount'] = $amountToBePaid;

                            // Store transaction details in nfc_card_order_id table before redirecting to Phonepe
                            $nfcCardOrder = new NfcCardOrder();
                            $nfcCardOrder->nfc_card_order_id = $nfcCardOrderId;
                            $nfcCardOrder->user_id = Auth::id();
                            $nfcCardOrder->nfc_card_id = $nfcId;
                            $nfcCardOrder->nfc_card_order_transaction_id = $nfcTransactionId;
                            $nfcCardOrder->order_details = json_encode($this->prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice));
                            $nfcCardOrder->delivery_address = json_encode($this->prepareDeliveryAddress($userData));
                            $nfcCardOrder->delivery_note = "-";
                            $nfcCardOrder->order_status = 'pending';
                            $nfcCardOrder->status = 1;
                            $nfcCardOrder->save();

                            // Store transaction details in nfc_card_order_transactions table before redirecting to Phonepe
                            $transaction = new NfcCardOrderTransaction();
                            $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
                            $transaction->nfc_card_order_id = $nfcCardOrderId;
                            $transaction->payment_transaction_id = $rawResult['token'];
                            $transaction->payment_method = "Iyzipay";
                            $transaction->currency = $config[1]->config_value;
                            $transaction->amount = $amountToBePaid;
                            $transaction->invoice_details = json_encode($this->prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice));
                            $transaction->payment_status = "pending";
                            $transaction->save();

                            // Coupon is not applied
                            if ($couponId != " ") {
                                // Save applied coupon
                                $appliedCoupon = new AppliedCoupon;
                                $appliedCoupon->applied_coupon_id = uniqid();
                                $appliedCoupon->transaction_id = $rawResult['token'];
                                $appliedCoupon->user_id = Auth::user()->id;
                                $appliedCoupon->coupon_id = $couponId;
                                $appliedCoupon->status = 0;
                                $appliedCoupon->save();
                            }

                            return Redirect::away($rawResult['paymentPageUrl']);
                        } else {
                            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
                        }
                    } catch (\Throwable $e) {
                        dd($e);
                        return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed.'));
                    }
                } catch (\Exception $e) {
                    return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed.'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function nfcIyzipayPaymentStatus(Request $request)
    {
        // Iyzipay sends back the token
        $token = $request->get('token');

        // Retrieve user from session
        $userId = $request->query('user_id');

        if ($userId) {
            Auth::loginUsingId($userId); // Restore the user session if needed
        }

        // Get transaction details based on the transactionId
        $transaction_details = NfcCardOrderTransaction::where('payment_transaction_id', $token)->first();

        if (!$transaction_details) {
            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Transaction not found or already processed.'));
        }

        // Retrieve the checkout form details using the token
        $retrieveRequest = new RetrieveCheckoutFormRequest();
        $retrieveRequest->setLocale('en');
        $retrieveRequest->setToken($token);

        try {
            $checkoutForm = CheckoutForm::retrieve($retrieveRequest, $this->options);
            $rawResult = json_decode($checkoutForm->getRawResult(), true);

            if ($rawResult && isset($rawResult['paymentStatus'])) {
                // Order place
                $order = new OrderNFC();
                $order->order($transaction_details->payment_transaction_id, "PAID");

                // Update transaction id
                NfcCardOrderTransaction::where('nfc_card_order_transaction_id', $transaction_details->nfc_card_order_transaction_id)->update(['payment_transaction_id' => $rawResult['paymentId']]);

                return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!. If you want to NFC Card Logo, please upload it from the "Manage NFC Cards" section.'));
            } else {
                // Update payment status
                NfcCardOrderTransaction::where('nfc_card_order_transaction_id', $transaction_details->nfc_card_order_transaction_id)->update(['payment_status' => 'FAILED']);

                return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
            }
        } catch (\Throwable $e) {
            // Update payment status
            NfcCardOrderTransaction::where('nfc_card_order_transaction_id', $transaction_details->nfc_card_order_transaction_id)->update(['payment_status' => 'FAILED']);

            return redirect()->route('user.order.nfc.cards')->with('failed', trans('Payment failed.'));
        }

        // Update payment status
        NfcCardOrderTransaction::where('nfc_card_order_transaction_id', $transaction_details->nfc_card_order_transaction_id)->update(['payment_status' => 'FAILED']);

        return redirect()->route('user.order.nfc.cards')->with('failed', trans('Order failed!'));
    }

    private function prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice)
    {
        // Prepare invoice details
        $invoiceDetails = [
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
            'tax_name' => $config[24]->config_value,
            'tax_type' => $config[14]->config_value,
            'tax_value' => $config[25]->config_value,
            'applied_coupon' => $appliedCoupon,
            'discounted_price' => $discountPrice,
            'invoice_amount' => $amountToBePaid,
            'subtotal' => $nfcDetails->nfc_card_price,
            'tax_amount' => (float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100
        ];

        return $invoiceDetails;
    }


    // Prepare oder details
    private function prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice)
    {
        // Prepare invoice details
        $invoiceDetails = [
            'nfc_card_id' => $nfcDetails->nfc_card_id,
            'order_item' => $nfcDetails->nfc_card_name,
            'order_description' => $nfcDetails->nfc_card_description,
            'order_quantity' => 1,
            'price' => $nfcDetails->nfc_card_price,
            'invoice_amount' => $amountToBePaid,
            'tax_name' => $config[24]->config_value,
            'tax_type' => $config[14]->config_value,
            'tax_value' => $config[25]->config_value,
            'tax_amount' => (float)($nfcDetails->nfc_card_price) * (float)($config[25]->config_value) / 100,
            'applied_coupon' => $appliedCoupon,
            'discounted_price' => $discountPrice,
            'subtotal' => $nfcDetails->nfc_card_price
        ];

        return $invoiceDetails;
    }

    // Prepare delivery address
    private function prepareDeliveryAddress($userData)
    {
        // Prepare delivery address
        $deliveryAddress = [
            'billing_name' => $userData->billing_name,
            'billing_address' => $userData->billing_address,
            'billing_city' => $userData->billing_city,
            'billing_state' => $userData->billing_state,
            'billing_zipcode' => $userData->billing_zipcode,
            'billing_country' => $userData->billing_country,
            'billing_phone' => $userData->billing_phone,
            'billing_email' => $userData->billing_email,
            'shipping_name' => $userData->billing_name,
            'shipping_address' => $userData->billing_address,
            'shipping_city' => $userData->billing_city,
            'shipping_state' => $userData->billing_state,
            'shipping_zipcode' => $userData->billing_zipcode,
            'shipping_country' => $userData->billing_country,
            'shipping_phone' => $userData->billing_phone,
            'shipping_email' => $userData->billing_email,
            'type' => $userData->type,
            'vat_number' => $userData->vat_number
        ];

        return $deliveryAddress;
    }
}
