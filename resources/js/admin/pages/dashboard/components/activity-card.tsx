import { CalendarCheck, Contact, Eye, MessageSquareText, ShoppingBag, Store } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { formatNumber } from "@admin/lib/format";

import { change, type Usage } from "../types";
import { TrendBadge } from "./trend-badge";

const METRICS = [
    { key: "vcards", label: "Business cards created", icon: Contact },
    { key: "stores", label: "Stores created", icon: Store },
    { key: "visits", label: "Card & store visits", icon: Eye },
    { key: "enquiries", label: "Enquiries received", icon: MessageSquareText },
    { key: "storeOrders", label: "Store orders", icon: ShoppingBag },
    { key: "appointments", label: "Appointments booked", icon: CalendarCheck },
] as const;

/**
 * How much the customers' cards and stores are being used, a leading
 * indicator for retention.
 */
export function ActivityCard({ usage }: { usage: Usage }) {
    const { t } = useTranslation();

    return (
        <Card className="gap-4 lg:col-span-2">
            <CardHeader>
                <CardTitle>{t("Platform activity")}</CardTitle>
                <CardDescription>{t("What your customers and their visitors are doing")}</CardDescription>
            </CardHeader>
            <CardContent className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                {METRICS.filter(({ key }) => usage[key]).map(({ key, label, icon: Icon }) => {
                    const value = usage[key]!;
                    return (
                        <div key={key} className="flex flex-col gap-3 rounded-lg border p-4">
                            <div className="flex items-center justify-between gap-2">
                                <span className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Icon className="size-4" />
                                    {t(label)}
                                </span>
                            </div>
                            <div className="flex items-end justify-between gap-2">
                                <span className="text-2xl font-semibold tabular-nums">{formatNumber(value.current)}</span>
                                <TrendBadge value={change(value)} />
                            </div>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
