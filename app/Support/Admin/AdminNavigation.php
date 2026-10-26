<?php

namespace App\Support\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Admin sidebar definition shared with the Inertia admin panel.
 *
 * Mirrors resources/views/admin/includes/sidebar.blade.php (same routes,
 * permission keys and feature conditions). While modules are migrated one
 * by one, items whose route is listed in INERTIA are rendered with Inertia
 * links; every other item is a full page load to its Blade page.
 */
class AdminNavigation
{
    /** Route names already served by the Inertia admin panel. */
    /** Items that open a dialog in the admin panel instead of a page. */
    private const ACTIONS = ['admin.clear.cache' => 'clear-cache'];

    public const INERTIA = [
        'admin.dashboard',
        'admin.payment.methods',
        'admin.plugins',
        'admin.account',
        'admin.settings',
        'admin.cron.jobs',
        'admin.sitemap',
        'admin.tax.setting',
        'admin.check',
        'admin.backups',
        'admin.currencies',
        'admin.users',
        'admin.email.templates.index',
        'translation-manager.index',
        'admin.web-templates',
        'admin.blogs',
        'admin.blog.categories',
        'admin.clear.cache',
        'admin.themes',
        'admin.business-card-intros',
        'admin.customers',
        'admin.created.cards',
        'admin.created.stores',
        'admin.custom.domain.requests',
        'admin.plans',
        'admin.designs',
        'admin.orders',
        'admin.key.generations',
        'admin.transactions',
        'admin.ai.credits.plans',
        'admin.ai.credits.transactions',
        'admin.referrals',
        'admin.referral.withdrawal.request',
        'admin.referral.system.configuration',
        'admin.coupons',
        'admin.marketing.campaigns',
        'admin.marketing.groups',
        'admin.marketing.mailgun',
        'admin.marketing.pusher.notification',
        'admin.marketing.pusher',
        'admin.transactions.index',
        'admin.in-app-purchases',
    ];

