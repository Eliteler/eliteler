import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Eye, EyeOff, Loader2, Save } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Switch } from "@admin/components/ui/switch";
import { useHideRevenue } from "@admin/hooks/use-hide-revenue";
import { formatCurrency } from "@admin/lib/format";

import type { CustomerState, PlanOption } from "./types";

type Props = {
    customer: { id: string; name: string; email: string; status: 0 | 1; state: CustomerState } | null;
    plans: PlanOption[];
};

export default function CustomerForm({ customer, plans }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const hideRevenue = useHideRevenue();
    const editing = customer !== null;
    const [showPassword, setShowPassword] = useState(false);

    const form = useForm({
        user_id: customer?.id ?? "",
        full_name: customer?.name ?? "",
        email: customer?.email ?? "",
        password: "",
        plan_id: "",
        status: String(customer?.status ?? 1),
        welcome_email: !editing,
        reset_password: false,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, plan_id: data.plan_id === "none" ? "" : data.plan_id }));
        form.post(editing ? route("admin.update.customer") : route("admin.save.customer"), { preserveScroll: true });
    };

    const lockedState = customer && (customer.state === "suspended" || customer.state === "deleted");
    const backHref = editing ? route("admin.view.customer", customer.id) : route("admin.customers", { status: "all" });

    return (
        <>
            <Head title={editing ? `${t("Edit")} ${customer.name}` : t("Add customer")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={editing ? customer.name : t("Add customer")}
                    description={
                        editing
                            ? t("Update the customer's sign-in details and account status.")
                            : t("Create an account on behalf of a customer.")
                    }
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Customers"), href: route("admin.customers", { status: "all" }) },
                        ...(editing ? [{ label: customer.name, href: route("admin.view.customer", customer.id) }] : []),
                        { label: editing ? t("Edit") : t("Add") },
                    ]}
                />

                <div className="grid max-w-5xl gap-6 lg:grid-cols-3">
                    <div className="flex flex-col gap-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Account")}</CardTitle>
                                <CardDescription>{t("Used to sign in to the customer dashboard.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="full_name">{t("Full name")}</Label>
                                    <Input
                                        id="full_name"
                                        value={form.data.full_name}
                                        onChange={(event) => form.setData("full_name", event.target.value)}
                                        placeholder={t("e.g., John Doe")}
                                        aria-invalid={!!form.errors.full_name}
                                        autoComplete="off"
                                        required
                                    />
                                    <FieldError message={form.errors.full_name} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="email">{t("Email")}</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) => form.setData("email", event.target.value)}
                                        placeholder={t("e.g., john@example.com")}
                                        aria-invalid={!!form.errors.email}
                                        autoComplete="off"
                                        required
                                    />
                                    <FieldError message={form.errors.email} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="password">{editing ? t("New password") : t("Password")}</Label>
                                    <div className="relative">
                                        <Input
                                            id="password"
                                            type={showPassword ? "text" : "password"}
                                            value={form.data.password}
                                            onChange={(event) => form.setData("password", event.target.value)}
                                            placeholder={editing ? t("Leave blank to keep the current password") : t("At least 6 characters")}
                                            aria-invalid={!!form.errors.password}
                                            autoComplete="new-password"
                                            className="pe-10"
                                            required={!editing}
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            className="absolute end-1 top-1/2 -translate-y-1/2"
                                            onClick={() => setShowPassword((value) => !value)}
                                            aria-label={showPassword ? t("Hide password") : t("Show password")}
                                        >
                                            {showPassword ? <EyeOff /> : <Eye />}
                                        </Button>
                                    </div>
                                    <FieldError message={form.errors.password} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Emails")}</CardTitle>
                                <CardDescription>{t("Sent when you save.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                {(
                                    [
                                        ["welcome_email", t("Welcome email"), t("Send the welcome email template to the customer.")],
                                        ["reset_password", t("Password setup link"), t("Email a link so the customer can set their own password.")],
                                    ] as const
                                ).map(([field, title, hint]) => (
                                    <label key={field} className="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-3">
                                        <span className="flex flex-col gap-0.5">
                                            <span className="text-sm font-medium">{title}</span>
                                            <span className="text-xs text-muted-foreground">{hint}</span>
                                        </span>
                                        <Switch checked={form.data[field]} onCheckedChange={(checked) => form.setData(field, checked)} />
                                    </label>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Status")}</CardTitle>
                                <CardDescription>
                                    {lockedState
                                        ? t("Use the actions on the customer page to change a suspended or deleted account.")
                                        : t("Inactive customers can't use their business cards.")}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Select value={form.data.status} onValueChange={(status) => form.setData("status", status)} disabled={!!lockedState}>
                                    <SelectTrigger className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">{t("Active")}</SelectItem>
                                        <SelectItem value="0">{t("Inactive")}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.status} />
                            </CardContent>
                        </Card>

                        {!editing && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>{t("Plan")}</CardTitle>
                                    {!hideRevenue && <CardDescription>{t("Paid plans are recorded as an offline payment.")}</CardDescription>}
                                </CardHeader>
                                <CardContent className="flex flex-col gap-2">
                                    <Select value={form.data.plan_id || "none"} onValueChange={(plan) => form.setData("plan_id", plan)}>
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">{t("No plan")}</SelectItem>
                                            {plans.map((plan) => (
                                                <SelectItem key={plan.id} value={plan.id}>
                                                    {plan.name}
                                                    {plan.price !== null && ` · ${plan.price > 0 ? formatCurrency(plan.price, currency) : t("Free")}`}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FieldError message={form.errors.plan_id} />
                                </CardContent>
                            </Card>
                        )}

                        <div className="flex gap-2">
                            <Button variant="outline" asChild className="flex-1">
                                <Link href={backHref}>{t("Cancel")}</Link>
                            </Button>
                            <Button type="submit" className="flex-1" disabled={form.processing}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                {editing ? t("Save changes") : t("Create customer")}
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        </>
    );
}
