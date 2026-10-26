import { Head, Link, usePage } from "@inertiajs/react";
import { BarChart3 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Card, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";

import { discountLabel } from "./index";
import { USED_FOR_LABELS, type Coupon } from "./types";

type Use = {
    reference: string;
    redeemed: boolean;
    date: string | null;
    customer: { id: string; name: string; email: string } | null;
    paid: number | null;
    discount: number | null;
};

export default function CouponUsage({ coupon, uses }: { coupon: Coupon; uses: Use[] }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const money = (value: number) => formatCurrency(value, currency);
    const redeemed = uses.filter((use) => use.redeemed);
    const saved = redeemed.reduce((sum, use) => sum + (use.discount ?? 0), 0);
    const revenue = redeemed.reduce((sum, use) => sum + (use.paid ?? 0), 0);

    const stats = [
        { label: t("Redeemed"), value: `${redeemed.length} / ${coupon.total}` },
        { label: t("Started but not paid"), value: String(uses.length - redeemed.length) },
        { label: t("Discount given"), value: money(saved) },
        { label: t("Revenue with this code"), value: money(revenue) },
    ];

    return (
        <>
            <Head title={`${t("Coupon")} ${coupon.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={<span className="font-mono">{coupon.code}</span>}
                    description={`${discountLabel(coupon, currency)} · ${t(USED_FOR_LABELS[coupon.usedFor])}${coupon.description ? ` · ${coupon.description}` : ""}`}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Coupons"), href: route("admin.coupons") },
                        { label: coupon.code },
                    ]}
                />
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {stats.map((stat) => (
                        <Card key={stat.label} className="gap-1 py-5">
                            <CardHeader className="px-5">
                                <CardDescription>{stat.label}</CardDescription>
                                <CardTitle className="text-2xl tabular-nums">{stat.value}</CardTitle>
                            </CardHeader>
                        </Card>
                    ))}
                </div>
                {uses.length === 0 ? (
                    <EmptyState icon={BarChart3} title={t("Nobody has used this coupon yet")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Customer")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Reference")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="text-end">{t("Discount")}</TableHead>
                                    <TableHead className="hidden pe-4 text-end sm:table-cell">{t("Date")}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {uses.map((use, index) => (
                                    <TableRow key={`${use.reference}-${index}`}>
                                        <TableCell className="max-w-[220px] ps-4">
                                            {use.customer ? (
                                                <Link href={route("admin.view.customer", use.customer.id)} className="flex flex-col hover:underline">
                                                    <span className="truncate text-sm">{use.customer.name}</span>
                                                    <span className="truncate text-xs text-muted-foreground">{use.customer.email}</span>
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden font-mono text-xs md:table-cell">{use.reference}</TableCell>
                                        <TableCell>
                                            {use.redeemed ? (
                                                <Badge variant="outline" className="border-success/30 text-success">{t("Redeemed")}</Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-muted-foreground">{t("Not paid")}</Badge>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-end tabular-nums">{use.discount !== null ? money(use.discount) : "—"}</TableCell>
                                        <TableCell className="hidden pe-4 text-end text-sm text-muted-foreground sm:table-cell">{formatDate(use.date)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}
            </div>
        </>
    );
}
