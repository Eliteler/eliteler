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
        [$int, $dec] = array_pad(explode('.', $amount), 2, '00');

        $last3 = substr($int, -3);
        $rest = substr($int, 0, -3);

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            return $rest . ',' . $last3 . '.' . $dec;
        }

        return $last3 . '.' . $dec;
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
