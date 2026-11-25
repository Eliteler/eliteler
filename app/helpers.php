<?php

use App\Currency;
use Illuminate\Support\Facades\DB;
use App\Services\GoBizCommonService;

/*
|--------------------------------------------------------------------------
| Currency Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('formatCurrencyBase')) {
    function formatCurrencyBase($amount, $currencyCode, $matchBy = 'iso_code')
    {
        static $config, $currencies;

        $config ??= GoBizCommonService::config()->pluck('config_value', 'config_key');
        $currencies ??= GoBizCommonService::currencies();

        $formatType = $config['currency_format_type'] ?? '1,234,567.89';
        $decimals = (int) ($config['currency_decimals_place'] ?? 2);

        $currency = $currencies->firstWhere($matchBy, $currencyCode);

        $symbol = $currency->symbol ?? '';
        $symbolFirst = ($currency->symbol_first ?? 'true') !== 'false';

        $formatted = match ($formatType) {
            '1,234,567.89' => number_format($amount, $decimals, '.', ','),
            '12,34,567.89' => formatIndianNumber($amount, $decimals),
            '1.234.567,89' => number_format($amount, $decimals, ',', '.'),
            '1 234 567,89' => number_format($amount, $decimals, ',', ' '),
            "1'234'567.89" => number_format($amount, $decimals, '.', "'"),
            default => number_format($amount, $decimals, '.', ','),
        };

        return $symbolFirst ? $symbol . $formatted : $formatted . $symbol;
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        static $currencyCode;

        $currencyCode ??= GoBizCommonService::singleConfig('currency');

        return formatCurrencyBase($amount, $currencyCode, 'iso_code');
    }
}

if (!function_exists('formatMoney')) {
    /**
     * Format a recorded payment in the currency it was paid in.
     *
     * Use this for anything already paid (transactions, orders, invoices,
     * referral earnings). formatCurrency() is for current prices only: it
     * always uses today's base currency, so old payments would change
     * currency when the admin switches it. Falls back to the base currency
     * when the stored code is empty or unknown.
     */
    function formatMoney($amount, ?string $currencyCode = null)
    {
        // currencies() runs a query on every call, so look the codes up once per request
        static $known;
        $known ??= GoBizCommonService::currencies()->pluck('iso_code')->flip();

        $code = strtoupper(trim((string) $currencyCode));

        if ($code === '' || ! isset($known[$code])) {
            return formatCurrency($amount);
        }

        return formatCurrencyBase((float) $amount, $code, 'iso_code');
    }
}

if (!function_exists('formatCurrencyVcard')) {
    function formatCurrencyVcard($amount, $currencyCode = 'USD')
    {
        return formatCurrencyBase($amount, $currencyCode, 'iso_code');
    }
}

if (!function_exists('formatCurrencyCard')) {
    function formatCurrencyCard($amount, $currencyCode = 'USD')
    {
        return formatCurrencyBase($amount, $currencyCode, 'symbol');
    }
}

/*
|--------------------------------------------------------------------------
| Indian Number Format
|--------------------------------------------------------------------------
*/

if (!function_exists('formatIndianNumber')) {
    function formatIndianNumber($amount, $decimals = 2)
    {
        $amount = number_format($amount, $decimals, '.', '');
        [$int, $dec] = array_pad(explode('.', $amount), 2, '');

        $last3 = substr($int, -3);
        $rest = substr($int, 0, -3);
        $fraction = $decimals > 0 ? '.' . $dec : '';

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            return $rest . ',' . $last3 . $fraction;
        }

        return $last3 . $fraction;
    }
}

/*
|--------------------------------------------------------------------------
| Config Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('getConfigData')) {
    function getConfigData($key)
    {
        return GoBizCommonService::singleConfig($key);
    }
}

// Whether a plugin is installed and may run (legacy Blade versions stay idle)
if (!function_exists('plugin_active')) {
    function plugin_active(string $id): bool
    {
        return app(\App\Services\PluginManager::class)->isActive($id);
    }
}

// Zapier Helper
if (!function_exists('zapier_dispatch')) {
    /**
     * Safe Zapier Webhook Dispatcher (kept for existing callers and plugins).
     * New code: App\Support\Zapier, which builds the payload too.
     */
    function zapier_dispatch(string $event, array $payload)
    {
        \App\Support\Zapier::send($event, $payload);
    }
}

