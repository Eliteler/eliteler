import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { Banknote, Check, Loader2, Wallet, X } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { CopyButton } from "@admin/components/copy-button";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Textarea } from "@admin/components/ui/textarea";
import { fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

type Status = "pending" | "accepted" | "transferred" | "rejected";
type Row = {
    id: string;
    amount: number;
    details: string;
    notes: string | null;
    status: Status;
    date: string | null;
    customer: { id: string; name: string; email: string } | null;
    balance: number;
};
type Props = { requests: Paginated<Row>; tab: string; search: string; counts: Record<string, number> };

const STYLES: Record<Status, string> = {
    pending: "border-warning/40 text-warning",
    accepted: "border-primary/30 text-primary",
    transferred: "border-success/30 text-success",
    rejected: "border-destructive/30 text-destructive",
};

export default function Withdrawals({ requests, tab, search, counts }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const money = (value: number) => formatCurrency(value, currency);
    const [pending, setPending] = useState<{ row: Row; status: "accepted" | "rejected" } | null>(null);
    const [paying, setPaying] = useState<Row | null>(null);
    const [processing, setProcessing] = useState(false);

    const labels: Record<Status, string> = { pending: t("Waiting"), accepted: t("Approved"), transferred: t("Paid"), rejected: t("Rejected") };

    const run = () => {
        if (!pending) return;
        router.post(route("admin.update.withdrawal.request.status"), { requestId: pending.row.id, status: pending.status }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        });
    };

    return (
        <>
            <Head title={t("Payout requests")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Payout requests")}
                    description={t("Referrers asking to withdraw their commission. Approve, send the money, then mark it as paid.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Referrals"), href: route("admin.referrals") },
                        { label: t("Payout requests") },
                    ]}
                />
                <ListToolbar
                    routeName="admin.referral.withdrawal.request"
                    tabs={[
                        { value: "pending", label: labels.pending },
                        { value: "accepted", label: labels.accepted },
                        { value: "transferred", label: labels.transferred },
                        { value: "rejected", label: labels.rejected },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    highlight="pending"
                    placeholder={t("Search customer or request ID...")}
                />
                {requests.data.length === 0 ? (
                    <EmptyState icon={Wallet} title={tab === "pending" ? t("No requests waiting") : t("No requests found")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Referrer")}</TableHead>
                                    <TableHead className="pe-6 text-end">{t("Amount")}</TableHead>
                                    <TableHead className="hidden ps-6 md:table-cell">{t("Send to")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="pe-4 text-end" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {requests.data.map((row) => {
                                    const overBalance = row.status !== "transferred" && row.amount > row.balance;
                                    return (
                                        <TableRow key={row.id}>
                                            <TableCell className="max-w-[220px] ps-4">
                                                {row.customer ? (
                                                    <Link href={route("admin.view.customer", row.customer.id)} className="flex flex-col hover:underline">
                                                        <span className="truncate text-sm">{row.customer.name}</span>
                                                        <span className="truncate text-xs text-muted-foreground">{fromNow(row.date)}</span>
                                                    </Link>
                                                ) : (
                                                    <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="pe-6 text-end">
                                                <div className="flex flex-col items-end">
                                                    <span className="font-medium tabular-nums">{money(row.amount)}</span>
                                                    <span className={cn("text-xs", overBalance ? "text-destructive" : "text-muted-foreground")}>
                                                        {t("Balance")} {money(row.balance)}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="hidden max-w-[280px] ps-6 whitespace-normal md:table-cell">
                                                <div className="flex items-center gap-2">
                                                    <span className="line-clamp-2 min-w-0 flex-1 text-sm break-words whitespace-pre-line">{row.details || "—"}</span>
                                                    {row.details && <CopyButton value={row.details} label={t("Copy payout details")} />}
                                                </div>
                                                {row.notes && <p className="mt-0.5 line-clamp-1 text-xs text-muted-foreground">{row.notes}</p>}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className={STYLES[row.status]}>{labels[row.status]}</Badge>
                                            </TableCell>
                                            <TableCell className="pe-4 text-end">
                                                <div className="flex justify-end gap-1">
                                                    {row.status === "pending" && (
                                                        <>
                                                            <Button size="sm" variant="outline" onClick={() => setPending({ row, status: "rejected" })}>
                                                                <X />
                                                                {t("Reject")}
                                                            </Button>
                                                            <Button size="sm" onClick={() => setPending({ row, status: "accepted" })}>
                                                                <Check />
                                                                {t("Approve")}
                                                            </Button>
                                                        </>
                                                    )}
                                                    {row.status === "accepted" && (
                                                        <>
                                                            <Button size="sm" variant="outline" onClick={() => setPending({ row, status: "rejected" })}>
                                                                {t("Reject")}
                                                            </Button>
                                                            <Button size="sm" onClick={() => setPaying(row)}>
                                                                <Banknote />
                                                                {t("Mark as paid")}
                                                            </Button>
                                                        </>
                                                    )}
                                                    {row.status === "rejected" && (
                                                        <Button size="sm" variant="outline" onClick={() => setPending({ row, status: "accepted" })}>
                                                            {t("Approve")}
                                                        </Button>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </Card>
                )}
                <Pagination data={requests} />
            </div>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={pending?.status === "accepted" ? t("Approve this payout?") : t("Reject this payout?")}
                description={
                    pending
                        ? pending.status === "accepted"
                            ? t("Approving doesn't send money. Send :amount to the referrer, then mark the request as paid.").replace(":amount", money(pending.row.amount))
                            : t("The referrer keeps the commission and can request a payout again.")
                        : undefined
                }
                confirmLabel={pending?.status === "accepted" ? t("Approve") : t("Reject")}
                destructive={pending?.status === "rejected"}
                processing={processing}
                onConfirm={run}
            />
            <PayDialog row={paying} onClose={() => setPaying(null)} />
        </>
    );
}

function PayDialog({ row, onClose }: { row: Row | null; onClose: () => void }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const form = useForm({ request_id: "", transfer_transaction_id: "", transfer_notes: "" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!row) return;
        form.transform((data) => ({ ...data, request_id: row.id }));
        form.post(route("admin.transfer.withdrawal.request"), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    return (
        <Dialog open={row !== null} onOpenChange={(value) => !value && onClose()}>
            <DialogContent className="sm:max-w-md">
                <form onSubmit={submit} className="flex flex-col gap-5">
                    <DialogHeader>
                        <DialogTitle>{t("Record payout")}</DialogTitle>
                        <DialogDescription>
                            {row && `${formatCurrency(row.amount, currency)} → ${row.customer?.name ?? ""}`}
                        </DialogDescription>
                    </DialogHeader>
                    {row?.details && <p className="rounded-lg bg-muted/50 p-3 text-sm whitespace-pre-line">{row.details}</p>}
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="transfer_transaction_id">{t("Transfer reference")}</Label>
                        <Input id="transfer_transaction_id" value={form.data.transfer_transaction_id} onChange={(e) => form.setData("transfer_transaction_id", e.target.value)} placeholder={t("e.g., bank or UPI transaction ID")} required />
                        <FieldError message={form.errors.transfer_transaction_id} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="transfer_notes">{t("Note for the referrer")}</Label>
                        <Textarea id="transfer_notes" value={form.data.transfer_notes} onChange={(e) => form.setData("transfer_notes", e.target.value)} className="min-h-20" />
                        <FieldError message={form.errors.transfer_notes} />
                    </div>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" onClick={onClose}>{t("Cancel")}</Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {t("Mark as paid")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
