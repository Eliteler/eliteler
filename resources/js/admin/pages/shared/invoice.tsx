import { Head, Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Printer } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Card, CardContent } from "@admin/components/ui/card";
import { Separator } from "@admin/components/ui/separator";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";

import { PaymentBadge } from "../nfc/components/status-badges";

type Party = Partial<Record<"name" | "address" | "phone" | "email" | "taxId", string>>;

type Invoice = {
    number: string | null;
    id: string;
    reference: string;
    orderId?: string;
    date: string | null;
    status: string;
    method: string;
    currency: string;
    logo: string | null;
    from: Party;
    to: Party;
    item: { name: string; description: string | null; price: number; quantity: number };
    subtotal: number;
    taxName: string | null;
    taxRate: string | null;
    taxType: string | null;
    tax: number;
    coupon: string | null;
    discount: number;
    total: number;
};

function PartyBlock({ title, party }: { title: string; party: Party }) {
    const { t } = useTranslation();
    return (
        <div className="flex flex-col gap-1 text-sm">
            <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">{title}</span>
            {party.name && <span className="font-semibold">{party.name}</span>}
            {party.address && <span className="text-muted-foreground">{party.address}</span>}
            {party.email && <span className="text-muted-foreground">{party.email}</span>}
            {party.phone && <span className="text-muted-foreground" dir="ltr">{party.phone}</span>}
            {party.taxId && (
                <span className="text-muted-foreground">
                    {t("Tax ID")}: {party.taxId}
                </span>
            )}
        </div>
    );
}

export default function Invoice({ invoice, back }: { invoice: Invoice; back: { label: string; href: string } }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const money = (value: number) => formatCurrency(value, currency);

    return (
        <>
            <Head title={`${t("Invoice")} ${invoice.number ?? invoice.id}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <div data-print="hide" className="flex flex-wrap items-center justify-between gap-2">
                    <Button variant="secondary" asChild>
                        <Link href={back.href}>
                            <ArrowLeft className="rtl:rotate-180" />
                            {back.label}
                        </Link>
                    </Button>
                    <div className="flex gap-2">
                        {invoice.orderId && (
                            <Button variant="outline" asChild>
                                <Link href={route("admin.order.show", invoice.orderId)}>{t("Open order")}</Link>
                            </Button>
                        )}
                        <Button onClick={() => window.print()}>
                            <Printer />
                            {t("Print or save as PDF")}
                        </Button>
                    </div>
                </div>

                <Card className="mx-auto w-full max-w-3xl print:border-0 print:shadow-none">
                    <CardContent className="flex flex-col gap-8 p-6 sm:p-10">
                        <div className="flex flex-col justify-between gap-6 sm:flex-row">
                            <div className="flex flex-col gap-3">
                                {invoice.logo && <img src={invoice.logo} alt="" className="h-10 w-auto self-start object-contain" />}
                                <PartyBlock title={t("From")} party={invoice.from} />
                            </div>
                            <div className="flex flex-col gap-1 sm:text-end">
                                <span className="text-2xl font-semibold tracking-tight">{t("Invoice")}</span>
                                <span className="font-mono text-sm">{invoice.number ?? "—"}</span>
                                <span className="text-sm text-muted-foreground">{formatDate(invoice.date)}</span>
                                <span className="sm:self-end">
                                    <PaymentBadge status={invoice.status === "paid" ? "success" : invoice.status} />
                                </span>
                            </div>
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2">
                            <PartyBlock title={t("Bill to")} party={invoice.to} />
                            <div className="flex flex-col gap-1 text-sm sm:text-end">
                                <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">{t("Payment")}</span>
                                <span>{invoice.method}</span>
                                <span className="font-mono text-xs text-muted-foreground">{invoice.reference}</span>
                                {invoice.orderId && (
                                    <span className="text-muted-foreground">
                                        {t("Order")} <span className="font-mono text-xs">{invoice.orderId}</span>
                                    </span>
                                )}
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead>{t("Item")}</TableHead>
                                    <TableHead className="text-end">{t("Price")}</TableHead>
                                    <TableHead className="text-end">{t("Qty")}</TableHead>
                                    <TableHead className="text-end">{t("Amount")}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow className="hover:bg-transparent">
                                    <TableCell className="whitespace-normal">
                                        <div className="flex flex-col">
                                            <span className="font-medium">{invoice.item.name}</span>
                                            {invoice.item.description && <span className="text-xs text-muted-foreground">{invoice.item.description}</span>}
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-end tabular-nums">{money(invoice.item.price)}</TableCell>
                                    <TableCell className="text-end tabular-nums">{invoice.item.quantity}</TableCell>
                                    <TableCell className="text-end tabular-nums">{money(invoice.subtotal)}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div className="ms-auto flex w-full max-w-xs flex-col gap-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">{t("Subtotal")}</span>
                                <span className="tabular-nums">{money(invoice.subtotal)}</span>
                            </div>
                            {invoice.tax > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {invoice.taxName ?? t("Tax")}
                                        {invoice.taxRate ? ` (${invoice.taxRate}%)` : ""}
                                    </span>
                                    <span className="tabular-nums">{money(invoice.tax)}</span>
                                </div>
                            )}
                            {invoice.coupon && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {t("Coupon")} {invoice.coupon}
                                    </span>
                                    <span className="tabular-nums">−{money(invoice.discount)}</span>
                                </div>
                            )}
                            <Separator />
                            <div className="flex justify-between text-base font-semibold">
                                <span>{t("Total")}</span>
                                <span className="tabular-nums">{money(invoice.total)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
