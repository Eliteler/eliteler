import { useTranslation } from "react-i18next";

import { useMoney } from "@admin/hooks/use-money";
import { cn } from "@admin/lib/utils";

import type { CurrencyTotal } from "../types";

/**
 * "Also received: ₹4,921.02 · ₦722.80": payments in currencies other than
 * the base one. They are not converted or added to the revenue totals.
 */
export function OtherCurrencies({ others, className }: { others: CurrencyTotal[]; className?: string }) {
    const { t } = useTranslation();
    const money = useMoney();

    if (others.length === 0) return null;

    return (
        <div className={cn("text-xs text-muted-foreground", className)} title={t("Payments in other currencies are listed separately and are not converted.")}>
            {t("Also received")}:{" "}
            <span className="tabular-nums">{others.map((row) => money(row.total, row.code)).join(" · ")}</span>
        </div>
    );
}
