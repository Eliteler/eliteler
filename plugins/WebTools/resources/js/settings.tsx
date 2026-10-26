/*
 | Web tools — admin settings.
 | Built into ../../dist with `npm run plugin:build -- WebTools` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2, Wrench } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Label, PageHeader, Switch } from "@gobiz/admin";

export default function WebToolsSettings({ enabled }: { enabled: boolean }) {
    const { t } = useTranslation();
    const form = useForm({ enable_webtools: enabled });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ enable_webtools: current.enable_webtools ? 1 : 0 }));
        form.post(route("admin.plugin.update.webtools"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Web tools")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Web tools")}
                    description={t("Free tools on your website — QR code maker, password generators, HTML/CSS/JS minifiers, DNS and IP lookups and more.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Web tools") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Wrench className="size-5 text-primary" />
                                {t("Web tools")}
                            </CardTitle>
                            <CardDescription>{t("Free tools bring visitors to your website from search engines.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="enable_webtools">{t("Show web tools on the website")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("Adds a Web Tools menu to your public website. When off, the tool pages are hidden.")}</p>
                                </div>
                                <Switch id="enable_webtools" checked={form.data.enable_webtools} onCheckedChange={(checked) => form.setData("enable_webtools", checked)} />
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
