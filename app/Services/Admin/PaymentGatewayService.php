<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Credentials for the built-in payment gateways (stored in the config table).
 *
 * gateways.status: 1 active, 0 inactive, -1 deleted.
 * Field keys are the request names PaymentSettingController::testPaymentConnection
 * already reads, so saving and testing share one form.
 * Secret values never leave the server: the form shows them as saved, and a
 * blank secret keeps the stored value.
 */
class PaymentGatewayService
{
    public const BANK_TRANSFER = '659644107y2g5';

    private const SANDBOX_LIVE = ['sandbox' => 'Sandbox (test)', 'live' => 'Live'];

    /** gateway id => [field => [config_key, label, type (text|secret|select|textarea), options?]] */
    public const FIELDS = [
        '60964401751ab' => [ // PayPal
            'paypal_mode'       => ['paypal_mode', 'Mode', 'select', self::SANDBOX_LIVE],
            'paypal_client_key' => ['paypal_client_id', 'Client ID', 'text'],
            'paypal_secret'     => ['paypal_secret', 'Secret', 'secret'],
        ],
        '60964410731d9' => [ // Razorpay
            'razorpay_client_key' => ['razorpay_key', 'Key ID', 'text'],
            'razorpay_secret'     => ['razorpay_secret', 'Key secret', 'secret'],
        ],
        '19065566166715' => [ // PhonePe
            'clientId'      => ['phonepe_client_id', 'Client ID', 'text'],
            'clientVersion' => ['phonepe_client_version', 'Client version', 'text'],
            'clientSecret'  => ['phonepe_client_secret', 'Client secret', 'secret'],
        ],
        '60964410732t9' => [ // Stripe
            'stripe_publishable_key' => ['stripe_publishable_key', 'Publishable key', 'text'],
            'stripe_secret'          => ['stripe_secret', 'Secret key', 'secret'],
        ],
        '60964410736592' => [ // Paystack
            'paystack_public_key' => ['paystack_public_key', 'Public key', 'text'],
            'paystack_secret'     => ['paystack_secret_key', 'Secret key', 'secret'],
            'merchant_email'      => ['merchant_email', 'Merchant email', 'text'],
        ],
        '6096441071589632' => [ // Mollie
            'mollie_key' => ['mollie_key', 'API key', 'secret'],
        ],
        '776111730465' => [ // Mercado Pago
            'mercado_pago_public_key'   => ['mercado_pago_public_key', 'Public key', 'text'],
            'mercado_pago_access_token' => ['mercado_pago_access_token', 'Access token', 'secret'],
        ],
        '767510608137' => [ // Toyyibpay
            'toyyibpay_mode'          => ['toyyibpay_mode', 'Mode', 'select', self::SANDBOX_LIVE],
            'toyyibpay_api_key'       => ['toyyibpay_api_key', 'API key', 'secret'],
            'toyyibpay_category_code' => ['toyyibpay_category_code', 'Category code', 'text'],
        ],
        '754201940107' => [ // Flutterwave
            'flw_public_key'     => ['flw_public_key', 'Public key', 'text'],
            'flw_secret_key'     => ['flw_secret_key', 'Secret key', 'secret'],
            'flw_encryption_key' => ['flw_encryption_key', 'Encryption key', 'secret'],
            'flw_hash'           => ['flw_hash', 'Webhook secret hash', 'secret'],
        ],
        '5992737427969' => [ // Paddle
            'paddle_environment'       => ['paddle_environment', 'Mode', 'select', ['true' => 'Sandbox (test)', 'false' => 'Live']],
            'paddle_seller_id'         => ['paddle_seller_id', 'Seller ID', 'text'],
            'paddle_api_key'           => ['paddle_api_key', 'API key', 'secret'],
            'paddle_client_side_token' => ['paddle_client_side_token', 'Client-side token', 'text'],
        ],
        '5992737427970' => [ // PayTR
            'paytr_mode'              => ['paytr_mode', 'Mode', 'select', ['1' => 'Test', '0' => 'Live']],
            'paytr_merchant_id'       => ['paytr_merchant_id', 'Merchant ID', 'text'],
            'paytr_merchant_key'      => ['paytr_merchant_key', 'Merchant key', 'secret'],
            'paytr_merchant_salt_key' => ['paytr_merchant_salt', 'Merchant salt', 'secret'],
        ],
        '278523098674' => [ // Xendit
            'xendit_secret_key' => ['xendit_secret_key', 'Secret key', 'secret'],
        ],
        '278523098675' => [ // Cashfree
            'cashfree_mode'       => ['cashfree_mode', 'Mode', 'select', ['test' => 'Sandbox (test)', 'live' => 'Live']],
            'cashfree_app_id'     => ['cashfree_app_id', 'App ID', 'text'],
            'cashfree_secret_key' => ['cashfree_secret_key', 'Secret key', 'secret'],
        ],
        '278523098676' => [ // Iyzipay
            'iyzipay_mode'       => ['IYZICO_MODE', 'Mode', 'select', ['sandbox' => 'Sandbox (test)', 'production' => 'Live']],
            'iyzipay_api_key'    => ['IYZICO_API_KEY', 'API key', 'text'],
            'iyzipay_secret_key' => ['IYZICO_SECRET_KEY', 'Secret key', 'secret'],
        ],
        self::BANK_TRANSFER => [
            'bank_transfer' => ['bank_transfer', 'Bank details shown to customers', 'textarea'],
        ],
    ];

