import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from "@admin/components/ui/chart";
import { formatCompact, formatCurrency } from "@admin/lib/format";

import type { RevenuePoint } from "../types";

export function RevenueChart({ data }: { data: RevenuePoint[] }) {
    const { t } = useTranslation();
    const { currency, features } = usePage().props;

    const config: ChartConfig = {
        plans: { label: t("Plans"), color: "var(--chart-1)" },
        ...(features.nfc ? { nfc: { label: t("NFC cards"), color: "var(--chart-2)" } } : {}),
        ...(features.aibuilder ? { ai: { label: t("AI credits"), color: "var(--chart-4)" } } : {}),
    };
    const keys = Object.keys(config) as (keyof RevenuePoint)[];

    const totals = keys.map((key) => ({
        key,
        total: data.reduce((sum, row) => sum + Number(row[key] || 0), 0),
    }));

    return (
        <Card className="lg:col-span-2">
            <CardHeader>
                <CardTitle>{t("Revenue")}</CardTitle>
                <CardDescription>{t("Collected payments by source")}</CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <div className="flex flex-wrap gap-x-6 gap-y-2">
                    {totals.map(({ key, total }) => (
                        <div key={key} className="flex items-center gap-2">
                            <span className="size-2.5 rounded-[3px]" style={{ background: config[key]?.color }} />
                            <span className="text-sm text-muted-foreground">{config[key]?.label}</span>
                            <span className="text-sm font-semibold tabular-nums">{formatCurrency(total, currency)}</span>
                        </div>
                    ))}
                </div>
                <ChartContainer config={config} className="aspect-auto h-[280px] w-full">
                    <BarChart data={data} margin={{ left: 0, right: 0, top: 4 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} minTickGap={20} />
                        <YAxis tickLine={false} axisLine={false} width={44} tickFormatter={formatCompact} />
                        <ChartTooltip
                            cursor={{ fill: "var(--muted)", opacity: 0.5 }}
                            content={<ChartTooltipContent valueFormatter={(value) => formatCurrency(value, currency)} />}
                        />
                        <ChartLegend content={<ChartLegendContent />} />
                        {keys.map((key, i) => (
                            <Bar
                                key={key}
                                dataKey={key}
                                stackId="revenue"
                                fill={`var(--color-${key})`}
                                radius={i === keys.length - 1 ? [4, 4, 0, 0] : 0}
                                maxBarSize={40}
                            />
                        ))}
                    </BarChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}
