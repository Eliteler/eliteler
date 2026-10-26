/*
 | Google AdSense — admin settings.
 | Built into ../../dist with `npm run plugin:build -- GoogleAdSense` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader } from "@gobiz/admin";

export default function GoogleAdSenseSettings({ google_adsense_code }: { google_adsense_code: string }) {
    const { t } = useTranslation();
    const form = useForm({ google_adsense_code });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.google_adsense_settings.update"), { preserveScroll: true });
    };

    return (
        <>
            <Head title="Google AdSense" />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title="Google AdSense"
                    description={t("Show Google ads on your public website to earn money.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: "Google AdSense" },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("AdSense account")}</CardTitle>
                            <CardDescription>{t("Find your publisher ID in AdSense under Account → Settings → Account information.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-col gap-2 sm:max-w-sm">
                                <Label htmlFor="google_adsense_code">{t("Publisher ID")}</Label>
                                <Input
                                    id="google_adsense_code"
                                    value={form.data.google_adsense_code}
                                    onChange={(event) => form.setData("google_adsense_code", event.target.value)}
                                    placeholder="ca-pub-1234567890123456"
                                    className="font-mono text-sm"
                                    aria-invalid={!!form.errors.google_adsense_code}
                                />
                                <p className="text-xs text-muted-foreground">{t("Leave blank to stop showing ads.")}</p>
                                <FieldError message={form.errors.google_adsense_code} />
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
