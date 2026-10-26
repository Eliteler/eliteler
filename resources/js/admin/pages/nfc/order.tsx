import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { Download, FileText, KeyRound, Loader2, Mail, Printer, Truck } from "lucide-react";
import { useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { CopyButton } from "@admin/components/copy-button";
import { FieldError } from "@admin/components/field-error";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Separator } from "@admin/components/ui/separator";
import { Switch } from "@admin/components/ui/switch";
import { Textarea } from "@admin/components/ui/textarea";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";

import { DeliveryBadge, PaymentBadge } from "./components/status-badges";
import { DELIVERY_LABELS, type Customer, type DeliveryStatus } from "./types";

type Party = Partial<Record<"name" | "address" | "phone" | "email", string>>;

type Order = {
    id: string;
    date: string | null;
    updated: string | null;
    delivery: DeliveryStatus;
    note: string | null;
    logo: string | null;
    tracking: Partial<Record<"number" | "courier" | "message", string>>;
    item: { name: string; description: string | null; image: string | null; price: number | null; quantity: number };
    // Both null when revenue is hidden
    pricing: { subtotal: number; taxName: string | null; taxRate: string | null; tax: number; coupon: string | null; discount: number; total: number } | null;
    payment: { id: string; reference: string; method: string; status: string; currency: string; invoice: string | null } | null;
    billing: Party;
    shipping: Party;
    taxId: string | null;
    customer: Customer | null;
    keys: { key: string; url: string; linked: boolean; card: { title: string; url: string } | null; greeting: string }[];
    missingKeys: number;
    paid: boolean;
};

type Props = { order: Order; statuses: DeliveryStatus[]; openDialog: "delivery" | null };

function Row({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex items-start justify-between gap-4 text-sm">
            <span className="shrink-0 text-muted-foreground">{label}</span>
            <span className="min-w-0 text-end break-words">{children}</span>
        </div>
    );
}

function Address({ title, party }: { title: string; party: Party }) {
    const { t } = useTranslation();
    return (
        <div className="flex flex-col gap-1 text-sm">
            <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">{title}</span>
            {Object.keys(party).length === 0 ? (
                <span className="text-muted-foreground">{t("Not provided")}</span>
            ) : (
                <>
                    {party.name && <span className="font-medium">{party.name}</span>}
                    {party.address && <span>{party.address}</span>}
                    {party.phone && <span dir="ltr">{party.phone}</span>}
                    {party.email && <span className="text-muted-foreground">{party.email}</span>}
                </>
            )}
        </div>
    );
}

