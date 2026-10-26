import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { Card, CardAction, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { formatCurrency, formatNumber } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import { change, type Kpis } from "../types";
import { TrendBadge } from "./trend-badge";

export function KpiCards({ kpis, periodLabel }: { kpis: Kpis; periodLabel: string }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const { paid, free, total } = kpis.activeSubscribers;

    const cards = [
        // No revenue card at all when revenue is hidden for this admin
        ...(kpis.revenue
            ? [
                  {
                      title: t("Revenue"),
                      value: formatCurrency(kpis.revenue.current, currency),
                      trend: change(kpis.revenue),
                      note: t("Plans, NFC cards and AI credits"),
                      sub: `${t("Previous period")}: ${formatCurrency(kpis.revenue.previous, currency)}`,
                  },
              ]
            : []),
        {
            title: t("New customers"),
            value: formatNumber(kpis.signups.current),
            trend: change(kpis.signups),
            note: t("Sign-ups in this period"),
            sub: `${t("Previous period")}: ${formatNumber(kpis.signups.previous)}`,
        },
        {
            title: t("Paid subscriptions"),
            value: formatNumber(kpis.subscriptions.current),
            trend: change(kpis.subscriptions),
            note: t("New purchases and renewals"),
            sub: `${t("Previous period")}: ${formatNumber(kpis.subscriptions.previous)}`,
        },
        {
            title: t("Active subscribers"),
            value: formatNumber(paid),
            trend: null,
            note: t("On a paid plan right now"),
            sub: `${formatNumber(free)} ${t("on free plans")} · ${formatNumber(total)} ${t("total")}`,
        },
    ];

    return (
        <div className={cn("grid grid-cols-1 gap-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs sm:grid-cols-2 dark:*:data-[slot=card]:bg-card", cards.length === 3 ? "lg:grid-cols-3" : "xl:grid-cols-4")}>
            {cards.map((card) => (
                <Card key={card.title} className="@container/card gap-4">
                    <CardHeader>
                        <CardDescription>{card.title}</CardDescription>
                        <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">{card.value}</CardTitle>
                        {card.trend !== null && (
                            <CardAction>
                                <TrendBadge value={card.trend} />
                            </CardAction>
                        )}
                    </CardHeader>
                    <CardFooter className="flex-col items-start gap-1 text-sm">
                        <div className="font-medium">{card.note}</div>
                        <div className="text-muted-foreground">
                            {card.trend !== null ? `${card.sub} (${periodLabel})` : card.sub}
                        </div>
                    </CardFooter>
                </Card>
            ))}
        </div>
    );
}
