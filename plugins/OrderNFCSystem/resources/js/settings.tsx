/*
 | Smart Products ordering — admin settings.
 | Built into ../../dist with `npm run plugin:build -- OrderNFCSystem` and shipped in the zip.
 */
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Loader2, Nfc } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Switch } from "@gobiz/admin";

type Values = {
    enable_disable_nfc_card_order: boolean;
    enable_disable_nfc_card_order_website: boolean;
    nfc_character_limit: string;
    smart_product_shipping_fee: string;
    smart_product_free_shipping_above: string;
};

type Currency = { symbol?: string };

export default function OrderNfcSettings({ values }: { values: Values }) {
    const { t } = useTranslation();
    const { currency } = usePage().props as { currency?: Currency };
    const form = useForm(values);
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            enable_disable_nfc_card_order: current.enable_disable_nfc_card_order ? 1 : 0,
            enable_disable_nfc_card_order_website: current.enable_disable_nfc_card_order_website ? 1 : 0,
        }));
        form.post(route("admin.plugin.update.status.nfc.cards.order"), { preserveScroll: true });
    };

    const toggle = (id: "enable_disable_nfc_card_order" | "enable_disable_nfc_card_order_website", label: string, description: string) => (
        <div className="flex items-start justify-between gap-4">
            <div className="flex flex-col gap-1">
                <Label htmlFor={id}>{label}</Label>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
            <Switch id={id} checked={data[id]} onCheckedChange={(checked) => setData(id, checked)} />
        </div>
    );

    const money = (id: "smart_product_shipping_fee" | "smart_product_free_shipping_above", label: string, hint: string) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={id}>{label}</Label>
            <div className="relative">
                {currency?.symbol && (
                    <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{currency.symbol}</span>
                )}
                <Input
                    id={id}
                    type="number"
                    min={0}
                    step="0.01"
                    value={data[id]}
                    onChange={(event) => setData(id, event.target.value)}
                    aria-invalid={!!errors[id]}
                    className={currency?.symbol ? "ps-8" : undefined}
                    required
                />
            </div>
            <p className="text-xs text-muted-foreground">{hint}</p>
            <FieldError message={errors[id]} />
        </div>
    );

    return (
        <>
            <Head title={t("Smart Products")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Smart Products")}
                    description={t("Sell NFC cards, stands, tags and other smart products to your customers.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Smart Products") },
                    ]}
                    actions={
                        <Button type="button" variant="secondary" asChild>
                            <Link href={route("admin.designs")}>
                                <Nfc />
                                {t("Products")}
                            </Link>
                        </Button>
                    }
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Ordering")}</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            {toggle("enable_disable_nfc_card_order", t("Let customers order Smart Products"), t("Customers can order products from their dashboard."))}
                            {toggle("enable_disable_nfc_card_order_website", t("Show Smart Products on the website"), t("Adds the Smart Products section to your public website. Needs ordering turned on."))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Shipping")}</CardTitle>
                            <CardDescription>{t("A flat fee added once to every order, whatever the quantity. Set it to 0 for free shipping.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            {money("smart_product_shipping_fee", t("Shipping fee"), t("0 = free shipping."))}
                            {money("smart_product_free_shipping_above", t("Free shipping for orders above"), t("Order subtotal before tax. 0 = never free."))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Activation keys")}</CardTitle>
                            <CardDescription>{t("Each product with an NFC chip comes with a key the customer types in to activate it. Longer keys are harder to guess.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-col gap-2 sm:max-w-xs">
                                <Label htmlFor="nfc_character_limit">{t("Key length")}</Label>
                                <div className="flex items-center gap-2">
                                    <Input
                                        id="nfc_character_limit"
                                        type="number"
                                        min={6}
                                        max={64}
                                        value={data.nfc_character_limit}
                                        onChange={(event) => setData("nfc_character_limit", event.target.value)}
                                        aria-invalid={!!errors.nfc_character_limit}
                                        required
                                    />
                                    <span className="text-sm text-muted-foreground">{t("characters")}</span>
                                </div>
                                <p className="text-xs text-muted-foreground">{t("Between 6 and 64. Applies to keys created from now on.")}</p>
                                <FieldError message={errors.nfc_character_limit} />
                            </div>
                        </CardContent>
                    </Card>
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