export default function NfcOrder({ order, statuses, openDialog }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [deliveryOpen, setDeliveryOpen] = useState(openDialog === "delivery");
    const [generating, setGenerating] = useState(false);
    const money = (value: number) => formatCurrency(value, currency);

    const generateKeys = () =>
        router.post(route("admin.generate.nfc.keys", order.id), {}, {
            preserveScroll: true,
            onStart: () => setGenerating(true),
            onFinish: () => setGenerating(false),
        });

    return (
        <>
            <Head title={`${t("Order")} ${order.id}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <nav data-print="hide" className="flex items-center gap-1 text-sm text-muted-foreground">
                    <Link href={route("admin.dashboard")} className="hover:text-foreground">
                        {t("Dashboard")}
                    </Link>
                    <span>/</span>
                    <Link href={route("admin.orders")} className="hover:text-foreground">
                        {t("NFC card orders")}
                    </Link>
                    <span>/</span>
                    <span className="font-mono text-foreground">{order.id}</span>
                </nav>

                <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div className="flex flex-col gap-1.5">
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="font-mono text-xl font-semibold tracking-tight">{order.id}</h1>
                            {order.payment && <PaymentBadge status={order.payment.status} />}
                            <DeliveryBadge status={order.delivery} />
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {t("Placed")} {formatDate(order.date, "dd MMM yyyy, HH:mm")}
                            {order.customer && (
                                <>
                                    {" · "}
                                    <Link href={route("admin.view.customer", order.customer.id)} className="hover:underline">
                                        {order.customer.name}
                                    </Link>
                                </>
                            )}
                        </p>
                    </div>
                    <div data-print="hide" className="flex flex-wrap gap-2">
                        <Button variant="outline" onClick={() => window.print()}>
                            <Printer />
                            {t("Print")}
                        </Button>
                        {order.payment?.invoice && (
                            <Button variant="outline" asChild>
                                <a href={order.payment.invoice} target="_blank" rel="noreferrer">
                                    <FileText />
                                    {t("Invoice")}
                                </a>
                            </Button>
                        )}
                        <Button onClick={() => setDeliveryOpen(true)} disabled={!order.paid}>
                            <Truck />
                            {t("Update delivery")}
                        </Button>
                    </div>
                </div>

                {!order.paid && (
                    <Alert variant="warning" data-print="hide">
                        <AlertDescription className="text-foreground">
                            {t("This order isn't paid yet. Delivery and NFC links become available once the payment is confirmed in NFC payments.")}
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-6 xl:grid-cols-3">
                    <div className="flex flex-col gap-6 xl:col-span-2">
                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle>{t("Items")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <div className="flex items-start gap-4">
                                    {order.item.image && <img src={order.item.image} alt="" className="h-16 w-24 shrink-0 rounded-md border object-cover" />}
                                    <div className="flex min-w-0 flex-1 flex-col gap-1">
                                        <span className="font-medium">{order.item.name}</span>
                                        {order.item.description && <span className="line-clamp-2 text-sm text-muted-foreground">{order.item.description}</span>}
                                    </div>
                                    <div className="text-end text-sm tabular-nums">
                                        <div>{order.item.price !== null ? `${money(order.item.price)} × ${order.item.quantity}` : `× ${order.item.quantity}`}</div>
                                    </div>
                                </div>
                                {order.pricing && (
                                    <>
                                        <Separator />
                                        <div className="ms-auto flex w-full max-w-xs flex-col gap-1.5 text-sm">
                                            <Row label={t("Subtotal")}>{money(order.pricing.subtotal)}</Row>
                                            {order.pricing.tax > 0 && (
                                                <Row label={`${order.pricing.taxName ?? t("Tax")}${order.pricing.taxRate ? ` (${order.pricing.taxRate}%)` : ""}`}>
                                                    {money(order.pricing.tax)}
                                                </Row>
                                            )}
                                            {order.pricing.coupon && (
                                                <Row label={`${t("Coupon")} ${order.pricing.coupon}`}>
                                                    <span className="text-success">−{money(order.pricing.discount)}</span>
                                                </Row>
                                            )}
                                            <Separator className="my-1" />
                                            <Row label={t("Total")}>
                                                <span className="text-base font-semibold">{money(order.pricing.total)}</span>
                                            </Row>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <KeyRound className="size-4" />
                                    {t("NFC links")}
                                </CardTitle>
                                <CardDescription>
                                    {t("Write each link onto one NFC card. When tapped, it opens the business card linked to that key.")}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2">
                                {order.keys.length === 0 && order.missingKeys === 0 && (
                                    <p className="py-4 text-center text-sm text-muted-foreground">
                                        {order.paid ? t("No NFC links yet.") : t("NFC links are created once the order is paid.")}
                                    </p>
                                )}
                                {order.keys.map((key, index) => (
                                    <div key={key.key} className="flex flex-col gap-2 rounded-lg border p-3 sm:flex-row sm:items-center">
                                        <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                                            <span className="flex items-center gap-2 text-xs text-muted-foreground">
                                                {t("Card")} {index + 1}
                                                {key.linked ? (
                                                    <Badge variant="outline" className="border-success/30 text-success">
                                                        {t("Linked")}
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">{t("Not linked yet")}</Badge>
                                                )}
                                                {key.card && (
                                                    <a href={key.card.url} target="_blank" rel="noreferrer" className="truncate hover:underline">
                                                        {key.card.title}
                                                    </a>
                                                )}
                                            </span>
                                            <span className="truncate font-mono text-sm select-all" dir="ltr">
                                                {key.url}
                                            </span>
                                        </div>
                                        <div data-print="hide" className="flex shrink-0 gap-1">
                                            <CopyButton value={key.url} label={t("Copy link")} size="sm" variant="outline" />
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={key.greeting}>
                                                    <Mail />
                                                    {t("Greeting letter")}
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                                {order.missingKeys > 0 && (
                                    <div data-print="hide" className="flex flex-col items-start gap-2 rounded-lg border border-dashed p-3 sm:flex-row sm:items-center sm:justify-between">
                                        <span className="text-sm text-muted-foreground">
                                            {t(":count NFC links are missing for this order.").replace(":count", String(order.missingKeys))}
                                        </span>
                                        <Button size="sm" onClick={generateKeys} disabled={generating}>
                                            {generating && <Loader2 className="animate-spin" />}
                                            {t("Create NFC links")}
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle>{t("Delivery")}</CardTitle>
                                <CardAction data-print="hide">
                                    <Button variant="secondary" size="sm" onClick={() => setDeliveryOpen(true)} disabled={!order.paid}>
                                        {t("Update")}
                                    </Button>
                                </CardAction>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2.5">
                                <Row label={t("Status")}>
                                    <DeliveryBadge status={order.delivery} />
                                </Row>
                                <Row label={t("Courier")}>{order.tracking.courier || "—"}</Row>
                                <Row label={t("Tracking number")}>
                                    {order.tracking.number ? <span className="font-mono">{order.tracking.number}</span> : "—"}
                                </Row>
                                {order.tracking.message && <Row label={t("Message to customer")}>{order.tracking.message}</Row>}
                                {order.note && <Row label={t("Customer's note")}>{order.note}</Row>}
                            </CardContent>
                        </Card>

                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle>{t("Customer")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <Address title={t("Ship to")} party={order.shipping} />
                                <Address title={t("Bill to")} party={order.billing} />
                                {order.taxId && <Row label={t("Tax ID")}>{order.taxId}</Row>}
                            </CardContent>
                        </Card>

                        {order.payment && (
                            <Card className="gap-4">
                                <CardHeader>
                                    <CardTitle>{t("Payment")}</CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-2.5">
                                    <Row label={t("Status")}>
                                        <PaymentBadge status={order.payment.status} />
                                    </Row>
                                    <Row label={t("Method")}>{order.payment.method}</Row>
                                    <Row label={t("Reference")}>
                                        <span className="font-mono text-xs">{order.payment.reference}</span>
                                    </Row>
                                </CardContent>
                            </Card>
                        )}

                        {order.logo && (
                            <Card className="gap-4">
                                <CardHeader>
                                    <CardTitle>{t("Logo to print")}</CardTitle>
                                    <CardDescription>{t("Uploaded by the customer for the card.")}</CardDescription>
                                </CardHeader>
                                <CardContent className="flex flex-col items-center gap-3">
                                    <img src={order.logo} alt={t("Logo to print")} className="max-h-40 rounded-md border bg-white object-contain p-2" />
                                    <Button variant="outline" size="sm" asChild data-print="hide">
                                        <a href={order.logo} download>
                                            <Download />
                                            {t("Download logo")}
                                        </a>
                                    </Button>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            <DeliveryDialog order={order} statuses={statuses} open={deliveryOpen} onOpenChange={setDeliveryOpen} />
        </>
    );
}

function DeliveryDialog({
    order,
    statuses,
    open,
    onOpenChange,
}: {
    order: Order;
    statuses: DeliveryStatus[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { t } = useTranslation();
    const form = useForm({
        order_id: order.id,
        status: order.delivery,
        courier_partner: order.tracking.courier ?? "",
        tracking_number: order.tracking.number ?? "",
        delivery_message: order.tracking.message ?? "",
        notify: true,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.updated.order"), { preserveScroll: true, onSuccess: () => onOpenChange(false) });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <form onSubmit={submit} className="flex flex-col gap-5">
                    <DialogHeader>
                        <DialogTitle>{t("Update delivery")}</DialogTitle>
                        <DialogDescription>{t("Order")} {order.id}</DialogDescription>
                    </DialogHeader>
                    <div className="flex flex-col gap-2">
                        <Label>{t("Delivery status")}</Label>
                        <Select value={form.data.status} onValueChange={(value) => form.setData("status", value as DeliveryStatus)}>
                            <SelectTrigger className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {statuses.map((status) => (
                                    <SelectItem key={status} value={status}>
                                        {t(DELIVERY_LABELS[status])}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <FieldError message={form.errors.status} />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="courier_partner">{t("Courier")}</Label>
                            <Input
                                id="courier_partner"
                                value={form.data.courier_partner}
                                onChange={(event) => form.setData("courier_partner", event.target.value)}
                                placeholder={t("e.g., DHL, FedEx, UPS")}
                            />
                            <FieldError message={form.errors.courier_partner} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="tracking_number">{t("Tracking number")}</Label>
                            <Input
                                id="tracking_number"
                                value={form.data.tracking_number}
                                onChange={(event) => form.setData("tracking_number", event.target.value)}
                                placeholder={t("e.g., TRK123456789")}
                            />
                            <FieldError message={form.errors.tracking_number} />
                        </div>
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="delivery_message">{t("Message to customer")}</Label>
                        <Textarea
                            id="delivery_message"
                            value={form.data.delivery_message}
                            onChange={(event) => form.setData("delivery_message", event.target.value)}
                            placeholder={t("e.g., Your cards have been dispatched and should arrive in 3–5 days.")}
                            className="min-h-20"
                        />
                        <FieldError message={form.errors.delivery_message} />
                    </div>
                    <label className="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-3">
                        <span className="flex flex-col gap-0.5">
                            <span className="text-sm font-medium">{t("Email the customer")}</span>
                            <span className="text-xs text-muted-foreground">{t("Send the delivery update email with these details.")}</span>
                        </span>
                        <Switch checked={form.data.notify} onCheckedChange={(checked) => form.setData("notify", checked)} />
                    </label>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            {t("Cancel")}
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {t("Save")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

