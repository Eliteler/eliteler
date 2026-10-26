import { Head, Link, router, usePage } from "@inertiajs/react";
import { CheckCircle2, Clock, FileText, MoreHorizontal, Receipt, XCircle } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@admin/components/ui/dropdown-menu";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

type Purchase = {
    id: string;
    invoice: string | null;
    plan: string;
    credits: number | null;
    method: string;
    amount: number;
    status: string;
    date: string | null;
    customer: { id: string; name: string; email: string } | null;
};

type Props = { transactions: Paginated<Purchase>; tab: string; search: string; counts: Record<string, number> };

const LABELS: Record<string, string> = {
    paid: "Paid",
    pending: "Pending",
    processing: "Processing",
    failed: "Failed",
    cancelled: "Cancelled",
    refunded: "Refunded",
    partially_refunded: "Partly refunded",
};

export default function AiCreditPurchases({ transactions, tab, search, counts }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [pending, setPending] = useState<{ row: Purchase; status: "paid" | "pending" | "failed" } | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(route("admin.ai.credits.transaction.status", { id: pending.row.id, status: pending.status }), {}, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        });
    };

    const wasPaid = pending?.row.status === "paid";
    const copy = pending && {
        paid: {
            title: t("Mark this purchase as paid?"),
            description: t(":count credits are added to the customer's balance and the purchase gets an invoice number.").replace(":count", String(pending.row.credits ?? 0)),
            label: t("Mark as paid"),
        },
        pending: {
            title: t("Mark this purchase as pending?"),
            description: wasPaid ? t("The credits from this purchase are taken back from the customer's balance.") : t("The purchase waits for payment."),
            label: t("Mark as pending"),
        },
        failed: {
            title: t("Mark this purchase as failed?"),
            description: wasPaid ? t("The credits from this purchase are taken back from the customer's balance.") : t("The purchase is marked as not paid."),
            label: t("Mark as failed"),
        },
    }[pending.status];

    return (
        <>
            <Head title={t("AI credit purchases")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("AI credit purchases")}
                    description={t("Credit packs bought by customers. Confirm offline payments here.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("AI credit purchases") }]}
                />
                <ListToolbar
                    routeName="admin.ai.credits.transactions"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "paid", label: t("Paid") },
                        { value: "pending", label: t("Pending") },
                        { value: "failed", label: t("Failed") },
                        { value: "refunded", label: t("Refunded") },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    highlight="pending"
                    placeholder={t("Search order, transaction or customer...")}
                />
                {transactions.data.length === 0 ? (
                    <EmptyState icon={Receipt} title={t("No purchases found")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Purchase")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Customer")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Method")}</TableHead>
                                    <TableHead className="text-end">{t("Amount")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {transactions.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="ps-4">
                                            <div className="flex flex-col">
                                                <span className="text-sm font-medium">
                                                    {row.plan}
                                                    {row.credits !== null && <span className="ms-1.5 text-xs font-normal text-muted-foreground">{row.credits.toLocaleString()} {t("credits")}</span>}
                                                </span>
                                                <span className="text-xs text-muted-foreground" title={formatDate(row.date, "dd MMM yyyy, HH:mm")}>
                                                    {row.invoice ?? row.id} · {fromNow(row.date)}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[220px] md:table-cell">
                                            {row.customer ? (
                                                <Link href={route("admin.view.customer", row.customer.id)} className="flex flex-col hover:underline">
                                                    <span className="truncate text-sm">{row.customer.name}</span>
                                                    <span className="truncate text-xs text-muted-foreground">{row.customer.email}</span>
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden sm:table-cell">{row.method}</TableCell>
                                        <TableCell className="text-end font-medium tabular-nums">{formatCurrency(row.amount, currency)}</TableCell>
                                        <TableCell>
                                            <Badge
                                                variant="outline"
                                                className={cn(
                                                    row.status === "paid" && "border-success/30 text-success",
                                                    ["failed", "cancelled"].includes(row.status) && "border-destructive/30 text-destructive",
                                                    ["pending", "processing"].includes(row.status) && "border-warning/40 text-warning",
                                                )}
                                            >
                                                {t(LABELS[row.status] ?? row.status)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" className="w-48">
                                                    {row.status === "paid" && (
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route("admin.ai.credits.transaction.invoice", row.id)}>
                                                                <FileText />
                                                                {t("View invoice")}
                                                            </Link>
                                                        </DropdownMenuItem>
                                                    )}
                                                    {row.status === "paid" && <DropdownMenuSeparator />}
                                                    {row.status !== "paid" && (
                                                        <DropdownMenuItem onSelect={() => setPending({ row, status: "paid" })}>
                                                            <CheckCircle2 />
                                                            {t("Mark as paid")}
                                                        </DropdownMenuItem>
                                                    )}
                                                    {row.status !== "pending" && (
                                                        <DropdownMenuItem onSelect={() => setPending({ row, status: "pending" })}>
                                                            <Clock />
                                                            {t("Mark as pending")}
                                                        </DropdownMenuItem>
                                                    )}
                                                    {row.status !== "failed" && (
                                                        <DropdownMenuItem variant="destructive" onSelect={() => setPending({ row, status: "failed" })}>
                                                            <XCircle />
                                                            {t("Mark as failed")}
                                                        </DropdownMenuItem>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}
                <Pagination data={transactions} />
            </div>
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={copy?.title ?? ""}
                description={copy?.description}
                confirmLabel={copy?.label}
                destructive={pending?.status === "failed"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
