import { Head, useForm } from "@inertiajs/react";
import { CheckCircle2, Loader2, Save } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";

type Props = { settings: { hasKey: boolean; from: string; region: "us" | "eu"; domain: string | null } };

export default function Mailgun({ settings }: Props) {
    const { t } = useTranslation();
    const form = useForm({ mailgun_api_key: "", mailgun_from_email: settings.from, mailgun_region: settings.region });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.marketing.mailgun.update"), { preserveScroll: true, onSuccess: () => form.reset("mailgun_api_key") });
    };

    return (
        <>
            <Head title={t("Mailgun settings")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Mailgun settings")}
                    description={t("Email campaigns are sent through your Mailgun account.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Mailgun settings") }]}
                />
                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {t("Connection")}
                            {settings.hasKey && <span className="flex items-center gap-1 text-xs font-normal text-success"><CheckCircle2 className="size-3.5" />{t("API key saved")}</span>}
                        </CardTitle>
                        <CardDescription>
                            {t("Sending domain")}: <span className="font-mono">{settings.domain || t("not set (MAIN_DOMAIN in .env)")}</span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="key">{t("API key")}</Label>
                            <Input id="key" type="password" autoComplete="off" value={form.data.mailgun_api_key} onChange={(e) => form.setData("mailgun_api_key", e.target.value)} placeholder={settings.hasKey ? t("Leave blank to keep the saved key") : "key-..."} required={!settings.hasKey} />
                            <FieldError message={form.errors.mailgun_api_key} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="from">{t("Sender email")}</Label>
                            <Input id="from" type="email" value={form.data.mailgun_from_email} onChange={(e) => form.setData("mailgun_from_email", e.target.value)} placeholder="news@yourdomain.com" required />
                            <FieldError message={form.errors.mailgun_from_email} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label>{t("Region")}</Label>
                            <Tabs value={form.data.mailgun_region} onValueChange={(v) => form.setData("mailgun_region", v as "us" | "eu")}>
                                <TabsList>
                                    <TabsTrigger value="us">{t("United States")}</TabsTrigger>
                                    <TabsTrigger value="eu">{t("Europe")}</TabsTrigger>
                                </TabsList>
                            </Tabs>
                            <p className="text-xs text-muted-foreground">{t("Use the region where your Mailgun domain was created.")}</p>
                        </div>
                    </CardContent>
                    <CardFooter className="justify-end border-t [.border-t]:pt-6">
                        <Button type="submit" disabled={form.processing || !form.isDirty}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {t("Save settings")}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </>
    );
}
