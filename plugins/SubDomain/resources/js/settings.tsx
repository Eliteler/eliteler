/*
 | Subdomains — admin settings.
 | Built into ../../dist with `npm run plugin:build -- SubDomain` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Globe, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Label, PageHeader, Switch } from "@gobiz/admin";

export default function SubDomainSettings({ enabled, example }: { enabled: boolean; example: string }) {
    const { t } = useTranslation();
    const form = useForm({ enable_subdomain: enabled });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ enable_subdomain: current.enable_subdomain ? 1 : 0 }));
        form.post(route("admin.plugin.sub-domain.update"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Subdomains")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Subdomains")}
                    description={t("Give each business card and store its own web address on your domain.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Subdomains") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Globe className="size-5 text-primary" />
                                {t("Subdomains")}
                            </CardTitle>
                            <CardDescription>{t("Needs a wildcard DNS record (*.yourdomain) pointing to this server, and a wildcard SSL certificate.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="enable_subdomain">{t("Let customers use subdomains")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("Cards and stores can open at an address like :example.").replace(":example", example)}</p>
                                </div>
                                <Switch id="enable_subdomain" checked={form.data.enable_subdomain} onCheckedChange={(checked) => form.setData("enable_subdomain", checked)} />
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
