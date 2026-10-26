import { Head, Link, usePage } from "@inertiajs/react";
import { ArrowRight, Gift } from "lucide-react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { useHideRevenue } from "@admin/hooks/use-hide-revenue";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import type { Paginated } from "@admin/types";

type Person = { id: string; name: string; email: string } | null;
type Row = { id: number; customer: Person; referrer: Person; subscribed: boolean; commission: number | null; date: string | null };
type Props = {
    referrals: Paginated<Row>;
    tab: string;
    search: string;
    counts: Record<string, number>;
    // Money stats are left out when revenue is hidden
    stats: { referrals: number; subscribed: number; earned?: number; paidOut?: number; owed?: number; requests?: number };
};

function PersonCell({ person }: { person: Person }) {
    const { t } = useTranslation();
    if (!person) return <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>;
    return (
        <Link href={route("admin.view.customer", person.id)} className="flex min-w-0 flex-col hover:underline">
            <span className="truncate text-sm">{person.name}</span>
            <span className="truncate text-xs text-muted-foreground">{person.email}</span>
        </Link>
    );
}

export default function Referrals({ referrals, tab, search, counts, stats }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const hideRevenue = useHideRevenue();
    const money = (value: number) => formatCurrency(value, currency);
    const rate = stats.referrals ? Math.round((stats.subscribed / stats.referrals) * 100) : 0;

    const cards = [
        { label: t("Referred sign-ups"), value: stats.referrals.toLocaleString(), note: `${stats.subscribed} ${t("subscribed")} (${rate}%)` },
        ...(hideRevenue
            ? []
            : [
                  { label: t("Commission earned"), value: money(stats.earned ?? 0), note: t("By all referrers") },
                  { label: t("Paid out"), value: money(stats.paidOut ?? 0), note: `${money(stats.owed ?? 0)} ${t("still owed")}` },
              ]),
    ];

    return (
        <>
            <Head title={t("Referrals")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Referrals")}
                    description={t("Customers who signed up through another customer's referral link.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Referrals") }]}
                    actions={
                        !hideRevenue && (
                            <Button variant="outline" asChild>
                                <Link href={route("admin.referral.withdrawal.request")}>
                                    {t("Payout requests")}
                                    {!!stats.requests && <Badge className="ms-1 tabular-nums">{stats.requests}</Badge>}
                                </Link>
                            </Button>
                        )
                    }
                />

                <div className="grid gap-4 sm:grid-cols-3">
                    {cards.map((card) => (
                        <Card key={card.label} className="gap-1 py-5">
                            <CardHeader className="px-5">
                                <CardDescription>{card.label}</CardDescription>
                                <CardTitle className="text-2xl tabular-nums">{card.value}</CardTitle>
                                <span className="text-xs text-muted-foreground">{card.note}</span>
                            </CardHeader>
                        </Card>
                    ))}
                </div>

                <ListToolbar
                    routeName="admin.referrals"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "subscribed", label: t("Subscribed") },
                        { value: "signed_up", label: t("Signed up only") },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    placeholder={t("Search customer or referrer...")}
                />

                {referrals.data.length === 0 ? (
                    <EmptyState icon={Gift} title={t("No referrals yet")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Referred by")}</TableHead>
                                    <TableHead className="w-6" />
                                    <TableHead>{t("New customer")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Status")}</TableHead>
                                    {!hideRevenue && <TableHead className="text-end">{t("Commission")}</TableHead>}
                                    <TableHead className="hidden pe-4 md:table-cell">{t("Joined")}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {referrals.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="max-w-[220px] ps-4"><PersonCell person={row.referrer} /></TableCell>
                                        <TableCell><ArrowRight className="size-4 text-muted-foreground rtl:rotate-180" /></TableCell>
                                        <TableCell className="max-w-[220px]"><PersonCell person={row.customer} /></TableCell>
                                        <TableCell className="hidden sm:table-cell">
                                            {row.subscribed ? (
                                                <Badge variant="outline" className="border-success/30 text-success">{t("Subscribed")}</Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-muted-foreground">{t("Signed up")}</Badge>
                                            )}
                                        </TableCell>
                                        {!hideRevenue && (
                                            <TableCell className="text-end font-medium tabular-nums">
                                                {row.commission !== null ? money(row.commission) : <span className="text-muted-foreground">—</span>}
                                            </TableCell>
                                        )}
                                        <TableCell className="hidden pe-4 text-sm text-muted-foreground md:table-cell">{formatDate(row.date)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}
                <Pagination data={referrals} />
            </div>
        </>
    );
}
