<?php
namespace App\Services\Admin;

use App\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Subscription plans (plans table).
 *
 * plans.status: 1 active, 0 disabled, 2 archived ("deleted" in the old UI).
 * plans.plan_type: VCARD, STORE or BOTH. Limits use 999 for unlimited,
 * ai_credits uses -1 for unlimited, validity 9999 means forever.
 *
 * Customers keep a snapshot of the plan in users.plan_details, so edits apply
 * to new purchases and renewals, not to current subscriptions.
 */
class PlanService
{
    public const TYPES = ['BOTH', 'VCARD', 'STORE'];

    public const UNLIMITED = 999;

    public const FOREVER = 9999;

    /** Business card limits (numbers). */
    public const VCARD_LIMITS = [
        'no_of_vcards', 'no_of_services', 'no_of_vcard_products', 'no_of_links',
        'no_of_payments', 'no_testimonials', 'no_of_galleries', 'no_of_enquires',
    ];

    /** Business card feature switches. */
    public const VCARD_FEATURES = ['business_hours', 'appointment', 'service_booking', 'contact_form', 'password_protected'];

    public const STORE_LIMITS = ['no_of_stores', 'no_of_categories', 'no_of_store_products'];

    /** Switches shared by all plan types. */
    public const COMMON_FEATURES = [
        'custom_domain', 'nfc_card', 'pwa', 'advanced_settings', 'personalized_link',
        'hide_branding', 'free_setup', 'free_support',
    ];

    /** Payment gateway / app store ids, only stored when the plugin is installed. */
    public const INTEGRATIONS = [
        'paddle_plan_price_id'        => 'PaddleRecurring',
        'dodo_payments_plan_price_id' => 'DodoPaymentsRecurring',
        'paypal_plan_id'              => 'PaypalRecurring',
        'in_app_purchase_type'        => 'MobileAppAPI',
        'in_app_product_id'           => 'MobileAppAPI',
        'in_app_purchase_id'          => 'MobileAppAPI',
    ];

    /** @return array<string, bool> */
    public function plugins(): array
    {
        return [
            'googleWallet' => plugin_active('GoogleWallet'),
            'paddle'       => plugin_active('PaddleRecurring'),
            'dodo'         => plugin_active('DodoPaymentsRecurring'),
            'paypal'       => plugin_active('PaypalRecurring'),
            'inApp'        => plugin_active('MobileAppAPI'),
            'aiBuilder'    => DB::table('aibuilder_settings')->first()->aibuilder == 1,
            'nfc'          => ($config[76]->config_value ?? null) === '1',
        ];
    }

    public function rules(string $type): array
    {
        $vcard = $type !== 'STORE';
        $store = $type !== 'VCARD';
        $limit = 'integer|min:0';

        $rules = [
            'plan_type'        => ['required', Rule::in(self::TYPES)],
            'plan_name'        => 'required|string|max:191',
            'plan_description' => 'required|string|max:1000',
            'plan_price'       => 'required|numeric|min:0|max:9999999',
            'validity'         => 'required|integer|min:1|max:' . self::FOREVER,
            'trial_period'     => 'required|integer|min:0|max:3650',
            'storage'          => 'required|integer|min:0',
            'recommended'      => 'boolean',
            'is_private'       => 'boolean',
        ];

        foreach (self::COMMON_FEATURES as $field) {
            $rules[$field] = 'boolean';
        }

        if ($vcard) {
            foreach (self::VCARD_LIMITS as $field) {
                $rules[$field] = "required|{$limit}";
            }
            foreach (self::VCARD_FEATURES as $field) {
                $rules[$field] = 'boolean';
            }
            $rules['ai_credits']           = 'required|integer|min:-1';
            $rules['trial_ai_credits']     = 'required|integer|min:0';
            $rules['google_wallet']        = 'boolean';
            $rules['no_of_google_wallets'] = "nullable|{$limit}";
        }

        if ($store) {
            foreach (self::STORE_LIMITS as $field) {
                $rules[$field] = "required|{$limit}";
            }
        }

        foreach (array_keys(self::INTEGRATIONS) as $field) {
            $rules[$field] = 'nullable|string|max:255';
        }
        $rules['in_app_purchase_type'] = 'nullable|in:one_time,recurring';

        return $rules;
    }

