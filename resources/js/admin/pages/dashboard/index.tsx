import { Head, router, usePage } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { cn } from "@admin/lib/utils";

import { ActivityCard } from "./components/activity-card";
import { AttentionCard } from "./components/attention-card";
import { CustomersCard } from "./components/customers-card";
import { GatewaysCard } from "./components/gateways-card";
import { GrowthChart } from "./components/growth-chart";
import { KpiCards } from "./components/kpi-cards";
import { PlanMixCard } from "./components/plan-mix-card";
import { RecentPayments } from "./components/recent-payments";
import { RevenueChart } from "./components/revenue-chart";
import { SystemNotices } from "./components/system-notices";
import { PERIOD_PROPS, type DashboardProps, type Period } from "./types";

const PERIOD_LABELS: Record<Period, { short: string; long: string }> = {
    "7d": { short: "7 days", long: "vs previous 7 days" },
    "30d": { short: "30 days", long: "vs previous 30 days" },
    "90d": { short: "90 days", long: "vs previous 90 days" },
    "12m": { short: "12 months", long: "vs previous 12 months" },
};

function greeting() {
    const hour = new Date().getHours();
    if (hour >= 5 && hour < 12) return "Good morning";
    if (hour >= 12 && hour < 17) return "Good afternoon";
    if (hour >= 17 && hour < 22) return "Good evening";
    return "Good night";
}

export default function Dashboard(props: DashboardProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const [loading, setLoading] = useState(false);

    const changePeriod = (period: string) => {
        router.reload({
            only: [...PERIOD_PROPS],
            data: { period },
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const periodLabel = t(PERIOD_LABELS[props.period].long);

    return (
        <>
            <Head title={t("Dashboard")} />
            <div className="@container/main flex flex-1 flex-col gap-4 p-4 md:gap-6 lg:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {t(greeting())}, {auth.user?.name}
                        </h1>
                        <p className="text-sm text-muted-foreground">{t("Here's how your business is doing.")}</p>
                    </div>
                    <div className="flex items-center gap-2">
                        {loading && <Loader2 className="size-4 animate-spin text-muted-foreground" />}
                        <Tabs value={props.period} onValueChange={changePeriod}>
                            <TabsList>
                                {props.periods.map((period) => (
                                    <TabsTrigger key={period} value={period} disabled={loading}>
                                        {t(PERIOD_LABELS[period].short)}
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                        </Tabs>
                    </div>
                </div>

                <SystemNotices notice={props.systemNotice} />

                <div className={cn("flex flex-col gap-4 transition-opacity md:gap-6", loading && "pointer-events-none opacity-60")}>
                    <KpiCards kpis={props.kpis} periodLabel={periodLabel} />

                    {props.hideRevenue ? (
                        <>
                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <GrowthChart data={props.growthSeries} />
                                <AttentionCard items={props.attention} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <ActivityCard usage={props.usage} />
                                <PlanMixCard rows={props.planMix} plansUrl={props.links.plans} />
                            </div>

                            <CustomersCard recent={props.recentCustomers} expiring={props.expiringSoon} links={props.links} />
                        </>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <RevenueChart data={props.revenueSeries ?? []} />
                                <AttentionCard items={props.attention} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <GrowthChart data={props.growthSeries} />
                                <PlanMixCard rows={props.planMix} plansUrl={props.links.plans} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <RecentPayments payments={props.recentPayments ?? []} links={props.links} />
                                <GatewaysCard gateways={props.gateways ?? []} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
                                <CustomersCard recent={props.recentCustomers} expiring={props.expiringSoon} links={props.links} />
                                <ActivityCard usage={props.usage} />
                            </div>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
