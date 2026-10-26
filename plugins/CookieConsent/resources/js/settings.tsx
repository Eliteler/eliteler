/*
 | Cookie Consent — admin settings.
 | Built into ../../dist with `npm run plugin:build -- CookieConsent` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Cookie, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Label, PageHeader, Switch } from "@gobiz/admin";

export default function CookieConsentSettings({ enabled }: { enabled: boolean }) {
    const { t } = useTranslation();
    const form = useForm({ cookie_consent: enabled });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ cookie_consent: current.cookie_consent ? 1 : 0 }));
        form.post(route("admin.plugin.cookie-consent.update"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Cookie consent")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Cookie consent")}
                    description={t("Ask website visitors to accept cookies before analytics and ads load.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Cookie consent") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Cookie className="size-5 text-primary" />
                                {t("Cookie banner")}
                            </CardTitle>
                            <CardDescription>{t("Required in many countries (for example under GDPR) when you use Google Analytics or AdSense.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="cookie_consent">{t("Show the cookie consent banner")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("Visitors see it on their first visit and can accept cookies.")}</p>
                                </div>
                                <Switch id="cookie_consent" checked={form.data.cookie_consent} onCheckedChange={(checked) => form.setData("cookie_consent", checked)} />
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
