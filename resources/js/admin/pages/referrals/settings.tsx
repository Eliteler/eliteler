import { Head, useForm, usePage } from "@inertiajs/react";
import { Loader2, Save } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatCurrency } from "@admin/lib/format";

type Props = { settings: { type: "percentage" | "fixed"; amount: number; minimum: number } };

export default function ReferralSettings({ settings }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const form = useForm({
        referral_commission_type: settings.type,
        referral_commission_amount: String(settings.amount),
        minimum_withdrawal_amount: String(settings.minimum),
    });
    const percentage = form.data.referral_commission_type === "percentage";
    const amount = Number(form.data.referral_commission_amount) || 0;
    const example = percentage ? formatCurrency((100 * amount) / 100, currency) : formatCurrency(amount, currency);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.referral.system.configuration"), { preserveScroll: true, onSuccess: () => form.setDefaults() });
    };

    return (
        <>
            <Head title={t("Referral settings")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Referral settings")}
                    description={t("How much referrers earn when someone they referred buys a plan.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Referrals"), href: route("admin.referrals") },
                        { label: t("Settings") },
                    ]}
                />
                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle>{t("Commission")}</CardTitle>
                        <CardDescription>{t("Earned once, on the referred customer's first paid plan. Changes apply to new referrals.")}</CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        <Tabs value={form.data.referral_commission_type} onValueChange={(value) => form.setData("referral_commission_type", value as "percentage" | "fixed")}>
                            <TabsList>
                                <TabsTrigger value="percentage">{t("Percentage of the payment")}</TabsTrigger>
                                <TabsTrigger value="fixed">{t("Fixed amount")}</TabsTrigger>
                            </TabsList>
                        </Tabs>
                        <div className="grid gap-5 sm:grid-cols-2">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="amount">{percentage ? t("Commission (%)") : t("Commission")}</Label>
                                <div className="relative">
                                    <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{percentage ? "%" : currency.symbol}</span>
                                    <Input id="amount" type="number" min={0} max={percentage ? 100 : undefined} step="0.01" value={form.data.referral_commission_amount} onChange={(e) => form.setData("referral_commission_amount", e.target.value)} className="ps-8" required />
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {percentage
                                        ? t("Example: on a :price payment, the referrer earns :amount.").replace(":price", formatCurrency(100, currency)).replace(":amount", example)
                                        : t("The referrer earns :amount per paying customer.").replace(":amount", example)}
                                </p>
                                <FieldError message={form.errors.referral_commission_amount} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="minimum">{t("Minimum payout")}</Label>
                                <div className="relative">
                                    <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{currency.symbol}</span>
                                    <Input id="minimum" type="number" min={0} step="0.01" value={form.data.minimum_withdrawal_amount} onChange={(e) => form.setData("minimum_withdrawal_amount", e.target.value)} className="ps-8" required />
                                </div>
                                <p className="text-xs text-muted-foreground">{t("Referrers can request a payout once their balance reaches this amount.")}</p>
                                <FieldError message={form.errors.minimum_withdrawal_amount} />
                            </div>
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
