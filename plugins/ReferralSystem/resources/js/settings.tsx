/*
 | Referral program — admin settings.
 | Built into ../../dist with `npm run plugin:build -- ReferralSystem` and shipped in the zip.
 */
import { Head, Link, useForm } from "@inertiajs/react";
import { Gift, Loader2, Settings2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Label, PageHeader, Switch } from "@gobiz/admin";

export default function ReferralSystemSettings({ enabled }: { enabled: boolean }) {
    const { t } = useTranslation();
    const form = useForm({ enable_referral_system: enabled });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ enable_referral_system: current.enable_referral_system ? 1 : 0 }));
        form.post(route("admin.plugin.update.status.referral.system"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Referral program")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Referral program")}
                    description={t("Customers earn a commission when people they refer buy a plan.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Referral program") },
                    ]}
                    actions={
                        <Button type="button" variant="secondary" asChild>
                            <Link href={route("admin.referral.system.configuration")}>
                                <Settings2 />
                                {t("Commission and payouts")}
                            </Link>
                        </Button>
                    }
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Gift className="size-5 text-primary" />
                                {t("Referral program")}
                            </CardTitle>
                            <CardDescription>{t("Set the commission rate and minimum payout in Commission and payouts.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="enable_referral_system">{t("Turn on the referral program")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("Customers get a referral link and can request payouts of what they earn.")}</p>
                                </div>
                                <Switch id="enable_referral_system" checked={form.data.enable_referral_system} onCheckedChange={(checked) => form.setData("enable_referral_system", checked)} />
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
