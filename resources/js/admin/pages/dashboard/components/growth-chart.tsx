import { useTranslation } from "react-i18next";
import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from "recharts";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from "@admin/components/ui/chart";
import { formatNumber } from "@admin/lib/format";

import type { GrowthPoint } from "../types";

export function GrowthChart({ data }: { data: GrowthPoint[] }) {
    const { t } = useTranslation();

    const config = {
        signups: { label: t("New customers"), color: "var(--chart-4)" },
        subscriptions: { label: t("Paid subscriptions"), color: "var(--chart-1)" },
    } satisfies ChartConfig;

    const signups = data.reduce((sum, row) => sum + row.signups, 0);
    const subscriptions = data.reduce((sum, row) => sum + row.subscriptions, 0);

    return (
        <Card className="lg:col-span-2">
            <CardHeader>
                <CardTitle>{t("Customer growth")}</CardTitle>
                <CardDescription>
                    {formatNumber(signups)} {t("sign-ups")} · {formatNumber(subscriptions)} {t("paid subscriptions")}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer config={config} className="aspect-auto h-[260px] w-full">
                    <AreaChart data={data} margin={{ left: 0, right: 0, top: 4 }}>
                        <defs>
                            {(Object.keys(config) as (keyof typeof config)[]).map((key) => (
                                <linearGradient key={key} id={`growth-${key}`} x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor={`var(--color-${key})`} stopOpacity={0.4} />
                                    <stop offset="95%" stopColor={`var(--color-${key})`} stopOpacity={0.02} />
                                </linearGradient>
                            ))}
                        </defs>
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} minTickGap={20} />
                        <YAxis tickLine={false} axisLine={false} width={32} allowDecimals={false} />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent indicator="dot" />} />
                        <ChartLegend content={<ChartLegendContent />} />
                        <Area dataKey="signups" type="monotone" stroke="var(--color-signups)" fill="url(#growth-signups)" strokeWidth={2} />
                        <Area
                            dataKey="subscriptions"
                            type="monotone"
                            stroke="var(--color-subscriptions)"
                            fill="url(#growth-subscriptions)"
                            strokeWidth={2}
                        />
                    </AreaChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}
