import { Head, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Separator } from "@admin/components/ui/separator";
import { Textarea } from "@admin/components/ui/textarea";
import { cn } from "@admin/lib/utils";

type Values = {
    invoice_prefix: string;
    invoice_name: string;
    invoice_email: string;
    invoice_phone: string;
    invoice_address: string;
    invoice_city: string;
    invoice_state: string;
    invoice_zipcode: string;
    invoice_country: string;
    tax_name: string;
    tax_number: string;
    tax_value: string;
    invoice_footer: string;
    email_heading: string;
    email_footer: string;
};

type Key = keyof Values;

export default function InvoiceSettings({ values }: { values: Values }) {
    const { t } = useTranslation();
    const form = useForm<Values>(values);
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.tax.setting"), { preserveScroll: true });
    };

    const field = (key: Key, label: string, props: React.ComponentProps<typeof Input> = {}, className?: string) => (
        <div className={cn("flex flex-col gap-2", className)}>
            <Label htmlFor={key}>{label}</Label>
            <Input id={key} value={data[key]} onChange={(event) => setData(key, event.target.value)} aria-invalid={!!errors[key]} {...props} />
            <FieldError message={errors[key]} />
        </div>
    );

    const textarea = (key: Key, label: string, hint: string) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Textarea id={key} value={data[key]} onChange={(event) => setData(key, event.target.value)} className="min-h-20" aria-invalid={!!errors[key]} />
            <p className="text-xs text-muted-foreground">{hint}</p>
            <FieldError message={errors[key]} />
        </div>
    );

    const rate = Number.parseFloat(data.tax_value) || 0;
    const location = [data.invoice_city, data.invoice_state, data.invoice_zipcode].filter(Boolean).join(", ");

    return (
        <>
            <Head title={t("Invoice and tax")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Invoice and tax")}
                    description={t("Your business details, tax and the text shown on invoices and invoice emails.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Invoice and tax") }]}
                />

                <div className="grid gap-6 xl:grid-cols-[1fr_22rem]">
                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Business details")}</CardTitle>
                                <CardDescription>{t("Shown as the seller on every invoice.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2">
                                {field("invoice_name", t("Business name"), { required: true }, "sm:col-span-2")}
                                {field("invoice_email", t("Email"), { type: "email", required: true })}
                                {field("invoice_phone", t("Phone"), { type: "tel" })}
                                {field("invoice_address", t("Address"), {}, "sm:col-span-2")}
                                {field("invoice_city", t("City"))}
                                {field("invoice_state", t("State or province"))}
                                {field("invoice_zipcode", t("ZIP or postal code"))}
                                {field("invoice_country", t("Country"))}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Tax")}</CardTitle>
                                <CardDescription>{t("Added on top of the price at checkout. Set the rate to 0 to charge no tax.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-3">
                                {field("tax_name", t("Tax name"), { placeholder: t("e.g., VAT or GST") })}
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="tax_value">{t("Tax rate")}</Label>
                                    <div className="relative">
                                        <Input
                                            id="tax_value"
                                            type="number"
                                            min={0}
                                            max={100}
                                            step="0.01"
                                            value={data.tax_value}
                                            onChange={(event) => setData("tax_value", event.target.value)}
                                            className="pe-8"
                                            aria-invalid={!!errors.tax_value}
                                        />
                                        <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">%</span>
                                    </div>
                                    <FieldError message={errors.tax_value} />
                                </div>
                                {field("tax_number", t("Tax ID"), { placeholder: t("Your VAT or GST number") })}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Invoice text")}</CardTitle>
                                <CardDescription>{t("Numbering and the messages on invoices and invoice emails.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2 sm:max-w-xs">
                                    <Label htmlFor="invoice_prefix">{t("Invoice number prefix")}</Label>
                                    <Input id="invoice_prefix" value={data.invoice_prefix} onChange={(event) => setData("invoice_prefix", event.target.value)} aria-invalid={!!errors.invoice_prefix} />
                                    <p className="text-xs text-muted-foreground">
                                        {t("Invoices look like :example. Changing the prefix starts numbering again from 1.").replace(":example", `${data.invoice_prefix}1042`)}
                                    </p>
                                    <FieldError message={errors.invoice_prefix} />
                                </div>
                                {textarea("invoice_footer", t("Invoice footer"), t("Printed at the bottom of every invoice."))}
                                <Separator />
                                {textarea("email_heading", t("Email heading"), t("The first line of the invoice email."))}
                                {textarea("email_footer", t("Email footer"), t("Shown at the end of the invoice email."))}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="xl:sticky xl:top-20 xl:self-start">
                        <Card className="gap-4">
                            <CardHeader>
                                <CardDescription>{t("Preview")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4 text-sm">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex min-w-0 flex-col">
                                        <span className="truncate font-semibold">{data.invoice_name || t("Business name")}</span>
                                        {data.invoice_address && <span className="text-muted-foreground">{data.invoice_address}</span>}
                                        {location && <span className="text-muted-foreground">{location}</span>}
                                        {data.invoice_country && <span className="text-muted-foreground">{data.invoice_country}</span>}
                                        {data.invoice_email && <span className="truncate text-muted-foreground">{data.invoice_email}</span>}
                                        {data.invoice_phone && <span className="text-muted-foreground">{data.invoice_phone}</span>}
                                        {data.tax_number && (
                                            <span className="text-muted-foreground">
                                                {t("Tax ID")}: {data.tax_number}
                                            </span>
                                        )}
                                    </div>
                                    <span className="shrink-0 font-mono text-xs text-muted-foreground">#{data.invoice_prefix}1042</span>
                                </div>
                                <Separator />
                                <dl className="flex flex-col gap-1.5 tabular-nums">
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">{t("Subtotal")}</dt>
                                        <dd>100.00</dd>
                                    </div>
                                    {rate > 0 && (
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">
                                                {data.tax_name || t("Tax")} ({rate}%)
                                            </dt>
                                            <dd>{rate.toFixed(2)}</dd>
                                        </div>
                                    )}
                                    <div className="flex justify-between font-semibold">
                                        <dt>{t("Total")}</dt>
                                        <dd>{(100 + rate).toFixed(2)}</dd>
                                    </div>
                                </dl>
                                {data.invoice_footer && <p className="border-t pt-3 text-xs text-muted-foreground">{data.invoice_footer}</p>}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button type="submit" disabled={form.processing || !form.isDirty}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save changes")}
                    </Button>
                </div>
            </form>
        </>
    );
}