    /** Readable field names for validation messages. */
    public function attributes(): array
    {
        return [
            'plan_type'            => __('plan type'),
            'plan_name'            => __('name'),
            'plan_description'     => __('description'),
            'plan_price'           => __('price'),
            'trial_period'         => __('free trial'),
            'no_of_vcards'         => __('business cards'),
            'no_of_services'       => __('services'),
            'no_of_vcard_products' => __('products'),
            'no_of_links'          => __('links'),
            'no_of_payments'       => __('payment methods'),
            'no_testimonials'      => __('testimonials'),
            'no_of_galleries'      => __('gallery images'),
            'no_of_enquires'       => __('enquiries'),
            'no_of_stores'         => __('stores'),
            'no_of_categories'     => __('categories'),
            'no_of_store_products' => __('store products'),
            'ai_credits'           => __('AI credits'),
            'trial_ai_credits'     => __('AI credits during trial'),
            'no_of_google_wallets' => __('Google Wallet passes'),
            'storage'              => __('media storage'),
        ];
    }

    /**
     * Write validated input to a plan. Fields of the other plan type are left
     * untouched (same as before), plugin fields only when the plugin exists.
     */
    public function fill(Plan $plan, array $data): Plan
    {
        $type = $data['plan_type'];

        $plan->plan_type        = $type;
        $plan->plan_name        = ucfirst($data['plan_name']);
        $plan->plan_description = ucfirst($data['plan_description']);
        $plan->plan_price       = $data['plan_price'];
        $plan->validity         = (float) $data['plan_price'] == 0 ? self::FOREVER : $data['validity'];
        $plan->trial            = $data['trial_period'];
        $plan->storage          = $data['storage'];
        $plan->recommended      = (int) ($data['recommended'] ?? false);
        $plan->is_private       = (int) ($data['is_private'] ?? false);
        $plan->additional_tools = 0;

        foreach (self::COMMON_FEATURES as $field) {
            $plan->{$field} = (int) ($data[$field] ?? false);
        }

        if ($type !== 'STORE') {
            foreach (self::VCARD_LIMITS as $field) {
                $plan->{$field} = $data[$field];
            }
            foreach (self::VCARD_FEATURES as $field) {
                $plan->{$field} = (int) ($data[$field] ?? false);
            }
            $plan->ai_credits       = $data['ai_credits'];
            $plan->trial_ai_credits = $data['trial_ai_credits'];

            if ($this->plugins()['googleWallet']) {
                $plan->google_wallet        = (int) ($data['google_wallet'] ?? false);
                $plan->no_of_google_wallets = (int) ($data['no_of_google_wallets'] ?? 0);
            }
        }

        if ($type !== 'VCARD') {
            foreach (self::STORE_LIMITS as $field) {
                $plan->{$field} = $data[$field];
            }
        }

        foreach (self::INTEGRATIONS as $field => $plugin) {
            if (plugin_active($plugin)) {
                $plan->{$field} = $data[$field] ?? null;
            }
        }

        return $plan;
    }

    /** Customers whose current plan is this one and not expired, per plan. */
    public function activeSubscribers(): array
    {
        return DB::table('users')
            ->where('role_id', 2)
            ->where('status', 1)
            ->whereNotNull('plan_id')
            ->where('plan_validity', '>=', now())
            ->selectRaw('plan_id, COUNT(*) as total')
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id')
            ->map(fn($n) => (int) $n)
            ->all();
    }

    /** Plan as the React pages need it. */
    public function present(Plan $plan, int $subscribers = 0): array
    {
        $out = [
            'id'             => $plan->plan_id,
            'type'           => $plan->plan_type,
            'name'           => $plan->plan_name,
            'description'    => $plan->plan_description,
            'price'          => (float) $plan->plan_price,
            'validity'       => (int) $plan->validity,
            'trial'          => (int) $plan->trial,
            'storage'        => (int) $plan->storage,
            'recommended'    => (bool) $plan->recommended,
            'private'        => (bool) $plan->is_private,
            'status'         => match ((int) $plan->status) {
                1       => 'active',
                2       => 'archived',
                default => 'disabled',
            },
            'subscribers'    => $subscribers,
            'aiCredits'      => (int) $plan->ai_credits,
            'trialAiCredits' => (int) $plan->trial_ai_credits,
            'googleWallet'   => (bool) $plan->google_wallet,
            'googleWallets'  => (int) $plan->no_of_google_wallets,
        ];

        foreach ([ ...self::VCARD_LIMITS, ...self::STORE_LIMITS] as $field) {
            $out['limits'][$field] = $plan->{$field} === null ? null : (int) $plan->{$field};
        }

        foreach ([ ...self::VCARD_FEATURES, ...self::COMMON_FEATURES] as $field) {
            $out['features'][$field] = (bool) $plan->{$field};
        }

        foreach (array_keys(self::INTEGRATIONS) as $field) {
            $out['integrations'][$field] = $plan->{$field};
        }

        return $out;
    }
}
