/*
 | Maintenance mode — admin settings.
 | Built into ../../dist with `npm run plugin:build -- MaintenanceMode` and shipped in the zip.
 */
import { Head, router } from "@inertiajs/react";
import { Construction, KeyRound } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Alert, AlertDescription, AlertTitle, Card, CardContent, CardDescription, CardHeader, CardTitle, ConfirmDialog, CopyButton, Label, PageHeader, Switch } from "@gobiz/admin";

type Props = { enabled: boolean; bypassUrl: string | null };

export default function MaintenanceModeSettings({ enabled, bypassUrl }: Props) {
    const { t } = useTranslation();
    const [confirm, setConfirm] = useState<boolean | null>(null);
    const [processing, setProcessing] = useState(false);

    const apply = () =>
        router.post(route("admin.plugin.maintenance-mode.update"), { maintenance_mode: confirm ? 1 : 0 }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setConfirm(null);
            },
        });

    return (
        <>
            <Head title={t("Maintenance mode")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Maintenance mode")}
                    description={t("Take your website offline while you update it. Visitors see a short “back soon” page.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Maintenance mode") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    {enabled && (
                        <Alert>
                            <Construction />
                            <AlertTitle>{t("Your website is in maintenance mode")}</AlertTitle>
                            <AlertDescription>{t("Visitors and customers can't use the site until you turn it off.")}</AlertDescription>
                        </Alert>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Status")}</CardTitle>
                            <CardDescription>{t("Changes apply straight away.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="maintenance_mode">{t("Maintenance mode")}</Label>
                                    <p className="text-sm text-muted-foreground">{enabled ? t("On — the website is offline.") : t("Off — the website is live.")}</p>
                                </div>
                                <Switch id="maintenance_mode" checked={enabled} disabled={processing} onCheckedChange={(checked) => setConfirm(checked)} />
                            </div>

                            {bypassUrl && (
                                <div className="flex flex-col gap-2 rounded-md border bg-muted/30 p-3">
                                    <Label className="flex items-center gap-2">
                                        <KeyRound className="size-4 text-muted-foreground" />
                                        {t("Bypass link")}
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <code className="min-w-0 flex-1 truncate rounded-md border bg-background px-3 py-2 font-mono text-xs">{bypassUrl}</code>
                                        <CopyButton value={bypassUrl} />
                                    </div>
                                    <p className="text-xs text-muted-foreground">{t("Open this link to use the site while it's offline. Share it only with your team.")}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmDialog
                open={confirm !== null}
                onOpenChange={(open) => !open && setConfirm(null)}
                title={confirm ? t("Take the website offline?") : t("Bring the website back online?")}
                description={
                    confirm
                        ? t("Visitors and customers will see the maintenance page. You'll be taken to the site with a bypass link so you can keep working.")
                        : t("Everyone can use the website again.")
                }
                confirmLabel={confirm ? t("Turn on maintenance") : t("Go live")}
                destructive={!!confirm}
                processing={processing}
                onConfirm={apply}
            />
        </>
    );
}
