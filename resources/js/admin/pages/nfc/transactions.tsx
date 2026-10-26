import { Head, Link, router, usePage } from "@inertiajs/react";
import { CheckCircle2, Clock, FileText, MoreHorizontal, Receipt, XCircle } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import type { Paginated } from "@admin/types";

import { ListToolbar } from "@admin/components/list-toolbar";
import { DeliveryBadge, PaymentBadge } from "./components/status-badges";
import type { Customer } from "./types";

type Transaction = {
    id: string;
    reference: string | null;
    invoice: string | null;
    method: string;
    amount: number;
    status: "success" | "pending" | "failed";
    orderId: string;
    delivery: string;
    date: string | null;
    customer: Customer | null;
};

type Props = { transactions: Paginated<Transaction>; tab: string; search: string; counts: Record<string, number> };
type Action = "success" | "pending" | "failed";

export default function NfcTransactions({ transactions, tab, search, counts }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [pending, setPending] = useState<{ transaction: Transaction; status: Action } | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(
            route("admin.action.transaction"),
            { id: pending.transaction.id, status: pending.status },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setPending(null);
                },
            },
        );
    };

    const dialog = pending && {
        success: {
            title: t("Mark this payment as paid?"),
            description: t(
                "The order moves to processing, stock is reduced, NFC links are created and the invoice is emailed to the customer. Use this after you've received an offline payment.",
            ),
            label: t("Mark as paid"),
            destructive: false,
        },
        failed: {
            title: t("Mark this payment as failed?"),
            description:
                pending.transaction.status === "success"
                    ? t("The order is cancelled and the cards go back into stock.")
                    : t("The order is cancelled."),
            label: t("Mark as failed"),
            destructive: true,
        },
        pending: {
            title: t("Mark this payment as pending?"),
            description:
                pending.transaction.status === "success"
                    ? t("The order goes back to awaiting payment and the cards go back into stock.")
                    : t("The order goes back to awaiting payment."),
            label: t("Mark as pending"),
            destructive: false,
        },
    }[pending.status];

    return (
        <>
            <Head title={t("NFC payments")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("NFC payments")}
                    description={t("Payments for NFC card orders. Confirm offline payments here.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("NFC payments") }]}
                />

                <ListToolbar
                    routeName="admin.transactions"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "success", label: t("Paid") },
                        { value: "pending", label: t("Pending") },
                        { value: "failed", label: t("Failed") },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    highlight="pending"
                    placeholder={t("Search transaction, order or customer...")}
                />

                {transactions.data.length === 0 ? (
                    <EmptyState icon={Receipt} title={t("No payments found")} description={search ? t("Try a different search.") : undefined} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Payment")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Order")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Customer")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Method")}</TableHead>
                                    <TableHead className="text-end">{t("Amount")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {transactions.data.map((transaction) => (
                                    <TableRow key={transaction.id}>
                                        <TableCell className="max-w-[220px] ps-4">
                                            <div className="flex flex-col">
                                                <span className="truncate font-mono text-xs font-medium" title={transaction.id}>
                                                    {transaction.invoice ?? transaction.id}
                                                </span>
                                                <span className="text-xs text-muted-foreground" title={formatDate(transaction.date, "dd MMM yyyy, HH:mm")}>
                                                    {fromNow(transaction.date)}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <div className="flex flex-col items-start gap-1">
                                                <Link href={route("admin.order.show", transaction.orderId)} className="font-mono text-xs hover:underline">
                                                    {transaction.orderId}
                                                </Link>
                                                <DeliveryBadge status={transaction.delivery} />
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[200px] lg:table-cell">
                                            {transaction.customer ? (
                                                <Link href={route("admin.view.customer", transaction.customer.id)} className="flex flex-col hover:underline">
                                                    <span className="truncate text-sm">{transaction.customer.name}</span>
                                                    <span className="truncate text-xs text-muted-foreground">{transaction.customer.email}</span>
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden sm:table-cell">{transaction.method}</TableCell>
                                        <TableCell className="text-end font-medium tabular-nums">{formatCurrency(transaction.amount, currency)}</TableCell>
                                        <TableCell>
                                            <PaymentBadge status={transaction.status} />
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" className="w-48">
                                                    {transaction.status === "success" && (
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route("admin.transaction.show", transaction.id)}>
                                                                <FileText />
                                                                {t("View invoice")}
                                                            </Link>
                                                        </DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route("admin.order.show", transaction.orderId)}>
                                                            <Receipt />
                                                            {t("Open order")}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    {transaction.status !== "success" && (
                                                        <DropdownMenuItem onSelect={() => setPending({ transaction, status: "success" })}>
                                                            <CheckCircle2 />
                                                            {t("Mark as paid")}
                                                        </DropdownMenuItem>
                                                    )}
                                                    {transaction.status !== "pending" && (
                                                        <DropdownMenuItem onSelect={() => setPending({ transaction, status: "pending" })}>
                                                            <Clock />
                                                            {t("Mark as pending")}
                                                        </DropdownMenuItem>
                                                    )}
                                                    {transaction.status !== "failed" && (
                                                        <DropdownMenuItem variant="destructive" onSelect={() => setPending({ transaction, status: "failed" })}>
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
                onOpenChange={(open) => !open && setPending(null)}
                title={dialog?.title ?? ""}
                description={dialog?.description}
                confirmLabel={dialog?.label}
                destructive={dialog?.destructive}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