// Whether the user's subscribed plan (users.plan_details snapshot) includes visitor analytics.
// Snapshots taken before the feature existed have no key and read as false.
if (!function_exists('plan_has_analytics')) {
    function plan_has_analytics($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user || empty($user->plan_details)) {
            return false;
        }

        $planDetails = is_string($user->plan_details)
            ? json_decode($user->plan_details, true)
            : (array) $user->plan_details;

        return !empty($planDetails['advanced_analytics']);
    }
}

// Flag emoji for a two-letter country code ("IN" -> 🇮🇳); empty for anything else.
if (!function_exists('country_flag')) {
    function country_flag(?string $code): string
    {
        if (!$code || !preg_match('/^[A-Za-z]{2}$/', $code)) {
            return '';
        }

        return implode('', array_map(fn($char) => mb_chr(0x1F1E6 + ord($char) - 65), str_split(strtoupper($code))));
    }
}

// YouTube video ID from a stored value: a bare ID (existing cards), an "ID&t=30" leftover
// or any watch / youtu.be / shorts / embed / live URL. Channel, @handle and playlist URLs give null.
if (!function_exists('youtube_video_id')) {
    function youtube_video_id(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Bare ID, optionally followed by leftover query params ("ID&t=30", "ID?si=...")
        if (preg_match('/^([A-Za-z0-9_-]{11})(?:[&?#].*)?$/', $value, $match)) {
            return $match[1];
        }

        $url  = preg_match('#^https?://#i', $value) ? $value : 'https://' . $value;
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^(www|m|music)\./', '', $host);
        $path = (string) parse_url($url, PHP_URL_PATH);
        $id   = null;

        if ($host === 'youtu.be') {
            $id = explode('/', trim($path, '/'))[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            if (!empty($query['v']) && is_string($query['v'])) {
                $id = $query['v'];
            } elseif (preg_match('~^/(?:embed|shorts|live|v)/([^/?#]+)~', $path, $match)) {
                $id = $match[1];
            }
        }

        return $id !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }
}

// Cleans YouTube cover and video features before a card is rendered, without saving anything:
// IDs are extracted from old URL-shaped values, a cover without a video is hidden and a
// video feature holding a channel URL is shown as a link button instead of a broken embed.
if (!function_exists('normalize_card_youtube')) {
    function normalize_card_youtube($business_card_details, $feature_details)
    {
        if ($business_card_details && in_array($business_card_details->cover_type ?? null, ['youtube', 'youtube-ap'], true)) {
            $id = youtube_video_id($business_card_details->cover);

            if ($id) {
                $business_card_details->cover = $id;
            } else {
                $business_card_details->cover_type = 'none';
            }
        }

        if (!$feature_details instanceof \Illuminate\Support\Collection) {
            return $feature_details;
        }

        return $feature_details->filter(function ($feature) {
            if (($feature->type ?? null) !== 'youtube') {
                return true;
            }

            $id = youtube_video_id($feature->content);

            if ($id) {
                $feature->content = $id;
                return true;
            }

            if (preg_match('#^https?://#i', trim((string) $feature->content))) {
                $feature->type = 'url';
                return true;
            }

            return false;
        })->values();
    }
}

/*
|--------------------------------------------------------------------------
| Storage Format Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('formatStorageSize')) {
    /**
     * Converts storage size in MB to a human-readable format (MB, GB, TB).
     * Automatically adds a note when storage is unlimited (Max 100 GB).
     *
     * @param int|float|string|null $mb
     * @param bool $showNote
     * @return string
     */
    function formatStorageSize($mb, bool $showNote = true): string
    {
        // Detect unlimited values (-1, null, 'unlimited', or large values like 999999)
        $isUnlimited = $mb === -1 ||
            $mb === '-1' ||
            $mb === null ||
            strtolower((string) $mb) === 'unlimited' ||
            (is_numeric($mb) && (float) $mb >= 999999);

        if ($isUnlimited) {
            return $showNote ? __('Unlimited (Max: 100 GB)') : __('Unlimited');
        }

        $mb = (float) $mb;

        if ($mb <= 0) {
            return '0 MB';
        }

        // Convert MB to TB (1 TB = 1048576 MB)
        if ($mb >= 1048576) {
            $tb = $mb / 1048576;
            return ($tb == (int) $tb ? (int) $tb : number_format($tb, 1)) . ' TB';
        }

        // Convert MB to GB (1 GB = 1024 MB)
        if ($mb >= 1024) {
            $gb = $mb / 1024;
            return ($gb == (int) $gb ? (int) $gb : number_format($gb, 1)) . ' GB';
        }

        // Return in MB
        return ($mb == (int) $mb ? (int) $mb : number_format($mb, 1)) . ' MB';
    }
}
