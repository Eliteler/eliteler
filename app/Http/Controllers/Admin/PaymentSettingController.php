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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // The configuration page is now a dialog on the payment gateways page
    public function configurePaymentMethod($id)
    {
        return redirect()->route('admin.payment.methods', ['edit' => $id]);
        }

    // Live credential check. Blank secrets fall back to the saved values.
    public function testPaymentConnection(Request $request, PaymentGatewayService $gateways, $id)
    {
        $request->merge($gateways->withSaved($id, $request->all()));

        try {
            // 1. PayPal (OAuth Token API)
        if ($id == '60964401751ab') {
                $clientKey = trim($request->paypal_client_key);
                $secret = trim($request->paypal_secret);
                $mode = $request->paypal_mode;

                $url = ($mode === 'sandbox')
                    ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token'
                    : 'https://api-m.paypal.com/v1/oauth2/token';

                // Send OAuth token request to PayPal
                $response = Http::asForm()
                    ->withBasicAuth($clientKey, $secret)
                    ->post($url, ['grant_type' => 'client_credentials']);

                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => "PayPal: Authentication Successful! (Mode: {$mode})"]);
                }
                return response()->json(['success' => false, 'message' => 'PayPal Server: ' . ($response->json('error_description') ?? 'Invalid Client ID or Secret Key')]);
        }

            // 2. Razorpay (Fetch Payments API)
        if ($id == '60964410731d9') {
                $clientKey = trim($request->razorpay_client_key);
                $secret = trim($request->razorpay_secret);

                // Ping Razorpay server
                $response = Http::withBasicAuth($clientKey, $secret)
                    ->get('https://api.razorpay.com/v1/payments?count=1');

                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Razorpay: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Razorpay Server: ' . ($response->json('error.description') ?? 'Authentication failed')]);
        }

            // 3. Stripe (Balance API)
        if ($id == '60964410732t9') {
                $pubKey = trim($request->stripe_publishable_key);
                $secretKey = trim($request->stripe_secret);

                if (!str_starts_with($pubKey, 'pk_test_') && !str_starts_with($pubKey, 'pk_live_')) {
                    return response()->json(['success' => false, 'message' => 'Stripe: Invalid Publishable Key format. Must start with pk_test_ or pk_live_.']);
        }

                // Ping Stripe balance endpoint
                $response = Http::withToken($secretKey)->get('https://api.stripe.com/v1/balance');
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Stripe: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Stripe Server: ' . ($response->json('error.message') ?? 'Invalid Secret Key')]);
            }

            // 4. Paystack (Balance API)
        if ($id == '60964410736592') {
                $pubKey = trim($request->paystack_public_key);
                $secretKey = trim($request->paystack_secret);

                if (!str_starts_with($pubKey, 'pk_test_') && !str_starts_with($pubKey, 'pk_live_')) {
                    return response()->json(['success' => false, 'message' => 'Paystack: Invalid Public Key format. Must start with pk_test_ or pk_live_.']);
        }

                // Ping Paystack server
                $response = Http::withToken($secretKey)->get('https://api.paystack.co/balance');
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Paystack: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Paystack Server: ' . ($response->json('message') ?? 'Invalid Secret Key')]);
            }

            // 5. Mollie (Methods API)
        if ($id == '6096441071589632') {
                $key = trim($request->mollie_key);

                // Ping Mollie server
                $response = Http::withToken($key)->get('https://api.mollie.com/v2/methods');
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Mollie: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Mollie Server: ' . ($response->json('detail') ?? 'Invalid API Key')]);
        }

            // 6. Mercado Pago (Payment Methods API)
        if ($id == '776111730465') {
                $token = trim($request->mercado_pago_access_token);

                // Ping Mercado Pago server
                $response = Http::withToken($token)->get('https://api.mercadopago.com/v1/payment_methods');
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Mercado Pago: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Mercado Pago Server: ' . ($response->json('message') ?? 'Invalid Access Token')]);
        }

            // 7. Flutterwave (Balance API)
        if ($id == '754201940107') {
                $secKey = trim($request->flw_secret_key);

                // Ping Flutterwave server
                $response = Http::withToken($secKey)->get('https://api.flutterwave.com/v3/balances');
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'Flutterwave: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Flutterwave Server: ' . ($response->json('message') ?? 'Invalid Secret Key')]);
        }

            // 8. Xendit Check (Invoices Endpoint Verification)
        if ($id == '278523098674') {
                $key = trim($request->xendit_secret_key);

                if (empty($key)) {
                    return response()->json(['success' => false, 'message' => 'Xendit Secret Key is required.']);
                }

                // Ping Xendit invoices API with basic auth
                $response = Http::withBasicAuth($key, '')
                    ->get('https://api.xendit.co/v2/invoices', [
                        'limit' => 1
                    ]);

                // Check if the API key is valid and authorized
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Xendit: Authentication Successful!'
            ]);
        }

                $result = $response->json();
                $errorMessage = $result['message'] ?? 'Invalid Secret Key or unauthorized.';
                return response()->json([
                    'success' => false,
                    'message' => 'Xendit Server: ' . $errorMessage
                ]);
            }

            // 9. Cashfree Check (Endpoint Authentication)
        if ($id == '278523098675') {
                $appId = trim($request->cashfree_app_id);
                $secret = trim($request->cashfree_secret_key);
                $mode = $request->cashfree_mode;

                if (empty($appId) || empty($secret)) {
                    return response()->json(['success' => false, 'message' => 'Cashfree App ID and Secret Key are required.']);
                }

                $baseUrl = ($mode === 'test') ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg';

                // Call Cashfree with a verification order ID to authenticate credentials
                $response = Http::withHeaders([
                    'x-client-id' => $appId,
                    'x-client-secret' => $secret,
                    'x-api-version' => '2023-08-01',
                    'Accept' => 'application/json',
                ])->get("{$baseUrl}/orders/PING_CHECK_AUTH");

                $result = $response->json();

                // If order_not_found is returned, it means authentication succeeded
                if (isset($result['code']) && $result['code'] === 'order_not_found') {
                    return response()->json([
                        'success' => true,
                        'message' => "Cashfree: Authentication Successful! (Mode: {$mode})"
            ]);
        }

                // Handle authentication failure from Cashfree
                $errorMessage = $result['message'] ?? 'Authentication failed. Invalid App ID or Secret Key.';
                return response()->json([
                    'success' => false,
                    'message' => 'Cashfree Server: ' . $errorMessage
                ]);
            }

            // 10. Iyzipay Check (Smart Mode Detection & Authentication)
        if ($id == '278523098676') {
                $apiKey = trim($request->iyzipay_api_key);
                $secretKey = trim($request->iyzipay_secret_key);
                $selectedMode = strtolower(trim($request->iyzipay_mode));

                if (empty($apiKey) || empty($secretKey)) {
                    return response()->json(['success' => false, 'message' => 'Both Iyzipay API Key and Secret Key are required.']);
                }

                // Detect environment based on Iyzipay key naming convention
                $isSandboxKey = str_starts_with($apiKey, 'sandbox-') || str_starts_with($secretKey, 'sandbox-');

                // Check if mode dropdown matches key type
                if ($isSandboxKey && $selectedMode === 'production') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Environment Mismatch: You entered a Sandbox Key (starts with "sandbox-"), but the Mode is set to "Production". Please change Mode to "Sandbox".'
            ]);
        }

                if (!$isSandboxKey && $selectedMode === 'sandbox') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Environment Mismatch: You entered a Live Production Key, but the Mode is set to "Sandbox". Please change Mode to "Production".'
                    ]);
                }

                // Determine target Iyzipay endpoint
                $baseUrl = ($selectedMode === 'sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
                $randomString = uniqid();

                // Prepare BIN test payload
                $payload = [
                    'locale' => 'en',
                    'conversationId' => 'TEST_' . time(),
                    'binNumber' => '454360',
                ];

                // Calculate Iyzipay HMAC SHA1 signature
                $pkiString = "[locale={$payload['locale']},conversationId={$payload['conversationId']},binNumber={$payload['binNumber']}]";
                $hash = base64_encode(sha1($apiKey . $randomString . $secretKey . $pkiString, true));
                $authorization = 'IYZWS ' . $apiKey . ':' . $hash;

                // Send verification request to Iyzipay
                $response = Http::withHeaders([
                    'Authorization' => $authorization,
                    'x-iyzi-rnd' => $randomString,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/payment/bin/check", $payload);

                $result = $response->json();

                if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
                    return response()->json([
                        'success' => true,
                        'message' => "Iyzipay: Authentication Successful! (Mode: {$selectedMode})"
                    ]);
                }

                $errorMessage = $result['errorMessage'] ?? 'Authentication failed. Please check your credentials and mode.';
                return response()->json([
                    'success' => false,
                    'message' => 'Iyzipay Server: ' . $errorMessage
                ]);
            }

            // 11. Paddle Check (Universal Endpoint Authentication)
            if ($id == '5992737427969') {
                $apiKey = trim($request->paddle_api_key);
                $sellerId = trim($request->paddle_seller_id);
                $clientToken = trim($request->paddle_client_side_token);
                $isSandbox = ($request->paddle_environment === 'true');

                if (empty($apiKey) || empty($sellerId) || empty($clientToken)) {
                    return response()->json(['success' => false, 'message' => 'Seller ID, API Key, and Client Side Token are all required.']);
                }

                // Validate Client-Side Token prefix according to selected environment
                if ($isSandbox && !str_starts_with($clientToken, 'test_')) {
                    return response()->json(['success' => false, 'message' => 'Paddle: Client Side Token must start with "test_" for Sandbox mode.']);
                }
                if (!$isSandbox && !str_starts_with($clientToken, 'live_')) {
                    return response()->json(['success' => false, 'message' => 'Paddle: Client Side Token must start with "live_" for Production mode.']);
                }

                $baseUrl = $isSandbox ? 'https://sandbox-api.paddle.com' : 'https://api.paddle.com';

                // Ping Paddle public event-types endpoint (requires authentication but no restricted product scopes)
                $response = Http::withToken($apiKey)->get("{$baseUrl}/event-types");

                // Success if 200 OK
                if ($response->successful()) {
                    $env = $isSandbox ? 'Sandbox' : 'Production';
                    return response()->json(['success' => true, 'message' => "Paddle: Authentication Successful! ({$env})"]);
                }

                $result = $response->json();

                // If forbidden, key is authentic but lacks scopes in Paddle dashboard
                if ($response->status() === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Paddle Server: API Key is authentic, but lacks permissions. Please check scopes/permissions under Developer Tools -> Authentication in your Paddle Dashboard.'
                    ]);
                }

                $errorMessage = $result['error']['detail'] ?? 'Invalid API Key or authentication failed.';
                return response()->json(['success' => false, 'message' => 'Paddle Server: ' . $errorMessage]);
            }

            // 12. PhonePe (Merchant Verification Check)
            if ($id == '19065566166715') {
                $merchantId = trim($request->clientId);
                $saltKey = trim($request->clientSecret);
                $saltIndex = trim($request->clientVersion);

                // PhonePe status endpoint check using SHA256 signature
                $endpoint = "/pg/v1/status/{$merchantId}/VERIFY_TEST_PING";
                $dataToHash = $endpoint . $saltKey;
                $sha256 = hash('sha256', $dataToHash);
                $checksum = $sha256 . '###' . $saltIndex;

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-VERIFY' => $checksum,
                    'X-MERCHANT-ID' => $merchantId,
                ])->get("https://api-preprod.phonepe.com/apis/pg-sandbox{$endpoint}");

                $result = $response->json();
                // PhonePe returns PAYMENT_NOT_FOUND if credentials are valid, or BAD_REQUEST if merchant/salt is invalid
                if (isset($result['code']) && in_array($result['code'], ['PAYMENT_NOT_FOUND', 'SUCCESS'])) {
                    return response()->json(['success' => true, 'message' => 'PhonePe: Merchant Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'PhonePe Server: ' . ($result['message'] ?? 'Invalid Client ID, Secret, or Version')]);
            }

            // 13. PayTR Check (Corrected get-token Parameters)
            if ($id == '5992737427970') {
                $merchantId = trim($request->paytr_merchant_id);
                $merchantKey = trim($request->paytr_merchant_key);
                $merchantSalt = trim($request->paytr_merchant_salt_key);
                $mode = $request->paytr_mode ?? '1';

                if (empty($merchantId) || empty($merchantKey) || empty($merchantSalt)) {
                    return response()->json(['success' => false, 'message' => 'PayTR Merchant ID, Key, and Salt are all required.']);
                }

                // Set parameters required by PayTR API
                $testOid = 'TEST' . time();
                $userIp = '127.0.0.1';
                $email = 'test@domain.com';
                $paymentAmount = '100'; // 1.00 TL in kurus
                $userBasket = base64_encode(json_encode([['Test Item', '1.00', 1]]));
                $noInstallment = '0';
                $maxInstallment = '0';
                $currency = 'TL';
                $testMode = ($mode == '1') ? '1' : '0';

                // Generate PayTR HMAC SHA256 token
                $hashStr = $merchantId . $userIp . $testOid . $email . $paymentAmount . $userBasket . $noInstallment . $maxInstallment . $currency . $testMode;
                $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $merchantSalt, $merchantKey, true));

                // Send authentication request to PayTR server
                $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', [
                    'merchant_id' => $merchantId,
                    'user_ip' => $userIp,
                    'merchant_oid' => $testOid,
                    'email' => $email,
                    'payment_amount' => $paymentAmount,
                    'paytr_token' => $paytrToken,
                    'user_basket' => $userBasket,
                    'debug_on' => '1',
                    'no_installment' => $noInstallment,
                    'max_installment' => $maxInstallment,
                    'user_name' => 'Test Customer',
                    'user_address' => 'Test Address',
                    'user_phone' => '05555555555',
                    'merchant_ok_url' => url('/'),
                    'merchant_fail_url' => url('/'),
                    'timeout_limit' => '30',
                    'currency' => $currency,
                    'test_mode' => $testMode,
                ]);

                $res = $response->json();

                // Check PayTR token response status
                if (isset($res['status']) && $res['status'] === 'success') {
                    return response()->json(['success' => true, 'message' => 'PayTR: Authentication Successful!']);
                }

                $errorMessage = $res['reason'] ?? 'Invalid Merchant ID, Key, or Salt.';
                return response()->json(['success' => false, 'message' => 'PayTR Server: ' . $errorMessage]);
            }

            // 14. Toyyibpay (Bill Verification Check)
            if ($id == '767510608137') {
                $secretKey = trim($request->toyyibpay_api_key);
                $categoryCode = trim($request->toyyibpay_category_code);
                $baseUrl = ($request->toyyibpay_mode === 'sandbox') ? 'https://dev.toyyibpay.com' : 'https://toyyibpay.com';

                // Ping Toyyibpay server
                $response = Http::asForm()->post("{$baseUrl}/index.php/api/getBillPaymentStatus", [
                    'userSecretKey' => $secretKey,
                    'billCode' => 'INVALID_TEST_BILL',
                ]);

                // If key is wrong, Toyyibpay responds with error or empty array
                if ($response->body() === '[]' || str_contains($response->body(), 'billCode')) {
                    return response()->json(['success' => true, 'message' => 'Toyyibpay: Authentication Successful!']);
                }
                return response()->json(['success' => false, 'message' => 'Toyyibpay Server: Invalid API Key or server unreachable.']);
            }

            // 15. Offline Bank Transfer (Manual)
        if ($id == '659644107y2g5') {
                return response()->json(['success' => true, 'message' => 'Offline Bank Transfer does not require server authentication.']);
        }

            return response()->json(['success' => false, 'message' => 'Gateway not supported.']);
        } catch (\Exception $e) {
            // Handle runtime connection or timeout errors
            return response()->json([
                'success' => false,
                'message' => 'Gateway Server unreachable: ' . $e->getMessage()
            ], 500);
        }
    }
}
