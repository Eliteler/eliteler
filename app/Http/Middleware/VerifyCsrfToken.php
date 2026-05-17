<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/phonepe-payment-status',
        '/mercadopago-callback',
        '/paytr-payment-status',
        '/paytr-payment-failure',
        '/paytr-payment-webhook',
        '/iyzipay-payment-status',
        '/paddle-payment-webhook',
        '/flutterwave-callback',

        '/nfc-phonepe-payment-status',
        '/nfc-mercadopago-callback',
        '/nfc-paytr-payment-status',
        '/nfc-paytr-payment-failure',
        '/nfc-paytr-payment-webhook',
        '/nfc/phonepe-payment-status',
        '/nfc/iyzipay-payment-status',
        '/nfc/paddle-payment-webhook',
        '/nfc/flutterwave-callback',

        '/payment/mercado-pago-recurring/webhook',

        '/ai-credits/phonepe-payment-status',
        '/ai-credits/mercadopago-callback',
        '/ai-credits/paytr-payment-status',
        '/ai-credits/paytr-payment-failure',
        '/ai-credits/paytr-payment-webhook',
        '/ai-credits/iyzipay-payment-status',
        '/ai-credits/paddle-payment-webhook',
        '/ai-credits/flutterwave-callback',

        '{id}',
        '{id}/product/*',
        '{id}/categories',
        'dynamic-card/*',
        'check-password/*',
        'download/*',
        'nfc/*',

        // Store policies
        '{id}/privacy-policy',
        '{id}/terms-and-conditions',
        '{id}/return-policy',
        '{id}/shipping-policy',
        '{id}/cookie-policy',
        '{id}/contact',

        // Store & orders
        'order/place',

        // Appointments & services
        'get-available-time-slots',
        'book-appointment',
        'book-service',

        // Enquiry & newsletter
        'sent-enquiry',
        'subscribe/newsletter',
        'subscribe/store/newsletter',

        // Locale
        'set-locale',
    ];
}
