import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { formatCurrency, formatNumber } from "@admin/lib/format";

import type { PlanMixRow } from "../types";

export function PlanMixCard({ rows, plansUrl }: { rows: PlanMixRow[]; plansUrl: string | null }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const total = rows.reduce((sum, row) => sum + row.customers, 0);

    return (
        <Card className="gap-4">
            <CardHeader>
                <CardTitle>{t("Plan mix")}</CardTitle>
                <CardDescription>
                    {formatNumber(total)} {t("customers with an active plan")}
                </CardDescription>
                {plansUrl && (
                    <CardAction>
                        <Button variant="secondary" size="sm" asChild>
                            <a href={plansUrl}>{t("Manage")}</a>
                        </Button>
                    </CardAction>
                )}
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                {rows.length === 0 && <p className="py-6 text-center text-sm text-muted-foreground">{t("No active plans yet")}</p>}
                {rows.map((row) => {
                    const share = total ? Math.round((row.customers / total) * 100) : 0;
                    return (
                        <div key={row.name} className="flex flex-col gap-1.5">
                            <div className="flex items-baseline justify-between gap-2 text-sm">
                                <span className="truncate font-medium">
                                    {row.name}
                                    {row.price !== undefined && (
                                        <span className="ms-2 text-xs font-normal text-muted-foreground">
                                            {row.price > 0 ? formatCurrency(row.price, currency) : t("Free")}
                                        </span>
                                    )}
                                </span>
                                <span className="shrink-0 text-muted-foreground tabular-nums">
                                    {formatNumber(row.customers)} · {share}%
                                </span>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    className={row.paid ? "h-full rounded-full bg-primary" : "h-full rounded-full bg-muted-foreground/40"}
                                    style={{ width: `${share}%` }}
                                />
                            </div>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
