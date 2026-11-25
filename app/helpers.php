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

if (! function_exists('card_copyright_href')) {
    /**
     * Link target for the custom copyright line of a card:
     * copyright_url when set; otherwise the copyright text itself when it looks like
     * a domain or URL (e.g. "example.com"); otherwise the card's own URL.
     */
    function card_copyright_href($card): string
    {
        $url = trim((string) ($card->copyright_url ?? ''));

        if ($url === '') {
            $text = trim((string) ($card->copyright ?? ''));
            if ($text !== '' && ! preg_match('/\s/', $text)
                && preg_match('~^(https?://|www\.|[a-z0-9-]+(\.[a-z0-9-]+)+(/\S*)?$)~i', $text)) {
                $url = $text;
            }
        }

        if ($url === '') {
            return url()->current();
        }

        return preg_match('~^(https?://|mailto:|tel:)~i', $url) ? $url : 'https://' . $url;
    }
}

if (! function_exists('card_copyright_is_external')) {
    /** True when the copyright line links somewhere other than the card itself. */
    function card_copyright_is_external($card): bool
    {
        return card_copyright_href($card) !== url()->current();
    }
}

if (! function_exists('card_title_fonts')) {
    /** Fonts offered for titles (name => css class). Google Fonts, plus Arial / Times New Roman. */
    function card_title_fonts(): array
    {
        return [
            'Poppins' => 'poppins', 'Roboto' => 'roboto', 'Inter' => 'inter', 'Arial' => 'arial', 'Times New Roman' => 'times-new-roman',
            'League Spartan' => 'league-spartan', 'Rethink Sans' => 'rethink-sans', 'Outfit' => 'outfit', 'Figtree' => 'figtree',
            'Teachers' => 'teachers', 'Saira Stencil One' => 'saira-stencil-one', 'Gloock' => 'gloock', 'Pacifico' => 'pacifico',
            'Playfair Display' => 'playfair-display',
            // Arabic
            'Cairo' => 'cairo', 'Tajawal' => 'tajawal', 'Almarai' => 'almarai', 'Amiri' => 'amiri', 'Noto Kufi Arabic' => 'noto-kufi-arabic',
            'Noto Naskh Arabic' => 'noto-naskh-arabic', 'El Messiri' => 'el-messiri', 'Changa' => 'changa', 'Readex Pro' => 'readex-pro',
            'IBM Plex Sans Arabic' => 'ibm-plex-sans-arabic', 'Markazi Text' => 'markazi-text',
        ];
    }
}

if (! function_exists('card_google_font_url')) {
    function card_google_font_url(string $font): ?string
    {
        if (in_array($font, ['Arial', 'Times New Roman'], true)) {
            return null;
        }

        return 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font) . ':wght@400;500;600;700&display=swap';
    }
}

if (! function_exists('card_align_style')) {
    /**
     * Inline style for one of a card's titles: 'title', 'subtitle', 'title2' or 'subtitle2'.
     * Alignment: title_align (title + subtitle) / title2_align (title2 + subtitle2).
     * Font size: <which>_font_size. Font family (second titles only): title2_font.
     * Empty when the theme defaults apply.
     */
    function card_align_style($card, string $which = 'title'): string
    {
        $second = in_array($which, ['title2', 'subtitle2'], true);
        $style  = '';

        $align = $second ? ($card->title2_align ?? null) : ($card->title_align ?? null);
        if (in_array($align, ['left', 'center', 'right'], true)) {
            $style .= "text-align:{$align} !important;";
        }

        $sizeColumn = $which . '_font_size';
        $size = (int) ($card->{$sizeColumn} ?? 0);
        if ($size >= 8 && $size <= 120) {
            $style .= "font-size:{$size}px !important;";
        }

        if ($second) {
            $font = (string) ($card->title2_font ?? '');
            if ($font !== '' && array_key_exists($font, card_title_fonts())) {
                $style .= "font-family:'" . $font . "', sans-serif !important;";
            }
        }

        return $style;
    }
}

if (! function_exists('card_title2_font_link')) {
    /** <link> that loads the second-titles font, or '' when none / system font. */
    function card_title2_font_link($card): string
    {
        if (trim((string) ($card->title2 ?? '')) === '' && trim((string) ($card->subtitle2 ?? '')) === '') {
            return '';
        }
        $font = (string) ($card->title2_font ?? '');
        if ($font === '' || ! array_key_exists($font, card_title_fonts()) || ! ($url = card_google_font_url($font))) {
            return '';
        }

        return '<link rel="stylesheet" href="' . e($url) . '">';
    }
}

if (! function_exists('card_slider_delay')) {
    /**
     * Autoplay delay (milliseconds) of a card slider. $which: services | products | galleries | testimonials.
     * Uses custom_styles['slider_speed_<which>'] (seconds) when set, else the theme default.
     */
    function card_slider_delay($customStyles, string $which, int $defaultMs): int
    {
        $seconds = is_array($customStyles) ? ($customStyles['slider_speed_' . $which] ?? null) : null;

        return is_numeric($seconds) && $seconds >= 0.5 && $seconds <= 60 ? (int) round($seconds * 1000) : $defaultMs;
    }
}

if (! function_exists('card_public_url')) {
    /**
     * The address visitors use for a card or store (what its QR code points to):
     * subdomain when subdomains are enabled, else its custom domain, else /<card_url>.
     * Same rules as SubdomainController::dynamicCard, without the redirect hop.
     */
    function card_public_url($card): string
    {
        static $subdomains = null;

        $slug = (string) ($card->card_url ?? '');
        if ($slug === '') {
            return config('app.url') . route('dynamic.card', $card->card_id, false);
        }

        if ($subdomains === null) {
            $subdomains = (string) \Illuminate\Support\Facades\DB::table('config')->where('config_key', 'subdomain_enabled')->value('config_value') === '1';
        }

        if ($subdomains) {
            return route('subdomain.profile', $slug);
        }

        if (! empty($card->custom_domain)) {
            return 'https://' . $card->custom_domain;
        }

        return rtrim(config('app.url'), '/') . '/' . $slug;
    }
}

if (! function_exists('showcase_cards')) {
    /**
     * Live cards marked by the admin as examples for the website "Our Work" section.
     * Each item: title, subtitle, url, logo (or null), cover (or null).
     */
    function showcase_cards(int $limit = 12): array
    {
        static $cache = [];
        if (isset($cache[$limit])) {
            return $cache[$limit];
        }

        try {
            $rows = \Illuminate\Support\Facades\DB::table('business_cards')
                ->where('show_in_showcase', 1)
                ->where('card_type', 'vcard')
                ->where('card_status', 'activated')
                ->where('status', '1')
                ->orderByDesc('id')
                ->limit($limit)
                ->get(['card_id', 'card_url', 'custom_domain', 'title', 'sub_title', 'profile', 'cover', 'cover_type']);
        } catch (\Throwable $e) {
            return $cache[$limit] = [];
        }

        return $cache[$limit] = $rows->map(function ($card) {
            $asset = fn ($path) => $path ? (preg_match('~^https?://~i', $path) ? $path : url(ltrim($path, '/'))) : null;

            return (object) [
                'title'    => (string) $card->title,
                'subtitle' => trim(strip_tags((string) $card->sub_title)),
                'url'      => card_public_url($card),
                'logo'     => $asset($card->profile),
                'cover'    => ($card->cover_type ?? 'photo') === 'photo' ? $asset($card->cover) : null,
            ];
        })->all();
    }
}
