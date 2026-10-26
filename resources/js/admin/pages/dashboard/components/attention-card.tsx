import { ArrowUpRight, Banknote, CalendarClock, CheckCircle2, Globe, HandCoins, Nfc, PackageOpen } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { cn } from "@admin/lib/utils";

import type { AttentionItem, AttentionKey } from "../types";

const META: Record<AttentionKey, { label: string; hint: string; icon: typeof Banknote }> = {
    offline_payments: { label: "Offline payments", hint: "Waiting for approval", icon: Banknote },
    nfc_orders: { label: "NFC card orders", hint: "Pending, processing or on hold", icon: Nfc },
    low_stock: { label: "Low NFC stock", hint: "Designs with fewer than 10 left", icon: PackageOpen },
    domain_requests: { label: "Custom domains", hint: "Requests to review", icon: Globe },
    withdrawals: { label: "Referral withdrawals", hint: "Payouts to process", icon: HandCoins },
    expiring: { label: "Expiring subscriptions", hint: "Paid plans ending in 7 days", icon: CalendarClock },
};

export function AttentionCard({ items }: { items: AttentionItem[] }) {
    const { t } = useTranslation();
    const open = items.filter((item) => item.count > 0);
    const done = items.filter((item) => item.count === 0);

    return (
        <Card className="gap-4">
            <CardHeader>
                <CardTitle>{t("Needs attention")}</CardTitle>
                <CardDescription>
                    {open.length ? t("Items waiting for an admin") : t("Nothing is waiting for you")}
                </CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col gap-1 px-3">
                {open.length === 0 && (
                    <div className="flex flex-col items-center gap-2 py-8 text-center text-sm text-muted-foreground">
                        <CheckCircle2 className="size-8 text-success" />
                        {t("All caught up")}
                    </div>
                )}
                {[...open, ...done].map((item) => {
                    const meta = META[item.key];
                    const Icon = meta.icon;
                    const Row = item.href ? "a" : "div";

                    return (
                        <Row
                            key={item.key}
                            {...(item.href ? { href: item.href } : {})}
                            className={cn(
                                "group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors",
                                item.href && "hover:bg-muted",
                                item.count === 0 && "opacity-60",
                            )}
                        >
                            <span
                                className={cn(
                                    "flex size-9 shrink-0 items-center justify-center rounded-md border",
                                    item.count > 0 ? "bg-primary/10 text-primary" : "bg-muted text-muted-foreground",
                                )}
                            >
                                <Icon className="size-4" />
                            </span>
                            <span className="flex min-w-0 flex-1 flex-col">
                                <span className="truncate text-sm font-medium">{t(meta.label)}</span>
                                <span className="truncate text-xs text-muted-foreground">{t(meta.hint)}</span>
                            </span>
                            <Badge variant={item.count > 0 ? "default" : "secondary"} className="tabular-nums">
                                {item.count}
                            </Badge>
                            {item.href && (
                                <ArrowUpRight className="size-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 rtl:-scale-x-100" />
                            )}
                        </Row>
                    );
                })}
            </CardContent>
        </Card>
    );
}
