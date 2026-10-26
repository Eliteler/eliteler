import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { formatCurrency } from "@admin/lib/format";

import type { Gateway } from "../types";

const TOP = 6;

export function GatewaysCard({ gateways }: { gateways: Gateway[] }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const total = gateways.reduce((sum, row) => sum + row.revenue, 0);

    const rows = gateways.slice(0, TOP);
    const rest = gateways.slice(TOP);
    if (rest.length) {
        rows.push({
            name: t("Others"),
            revenue: rest.reduce((sum, row) => sum + row.revenue, 0),
            payments: rest.reduce((sum, row) => sum + row.payments, 0),
        });
    }

    return (
        <Card className="gap-4">
            <CardHeader>
                <CardTitle>{t("Payment gateways")}</CardTitle>
                <CardDescription>{t("Plan revenue by gateway")}</CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                {rows.length === 0 && <p className="py-6 text-center text-sm text-muted-foreground">{t("No payments in this period")}</p>}
                {rows.map((row) => {
                    const share = total ? (row.revenue / total) * 100 : 0;
                    return (
                        <div key={row.name} className="relative flex items-center justify-between gap-2 overflow-hidden rounded-md px-3 py-2 text-sm">
                            <div className="absolute inset-y-0 start-0 rounded-md bg-primary/10" style={{ width: `${share}%` }} />
                            <span className="relative truncate font-medium">{row.name}</span>
                            <span className="relative flex shrink-0 items-baseline gap-2 tabular-nums">
                                <span className="text-xs text-muted-foreground">
                                    {row.payments} {t("payments")}
                                </span>
                                <span className="font-medium">{formatCurrency(row.revenue, currency)}</span>
                            </span>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