    /**
     * @param  array<string, bool>  $permissions
     * @param  array<string, bool>  $features
     * @return array<int, array<string, mixed>>
     */
    public static function for(User $user, Request $request, array $permissions, array $features): array
    {
        $can = fn (string $key) => $permissions[$key] ?? false;
        $hidesRevenue = AdminPermissions::hidesRevenue($user);

        $sections = [
            [
                'title' => __('Main Console'),
                'items' => [
                    self::item(__('Dashboard'), 'admin.dashboard', [], 'layout-dashboard', ['admin/dashboard']),
                ],
            ],
            [
                'title' => __('My Business'),
                'items' => [
                    self::group(__('Themes'), 'palette', ['admin/themes*', 'admin/edit-theme*', 'admin/business-card-intros*'], [
                        $can('themes') ? self::item(__('Manage Themes'), 'admin.themes', ['status' => 'all']) : null,
                        $can('business_card_intros') ? self::item(__('Business Card Intros'), 'admin.business-card-intros') : null,
                    ]),
                    self::group(__('Customers'), 'users', ['admin/customers*', 'admin/create-customer', 'admin/view-customer*', 'admin/edit-customer*', 'admin/created-cards', 'admin/created-stores', 'admin/custom-domain-requests*'], [
                        $can('customers') ? self::item(__('Manage Customers'), 'admin.customers', ['status' => 'all']) : null,
                        $can('customers') ? self::item(__('Digital Business Cards'), 'admin.created.cards') : null,
                        $can('customers') ? self::item(__('Stores'), 'admin.created.stores') : null,
                        $can('custom_domain') ? self::item(__('Custom Domains'), 'admin.custom.domain.requests', ['status' => 'all']) : null,
                    ]),
                    $can('plans') ? self::item(__('Plans'), 'admin.plans', [], 'package', ['admin/plans', 'admin/add-plan', 'admin/edit-plan/*']) : null,
                    $features['nfc'] ? self::group(__('NFC Sales'), 'nfc', ['admin/nfc-card-design', 'admin/nfc-card*', 'admin/nfc-card-order-transactions'], [
                        $can('nfc_card_design') ? self::item(__('NFC Cards'), 'admin.designs') : null,
                        $can('nfc_card_orders') ? self::item(__('Orders'), 'admin.orders') : null,
                        $can('nfc_card_key_generations') ? self::item(__('Activation Keys'), 'admin.key.generations') : null,
                        $can('nfc_card_order_transactions') ? self::item(__('Transactions'), 'admin.transactions') : null,
                    ]) : null,
                    $can('ai_credits') && $features['aibuilder'] ? self::group(__('AI Credit Plans'), 'sparkles', ['admin/ai-credits-plans', 'admin/ai-credits/create-plan', 'admin/ai-credits/edit-plan/*', 'admin/ai-credits/transactions*'], [
                        self::item(__('Plans'), 'admin.ai.credits.plans'),
                        self::item(__('Transactions'), 'admin.ai.credits.transactions'),
                    ]) : null,
                    self::group(__('Website Content'), 'globe', ['admin/web-templates*', 'admin/web-template*', 'admin/blogs*', 'admin/create-blog*', 'admin/edit-blog/*', 'admin/blog-categories*', 'admin/create-blog-category*', 'admin/edit-blog-category/*'], [
                        $can('web_templates') ? self::item(__('Web Templates'), 'admin.web-templates') : null,
                        $can('blogs') ? self::item(__('Blogs'), 'admin.blogs', [], null, ['admin/blogs*', 'admin/create-blog', 'admin/edit-blog/*']) : null,
                        $can('blogs') ? self::item(__('Blog Categories'), 'admin.blog.categories') : null,
                    ]),
                    $features['referral'] && $can('referral_system') ? self::group(__('Referral Program'), 'gift', ['admin/referrals*', 'admin/referral-withdrawal-requests*', 'admin/referral-system-configuration*'], [
                        self::item(__('Referrals'), 'admin.referrals'),
                        $hidesRevenue ? null : self::item(__('Withdrawal Requests'), 'admin.referral.withdrawal.request'),
                        $hidesRevenue ? null : self::item(__('Configuration'), 'admin.referral.system.configuration'),
                    ]) : null,
                ],
            ],
            [
                'title' => __('Marketing'),
                'items' => [
                    $can('coupons') ? self::item(__('Coupons'), 'admin.coupons', [], 'ticket-percent', ['admin/coupons*', 'admin/create-coupon', 'admin/edit-coupon/*', 'admin/statistics-coupon*']) : null,
                    $can('marketing') ? self::group(__('Email Marketing'), 'mail', ['admin/marketing/campaigns*', 'admin/marketing/groups*', 'admin/marketing/mailgun*'], [
                        self::item(__('Campaigns'), 'admin.marketing.campaigns'),
                        self::item(__('Contact groups'), 'admin.marketing.groups'),
                        self::item(__('Mailgun settings'), 'admin.marketing.mailgun'),
                    ]) : null,
                    $can('marketing') ? self::group(__('Web Notifications'), 'bell', ['admin/marketing/pusher-notification*', 'admin/marketing/pusher'], [
                        self::item(__('Send notification'), 'admin.marketing.pusher.notification'),
                        self::item(__('Pusher settings'), 'admin.marketing.pusher'),
                    ]) : null,
                ],
            ],
            [
                'title' => __('Sales & Payments'),
                'items' => [
                    $can('transactions') ? self::item(__('Transactions'), 'admin.transactions.index', [], 'arrow-left-right', ['admin/transactions*', 'admin/view-invoice*']) : null,
                    $can('in_app_purchases') && $features['mobile_app_api'] ? self::item(__('In App Purchases'), 'admin.in-app-purchases', [], 'smartphone', ['admin/in-app-purchases']) : null,
                    $can('payment_methods') ? self::item(__('Payment Gateways'), 'admin.payment.methods', [], 'wallet', ['admin/payment-methods', 'admin/configure-payment-method*', 'admin/edit-payment-method/*', 'admin/delete-payment-method']) : null,
                ],
            ],
            [
                'title' => __('Settings & Tools'),
                'items' => [
                    $can('plugins') ? self::item(__('Plugins'), 'admin.plugins', [], 'puzzle', ['admin/plugins', 'admin/plugin*']) : null,
                    self::group(__('System Utilities'), 'settings', ['admin/settings*', 'admin/cron*', 'admin/sitemap*', 'admin/currencies*', 'admin/create-currency', 'admin/edit-currency*', 'admin/users*', 'admin/create-user', 'admin/edit-user*', 'admin/email-templates*', 'admin/translations*', 'admin/backups*', 'admin/clear-cache*', 'admin/software-update*'], [
                        $can('general_settings') ? self::item(__('General Settings'), 'admin.settings') : null,
                        $can('general_settings') ? self::item(__('Cron Jobs'), 'admin.cron.jobs') : null,
                        $can('sitemap') ? self::item(__('Generate Sitemap'), 'admin.sitemap') : null,
                        $can('invoice_tax') ? self::item(__('Invoice & Tax Settings'), 'admin.tax.setting') : null,
                        $can('currencies') ? self::item(__('Currencies'), 'admin.currencies') : null,
                        $can('users') ? self::item(__('Administrators & Staff'), 'admin.users') : null,
                        $can('email_templates') ? self::item(__('Email Templates'), 'admin.email.templates.index', ['id' => '584922675196'], null, ['admin/email-templates*']) : null,
                        $can('translations') ? self::item(__('System Translations'), 'translation-manager.index', [], null, ['admin/translations*']) : null,
                        $can('backup') ? self::item(__('System Backups'), 'admin.backups') : null,
                        $can('general_settings') ? self::item(__('Clear App Cache'), 'admin.clear.cache') : null,
                        $can('software_update') ? self::item(__('Software Updates'), 'admin.check') : null,
                    ]),
                ],
            ],
            [
                'title' => __('Personal'),
                'items' => [
                    self::item(__('My Account'), 'admin.account', [], 'user-round', ['admin/account', 'admin/edit-account', 'admin/change-password']),
                ],
            ],
        ];

        return self::resolve($sections, $request);
    }

