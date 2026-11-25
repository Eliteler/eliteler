import type { CurrencyMap, CurrencySettings } from "@admin/types";

// Group digits Indian style (12,34,567).
function indianGroup(integer: string, separator: string) {
    if (integer.length <= 3) return integer;
    const last3 = integer.slice(-3);
    const rest = integer.slice(0, -3).replace(/\B(?=(\d{2})+(?!\d))/g, separator);
    return `${rest}${separator}${last3}`;
}

function groupNumber(value: number, decimals: number, decimalMark: string, thousands: string, indian = false) {
    const negative = value < 0;
    const [integer, fraction] = Math.abs(value).toFixed(decimals).split(".");
    const grouped = indian ? indianGroup(integer, thousands) : integer.replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
    return `${negative ? "-" : ""}${grouped}${fraction ? decimalMark + fraction : ""}`;
}

/**
 * Mirrors formatCurrencyBase() in app/helpers.php so amounts look the same
 * in Blade and React.
 */
export function formatCurrency(amount: number, currency: CurrencySettings) {
    const value = Number(amount) || 0;
    const { decimals } = currency;

    const formatted = (() => {
        switch (currency.format) {
            case "12,34,567.89":
                return groupNumber(value, decimals, ".", ",", true);
            case "1.234.567,89":
                return groupNumber(value, decimals, ",", ".");
            case "1 234 567,89":
                return groupNumber(value, decimals, ",", " ");
            case "1'234'567.89":
                return groupNumber(value, decimals, ".", "'");
            default:
                return groupNumber(value, decimals, ".", ",");
        }
    })();

    return currency.symbolFirst ? `${currency.symbol}${formatted}` : `${formatted}${currency.symbol}`;
}

/**
 * A recorded payment in the currency it was paid in (mirrors formatMoney()
 * in app/helpers.php). Number format and decimals follow the admin settings;
 * only the symbol changes. Unknown or empty codes use the base currency.
 * formatCurrency() is for current prices only.
 */
export function formatMoney(amount: number, code: string | null | undefined, base: CurrencySettings, currencies: CurrencyMap = {}) {
    const iso = (code ?? "").trim().toUpperCase();
    const match = iso ? currencies[iso] : undefined;

    return formatCurrency(amount, match ? { ...base, code: iso, ...match } : base);
}

export function formatNumber(value: number) {
    return new Intl.NumberFormat().format(Number(value) || 0);
}

/** Compact axis ticks: 1.2K, 3.4M. */
export function formatCompact(value: number) {
    return new Intl.NumberFormat(undefined, { notation: "compact", maximumFractionDigits: 1 }).format(Number(value) || 0);
}
