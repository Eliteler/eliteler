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
