import { Head, useForm } from "@inertiajs/react";
import { CheckCircle2, ExternalLink, Loader2, Save } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";

const GUIDE = "https://nativecode.in/docs/gobiz/setup/how-to-get-pusher-beams-instance-id-and-secret-key.html";

export default function PushSettings({ settings }: { settings: { enabled: boolean; instanceId: string; hasSecret: boolean } }) {
    const { t } = useTranslation();
    const form = useForm({ enable_beams: settings.enabled, beams_instance_id: settings.instanceId, beams_secret_key: "" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.marketing.pusher.update"), { preserveScroll: true, onSuccess: () => form.reset("beams_secret_key") });
    };

    return (
        <>
            <Head title={t("Pusher settings")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Pusher settings")}
                    description={t("Web notifications are delivered through Pusher Beams.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Pusher settings") }]}
                    actions={
                        <Button variant="outline" asChild>
                            <a href={GUIDE} target="_blank" rel="noreferrer">
                                <ExternalLink />
                                {t("Setup guide")}
                            </a>
                        </Button>
                    }
                />
                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {t("Pusher Beams")}
                            {settings.hasSecret && settings.instanceId && <span className="flex items-center gap-1 text-xs font-normal text-success"><CheckCircle2 className="size-3.5" />{t("Connected")}</span>}
                        </CardTitle>
                        <CardDescription>{t("Find these in your Pusher Beams dashboard under Keys.")}</CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        <label className="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-3">
                            <span className="flex flex-col gap-0.5">
                                <span className="text-sm font-medium">{t("Ask website visitors to subscribe")}</span>
                                <span className="text-xs text-muted-foreground">{t("Shows the browser's notification prompt on your website.")}</span>
                            </span>
                            <Switch checked={form.data.enable_beams} onCheckedChange={(v) => form.setData("enable_beams", v)} />
                        </label>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="instance">{t("Instance ID")}</Label>
                            <Input id="instance" value={form.data.beams_instance_id} onChange={(e) => form.setData("beams_instance_id", e.target.value.trim())} className="font-mono" dir="ltr" required />
                            <FieldError message={form.errors.beams_instance_id} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="secret">{t("Secret key")}</Label>
                            <Input id="secret" type="password" autoComplete="off" value={form.data.beams_secret_key} onChange={(e) => form.setData("beams_secret_key", e.target.value.trim())} placeholder={settings.hasSecret ? t("Leave blank to keep the saved key") : ""} className="font-mono" dir="ltr" required={!settings.hasSecret} />
                            <FieldError message={form.errors.beams_secret_key} />
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