    public function hasFields(string $id): bool
    {
        return isset(self::FIELDS[$id]);
    }

    /** Field definitions for the form, with saved values (secrets only flagged as saved). */
    public function form(string $id, $config): array
    {
        return collect(self::FIELDS[$id] ?? [])->map(function ($field, $name) use ($config) {
            [$key, $label, $type] = $field;
            $value = (string) ($config[$key] ?? '');

            return [
                'name'    => $name,
                'label'   => __($label),
                'type'    => $type,
                'value'   => $type === 'secret' ? '' : $value,
                'saved'   => $value !== '',
                'options' => isset($field[3]) ? collect($field[3])->map(fn ($text, $value) => ['value' => (string) $value, 'label' => __($text)])->values() : null,
            ];
        })->values()->all();
    }

    /** A gateway is ready when every non-select field has a value. */
    public function configured(string $id, $config): bool
    {
        return collect(self::FIELDS[$id] ?? [])
            ->filter(fn ($field) => $field[2] !== 'select')
            ->every(fn ($field) => (string) ($config[$field[0]] ?? '') !== '');
    }

    /** Callback URLs admins must copy into the gateway's dashboard. */
    public function links(string $id): array
    {
        $links = [
            '5992737427969' => ['Callback URL' => 'paddle.payment.status', 'Webhook URL' => 'paddle.payment.webhook'],
            '5992737427970' => ['Callback URL' => 'paytr.payment.status', 'Failure URL' => 'paytr.payment.failure'],
        ][$id] ?? [];

        return collect($links)
            ->filter(fn ($route) => Route::has($route))
            ->map(fn ($route, $label) => ['label' => __($label), 'url' => route($route)])
            ->values()
            ->all();
    }

    /** Fill blank secrets with the stored values (used before testing). */
    public function withSaved(string $id, array $input): array
    {
        $config = $this->config($id);

        foreach (self::FIELDS[$id] ?? [] as $name => [$key]) {
            if (trim((string) ($input[$name] ?? '')) === '') {
                $input[$name] = $config[$key] ?? '';
            }
        }

        return $input;
    }

    public function save(string $id, array $input): void
    {
        foreach (self::FIELDS[$id] ?? [] as $name => [$key, , $type]) {
            $value = $input[$name] ?? null;

            if ($type === 'secret' && trim((string) $value) === '') {
                continue;
            }

            DB::table('config')->where('config_key', $key)->update([
                'config_value' => is_string($value) ? trim($value) : $value,
                'updated_at'   => now(),
            ]);
        }
    }

    /** Validation rules for the credential fields. */
    public function rules(string $id): array
    {
        return collect(self::FIELDS[$id] ?? [])->map(fn ($field) => match ($field[2]) {
            'select'   => ['required', 'in:' . implode(',', array_map('strval', array_keys($field[3])))],
            'textarea' => ['nullable', 'string', 'max:5000'],
            default    => ['nullable', 'string', 'max:500'],
        })->all();
    }

    public function attributes(string $id): array
    {
        return collect(self::FIELDS[$id] ?? [])->map(fn ($field) => mb_strtolower(__($field[1])))->all();
    }

    public function config(?string $id = null)
    {
        $keys = collect($id ? (self::FIELDS[$id] ?? []) : collect(self::FIELDS)->collapse())->pluck(0)->all();

        return DB::table('config')->whereIn('config_key', $keys)->pluck('config_value', 'config_key');
    }
}