    private static function item(string $title, string $route, array $params = [], ?string $icon = null, array $active = []): array
    {
        return compact('title', 'route', 'params', 'icon', 'active');
    }

    private static function group(string $title, string $icon, array $active, array $children): array
    {
        return compact('title', 'icon', 'active', 'children');
    }

    /**
     * Drop hidden/unknown entries and empty groups or sections, then turn
     * route names into URLs and flag the active entries.
     */
    private static function resolve(array $sections, Request $request): array
    {
        $resolveItem = function (?array $item) use ($request): ?array {
            if ($item === null || ! Route::has($item['route'])) {
                return null;
            }

            $url = route($item['route'], $item['params']);

            return [
                'title'    => $item['title'],
                'href'     => $url,
                'icon'     => $item['icon'],
                'inertia'  => in_array($item['route'], self::INERTIA, true),
                'action'   => self::ACTIONS[$item['route']] ?? null,
                'isActive' => $item['active']
                    ? $request->is(...$item['active'])
                    : rtrim($request->url(), '/') === rtrim(strtok($url, '?'), '/'),
            ];
        };

        $out = [];

        foreach ($sections as $section) {
            $items = [];

            foreach ($section['items'] as $entry) {
                if ($entry === null) {
                    continue;
                }

                if (isset($entry['children'])) {
                    $children = array_values(array_filter(array_map($resolveItem, $entry['children'])));

                    if ($children) {
                        $items[] = [
                            'title'    => $entry['title'],
                            'icon'     => $entry['icon'],
                            'isActive' => $request->is(...$entry['active']),
                            'items'    => $children,
                        ];
                    }

                    continue;
                }

                if ($resolved = $resolveItem($entry)) {
                    $items[] = $resolved;
                }
            }

            if ($items) {
                $out[] = ['title' => $section['title'], 'items' => $items];
            }
        }

        return $out;
    }
}
