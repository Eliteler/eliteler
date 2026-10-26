<?php

namespace App\Support\Admin;

use App\User;
use Illuminate\Support\Facades\Auth;

class AdminPermissions
{
    /**
     * Every permission a staff account can have, grouped for the staff form.
     * Keys match the user.page.permission:<key> route middleware.
     */
    public const GROUPS = [
        'Customers and sales' => [
            'customers'        => 'Customers',
            'plans'            => 'Plans',
            'transactions'     => 'Transactions',
            'payment_methods'  => 'Payment gateways',
            'coupons'          => 'Coupons',
            'currencies'       => 'Currencies',
            'in_app_purchases' => 'In-app purchases',
            'ai_credits'       => 'AI credits',
            'referral_system'  => 'Referral program',
            'custom_domain'    => 'Custom domains',
        ],
        'Cards and content' => [
            'themes'               => 'Themes',
            'business_card_intros' => 'Business card intros',
            'vcard_store'          => 'Admin business cards and stores',
            'web_templates'        => 'Website templates',
            'blogs'                => 'Blogs',
            'email_templates'      => 'Email templates',
            'marketing'            => 'Marketing',
        ],
        'NFC cards' => [
            'nfc_card_design'             => 'NFC card designs',
            'nfc_card_orders'             => 'NFC card orders',
            'nfc_card_order_transactions' => 'NFC card payments',
            'nfc_card_key_generations'    => 'NFC activation keys',
        ],
        'System' => [
            'general_settings' => 'General settings and cron jobs',
            'invoice_tax'      => 'Invoice and tax',
            'users'            => 'Administrators and staff',
            'translations'     => 'Translations',
            'sitemap'          => 'Sitemap',
            'backup'           => 'Backups',
            'plugins'          => 'Plugins',
            'software_update'  => 'Software updates',
            'maintenance_mode' => 'Maintenance mode',
            'demo_mode'        => 'Demo mode',
        ],
    ];

    /**
     * Restriction stored in the same users.permissions JSON. Kept out of
     * GROUPS because it takes something away instead of granting access.
     */
    public const HIDE_REVENUE = 'hide_revenue';

    /**
     * Areas that are about money (payments, or setting prices). They are
     * switched off for admins with revenue hidden, whatever is stored.
     */
    public const REVENUE_AREAS = [
        'transactions',
        'payment_methods',
        'currencies',
        'invoice_tax',
        'plans',
        'coupons',
        'in_app_purchases',
        'ai_credits',
        'nfc_card_design',
        'nfc_card_order_transactions',
    ];

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(array_merge(...array_values(self::GROUPS)));
    }

    /**
     * Permission keys every admin gets unless explicitly turned off.
     * Mirrors the defaults in resources/views/admin/includes/sidebar.blade.php.
     */
    public const DEFAULTS = [
        'currencies'                  => 1,
        'coupons'                     => 1,
        'custom_domain'               => 1,
        'marketing'                   => 1,
        'maintenance_mode'            => 1,
        'demo_mode'                   => 1,
        'backup'                      => 1,
        'nfc_card_design'             => 1,
        'nfc_card_orders'             => 1,
        'nfc_card_order_transactions' => 1,
        'nfc_card_key_generations'    => 1,
        'email_templates'             => 1,
        'plugins'                     => 1,
        'referral_system'             => 1,
        'in_app_purchases'            => 1,
        'vcard_store'                 => 1,
        'business_card_intros'        => 1,
        'ai_credits'                  => 1,
    ];

    /**
     * Resolve the effective permissions of an admin user.
     *
     * Read-only: unlike the Blade sidebar, this never writes the merged
     * result back to users.permissions.
     *
     * @return array<string, bool>
     */
    public static function for(User $user): array
    {
        $stored = json_decode($user->permissions ?? '', true);
        $merged = array_merge(self::DEFAULTS, is_array($stored) ? $stored : []);

        $merged[self::HIDE_REVENUE] = self::hidesRevenue($user);
        if ($merged[self::HIDE_REVENUE]) {
            $merged = array_merge($merged, array_fill_keys(self::REVENUE_AREAS, 0));
        }

        return array_map(fn ($value) => (bool) (int) $value, $merged);
    }

    /**
     * Whether revenue and payment figures are hidden from this admin.
     * Never hidden for the super admin.
     */
    public static function hidesRevenue(User $user): bool
    {
        if ((int) $user->role_id === 1) {
            return false;
        }

        $stored = json_decode($user->permissions ?? '', true);

        return is_array($stored) && (bool) (int) ($stored[self::HIDE_REVENUE] ?? 0);
    }

    /**
     * An amount as the signed-in admin may see it: null when revenue is hidden.
     */
    public static function amount(float $value): ?float
    {
        $user = Auth::user();

        return $user && self::hidesRevenue($user) ? null : $value;
    }

    /**
     * A plan price as the signed-in admin may see it. Like amount(), but a
     * zero price stays zero so free plans can still say "Free".
     */
    public static function price(float $value): ?float
    {
        return $value == 0 ? 0.0 : self::amount($value);
    }
}
