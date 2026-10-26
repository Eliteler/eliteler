<?php

namespace App\Support\Admin;

use App\AibuilderSetting;
use App\Currency;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tighten\Ziggy\Ziggy;

/**
 * Inertia shared props for the admin panel (requests under /admin).
 */
class AdminSharedProps
{
    /**
     * Admin accent colour. Single source for now; this is where the upcoming
     * per-admin colour preference will plug in. Must match a
     * [data-accent="..."] block in resources/css/admin.css.
     */
    public const ACCENT = 'blue';

    /** Version of the plugin page contract (resources/js/admin/plugin-runtime.ts PLUGIN_API). */
    public const PLUGIN_API = '1';

    public const RTL_LOCALES = ['ar', 'ur', 'he'];

    public static function for(Request $request): array
    {
        $user     = $request->user();
        $settings = Setting::first();
        $config   = DB::table('config')->pluck('config_value', 'config_key');

        $features = self::features($config);

        $permissions = $user ? AdminPermissions::for($user) : [];

        return [
            'app' => [
                'name'      => $config['site_name'] ?? config('app.name'),
                'version'   => $config['app_version'] ?? null,
                'logo'      => asset($settings->site_logo),
                // Always saved as img/logo-light.<ext>, so add the file time to bust browser caches
                'logo_dark' => ($light = collect(glob(public_path('img/logo-light.*')) ?: [])->first()) ? asset('img/' . basename($light)) . '?v=' . filemtime($light) : asset($settings->site_logo),
                'favicon'   => asset($settings->favicon),
            ],
            'ui' => [
                'accent'     => self::ACCENT,
                'appearance' => self::appearance($user),
            ],
            'auth' => [
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'avatar' => asset($user->profile_image ?: 'profile.png'),
                    'role'   => (int) $user->role_id === 4 ? __('Manager') : __('Administrator'),
                ] : null,
            ],
            'permissions' => $permissions,
            'features'    => $features,
            'navigation'  => $user ? AdminNavigation::for($user, $request, $permissions, $features) : [],
            'currency'    => self::currency($config),
            'locale'      => self::locale(),
            'flash'       => [
                'success'                => fn () => $request->session()->get('success'),
                'failed'                 => fn () => $request->session()->get('failed'),
                // Why an uploaded plugin wasn't installed (old Blade version, or older than installed)
                'plugin_notice'          => fn () => $request->session()->get('plugin_notice'),
                'template_notice'        => fn () => $request->session()->get('template_notice'),
                'stock_message'          => fn () => $request->session()->get('stock_message'),
                'stock_url'              => fn () => $request->session()->get('stock_url'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Optional modules that are switched on (same conditions as the Blade sidebar).
     *
     * @return array<string, bool>
     */
    public static function features($config = null): array
    {
        $config ??= DB::table('config')->pluck('config_value', 'config_key');

        return [
            'nfc'            => ($config['nfc_order_system'] ?? '0') == '1',
            'referral'       => ($config['referral_system'] ?? '0') == '1',
            'aibuilder'      => optional(AibuilderSetting::first())->aibuilder == 1,
            'mobile_app_api' => plugin_active('MobileAppAPI'),
        ];
    }

    /** Same rule as the Blade layout: anything other than "light" is dark. */
    public static function appearance($user): string
    {
        return optional($user)->choosed_theme === 'light' ? 'light' : 'dark';
    }

    public static function isRtl(): bool
    {
        return in_array(app()->getLocale(), self::RTL_LOCALES, true);
    }

    private static function locale(): array
    {
        $flags = config('app.flag-icons', []);

        return [
            'current'   => app()->getLocale(),
            'dir'       => self::isRtl() ? 'rtl' : 'ltr',
            'languages' => collect(config('app.languages', []))
                ->map(fn ($name, $code) => ['code' => $code, 'name' => $name, 'flag' => $flags[$code] ?? null])
                ->values()
                ->all(),
        ];
    }

    /** Formatting rules used by formatCurrencyBase() in app/helpers.php. */
    private static function currency($config): array
    {
        $code     = $config['currency'] ?? 'USD';
        $currency = Currency::where('iso_code', $code)->first();

        return [
            'code'        => $code,
            'symbol'      => $currency->symbol ?? '',
            'symbolFirst' => ($currency->symbol_first ?? 'true') !== 'false',
            'decimals'    => (int) ($config['currency_decimals_place'] ?? 2),
            'format'      => $config['currency_format_type'] ?? '1,234,567.89',
        ];
    }
}
