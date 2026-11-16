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
     * Safe Zapier Webhook Dispatcher
     */
    function zapier_dispatch(string $event, array $payload)
    {
        $class = 'Plugins\Zapier\Helpers\ZapierHelper';

        // base_path points accurately to the project root directory
        $file = base_path('plugins/Zapier/Helpers/ZapierHelper.php');

        // Check if the file physically exists before triggering the autoloader
        if (plugin_active('Zapier') && file_exists($file) && class_exists($class)) {
            $class::dispatch($event, $payload);
        }
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
