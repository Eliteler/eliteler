/*
 | Google Analytics — admin settings.
 | Built into ../../dist with `npm run plugin:build -- GoogleAnalytics` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader } from "@gobiz/admin";

export default function GoogleAnalyticsSettings({ google_analytics_id }: { google_analytics_id: string }) {
    const { t } = useTranslation();
    const form = useForm({ google_analytics_id });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.google_analytics_settings.update"), { preserveScroll: true });
    };

    return (
        <>
            <Head title="Google Analytics" />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title="Google Analytics"
                    description={t("See how visitors find and use your public website.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: "Google Analytics" },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Analytics property")}</CardTitle>
                            <CardDescription>{t("In Google Analytics, open Admin → Data streams → your web stream and copy the measurement ID.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-col gap-2 sm:max-w-sm">
                                <Label htmlFor="google_analytics_id">{t("Measurement ID")}</Label>
                                <Input
                                    id="google_analytics_id"
                                    value={form.data.google_analytics_id}
                                    onChange={(event) => form.setData("google_analytics_id", event.target.value)}
                                    placeholder="G-XXXXXXXXXX"
                                    className="font-mono text-sm"
                                    aria-invalid={!!form.errors.google_analytics_id}
                                />
                                <p className="text-xs text-muted-foreground">{t("Leave blank to turn tracking off. Visitors are only tracked after they accept cookies, if the cookie banner is on.")}</p>
                                <FieldError message={form.errors.google_analytics_id} />
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
