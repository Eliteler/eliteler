import { Head, Link, router, usePage } from "@inertiajs/react";
import { Ban, CheckCircle2, Clock, FileText, MoreHorizontal, Receipt, XCircle } from "lucide-react";
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

type Status = "success" | "pending" | "failed" | "cancelled";
type Row = {
    id: string;
    reference: string | null;
    invoice: string | null;
    plan: string;
    gateway: string;
    amount: number;
    status: Status;
    date: string | null;
    customer: { id: string; name: string; email: string } | null;
};
type Props = { transactions: Paginated<Row>; filters: { tab: string; type: string; search: string }; counts: Record<string, number> };
type Action = "SUCCESS" | "PENDING" | "FAILED" | "CANCELLED";

const STYLES: Record<Status, string> = {
    success: "border-success/30 text-success",
    pending: "border-warning/40 text-warning",
    failed: "border-destructive/30 text-destructive",
    cancelled: "text-muted-foreground",
};

export default function Transactions({ transactions, filters, counts }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [pending, setPending] = useState<{ row: Row; status: Action } | null>(null);
    const [processing, setProcessing] = useState(false);
    const labels: Record<Status, string> = { success: t("Paid"), pending: t("Pending"), failed: t("Failed"), cancelled: t("Cancelled") };

    const run = () =>
        pending &&
        router.post(route("admin.trans.status", { id: pending.row.id, status: pending.status }), {}, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        });

    const setType = (type: string) =>
        router.get(route("admin.transactions.index"), {
            ...(filters.tab !== "all" ? { tab: filters.tab } : {}),
            ...(type !== "all" ? { type } : {}),
            ...(filters.search ? { search: filters.search } : {}),
        }, { preserveState: true, preserveScroll: true, replace: true });

    const wasPaid = pending?.row.status === "success";
    const dialog = pending && {
        SUCCESS: {
            title: t("Confirm this payment?"),
            description: t("The customer gets the :plan plan right away, the payment gets an invoice number and the invoice is emailed to them. Use this once you've received an offline payment.").replace(":plan", pending.row.plan),
            label: t("Confirm payment"),
        },
        PENDING: { title: t("Mark as pending?"), description: wasPaid ? t("The invoice number is removed. The customer's current plan is not changed.") : t("The payment waits for confirmation."), label: t("Mark as pending") },
        FAILED: { title: t("Mark as failed?"), description: wasPaid ? t("The invoice number is removed. The customer's current plan is not changed.") : t("The payment is marked as failed."), label: t("Mark as failed") },
        CANCELLED: { title: t("Mark as cancelled?"), description: wasPaid ? t("The invoice number is removed. The customer's current plan is not changed.") : t("The payment is marked as cancelled."), label: t("Mark as cancelled") },
    }[pending.status];

    return (
        <>
            <Head title={t("Transactions")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Transactions")}
                    description={t("Plan payments from customers. Confirm offline payments here.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Transactions") }]}
                />
                <ListToolbar
                    routeName="admin.transactions.index"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "paid", label: t("Paid") },
                        { value: "pending", label: t("Pending") },
                        { value: "failed", label: t("Failed or cancelled") },
                    ]}
                    tab={filters.tab}
                    counts={counts}
                    search={filters.search}
                    extraQuery={filters.type !== "all" ? { type: filters.type } : {}}
                    placeholder={t("Search ID, invoice, customer or gateway...")}
                >
                    <Select value={filters.type} onValueChange={setType}>
                        <SelectTrigger className="w-40"><SelectValue /></SelectTrigger>
                        <SelectContent align="end">
                            <SelectItem value="all">{t("All payment types")}</SelectItem>
                            <SelectItem value="online">{t("Online gateways")}</SelectItem>
                            <SelectItem value="offline">{t("Offline")}</SelectItem>
                            <SelectItem value="free">{t("Free and trial")}</SelectItem>
                        </SelectContent>
                    </Select>
                </ListToolbar>
                {transactions.data.length === 0 ? (
                    <EmptyState icon={Receipt} title={t("No transactions found")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Payment")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Customer")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Gateway")}</TableHead>
                                    <TableHead className="text-end">{t("Amount")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {transactions.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="max-w-[240px] ps-4">
                                            <span className="block truncate text-sm font-medium">{row.plan}</span>
                                            <span className="block truncate text-xs text-muted-foreground" title={formatDate(row.date, "dd MMM yyyy, HH:mm")}>
                                                {row.invoice ?? row.id} · {fromNow(row.date)}
                                            </span>
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
                                        <TableCell className="hidden sm:table-cell">{row.gateway}</TableCell>
                                        <TableCell className="text-end font-medium tabular-nums">{formatCurrency(row.amount, currency)}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline" className={cn(STYLES[row.status])}>{labels[row.status] ?? row.status}</Badge>
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}><MoreHorizontal /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" className="w-48">
                                                    {row.status === "success" && row.gateway !== 'TRIAL' && row.gateway !== 'FREE' ? (
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route("admin.view.invoice", row.id)}><FileText />{t("View invoice")}</Link>
                                                        </DropdownMenuItem>
                                                    ) : (
                                                        <DropdownMenuItem onSelect={() => setPending({ row, status: "SUCCESS" })}><CheckCircle2 />{t("Confirm payment")}</DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuSeparator />
                                                    {row.status !== "pending" && <DropdownMenuItem onSelect={() => setPending({ row, status: "PENDING" })}><Clock />{t("Mark as pending")}</DropdownMenuItem>}
                                                    {row.status !== "failed" && <DropdownMenuItem onSelect={() => setPending({ row, status: "FAILED" })}><XCircle />{t("Mark as failed")}</DropdownMenuItem>}
                                                    {row.status !== "cancelled" && <DropdownMenuItem variant="destructive" onSelect={() => setPending({ row, status: "CANCELLED" })}><Ban />{t("Mark as cancelled")}</DropdownMenuItem>}
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
                onOpenChange={(v) => !v && setPending(null)}
                title={dialog?.title ?? ""}
                description={dialog?.description}
                confirmLabel={dialog?.label}
                destructive={pending?.status !== "SUCCESS" && pending?.status !== "PENDING"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
