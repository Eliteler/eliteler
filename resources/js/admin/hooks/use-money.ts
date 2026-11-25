import { usePage } from "@inertiajs/react";
import { formatMoney } from "@admin/lib/format";

/**
 * Formatter for recorded payments: money(amount, row.currency) shows the
 * amount in the currency it was paid in, so old sales keep their symbol
 * after the admin changes the base currency. Leave the code out (or pass
 * null) for the base currency.
 */
export function useMoney() {
    const { currency, currencySymbols } = usePage().props;

    return (amount: number, code?: string | null) => formatMoney(amount, code, currency, currencySymbols ?? {});
}
