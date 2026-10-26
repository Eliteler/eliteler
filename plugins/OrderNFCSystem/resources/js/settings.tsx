/*
 | NFC card ordering — admin settings.
 | Built into ../../dist with `npm run plugin:build -- OrderNFCSystem` and shipped in the zip.
 */
import { Head, Link, useForm } from "@inertiajs/react";
import { Loader2, Nfc } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Switch } from "@gobiz/admin";

type Values = { enable_disable_nfc_card_order: boolean; enable_disable_nfc_card_order_website: boolean; nfc_character_limit: string };

export default function OrderNfcSettings({ values }: { values: Values }) {
    const { t } = useTranslation();
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

    return (
        <>
            <Head title={t("NFC card ordering")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("NFC card ordering")}
                    description={t("Let customers order physical NFC cards linked to their business card.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("NFC card ordering") },
                    ]}
                    actions={
                        <Button type="button" variant="secondary" asChild>
                            <Link href={route("admin.designs")}>
                                <Nfc />
                                {t("NFC card designs")}
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
                            {toggle("enable_disable_nfc_card_order", t("Let customers order NFC cards"), t("Customers can order cards from their dashboard."))}
                            {toggle("enable_disable_nfc_card_order_website", t("Show NFC cards on the website"), t("Adds the NFC cards section to your public website. Needs ordering turned on."))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Activation keys")}</CardTitle>
                            <CardDescription>{t("Each card comes with a key the customer types in to activate it. Longer keys are harder to guess.")}</CardDescription>
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
