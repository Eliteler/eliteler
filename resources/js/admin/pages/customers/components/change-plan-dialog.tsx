import { useForm, usePage } from "@inertiajs/react";
import { AlertTriangle, Check, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import type { ActionTarget, PlanOption } from "../types";

export function ChangePlanDialog({
    customer,
    plans,
    open,
    onOpenChange,
}: {
    customer: ActionTarget;
    plans: PlanOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const current = customer.plan;
    const form = useForm({ user_id: customer.id, plan_id: current?.id ?? "" });

    const selected = plans.find((plan) => plan.id === form.data.plan_id);
    const isSame = !!current && selected?.id === current.id;
    const switching = !!current && !!selected && !isSame;

    const outcome = !selected
        ? null
        : isSame && !current.expired
          ? t("The current plan is extended by :days days from :date.")
                .replace(":days", String(selected.validity))
                .replace(":date", formatDate(current.expiresAt))
          : t("The plan starts today and is valid for :days days.").replace(":days", String(selected.validity));

    const submit = () =>
        form.post(route("admin.update.customer.plan"), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t("Change plan")}</DialogTitle>
                    <DialogDescription>
                        {customer.name} ·{" "}
                        {current ? `${t("Current plan")}: ${current.name}` : t("No plan yet")}
                    </DialogDescription>
                </DialogHeader>

                <div className="flex max-h-[50svh] flex-col gap-2 overflow-y-auto">
                    {plans.map((plan) => {
                        const active = form.data.plan_id === plan.id;
                        return (
                            <button
                                key={plan.id}
                                type="button"
                                onClick={() => form.setData("plan_id", plan.id)}
                                className={cn(
                                    "flex items-center gap-3 rounded-lg border p-3 text-start transition-colors hover:bg-muted/50",
                                    active && "border-primary bg-primary/5 ring-1 ring-primary",
                                )}
                            >
                                <span
                                    className={cn(
                                        "flex size-4 shrink-0 items-center justify-center rounded-full border",
                                        active && "border-primary bg-primary text-primary-foreground",
                                    )}
                                >
                                    {active && <Check className="size-3" />}
                                </span>
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="flex items-center gap-2 text-sm font-medium">
                                        {plan.name}
                                        {current?.id === plan.id && <Badge variant="secondary">{t("Current")}</Badge>}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        {plan.validity >= 9999 ? t("Lifetime") : `${plan.validity} ${t("days")}`}
                                    </span>
                                </span>
                                {plan.price !== null && (
                                    <span className="text-sm font-semibold tabular-nums">
                                        {plan.price > 0 ? formatCurrency(plan.price, currency) : t("Free")}
                                    </span>
                                )}
                            </button>
                        );
                    })}
                </div>

                {outcome && <p className="text-sm text-muted-foreground">{outcome}</p>}
                {switching && (
                    <Alert variant="warning">
                        <AlertTriangle />
                        <AlertDescription className="text-foreground">
                            {t("Switching to a different plan sets all of this customer's business cards and stores to inactive.")}
                        </AlertDescription>
                    </Alert>
                )}
                {selected && selected.price !== null && selected.price > 0 && (
                    <p className="text-xs text-muted-foreground">
                        {t("An offline payment invoice is recorded and emailed to the customer.")}
                    </p>
                )}
                <FieldError message={form.errors.plan_id} />

                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button variant="outline" onClick={() => onOpenChange(false)} disabled={form.processing}>
                        {t("Cancel")}
                    </Button>
                    <Button onClick={submit} disabled={!selected || form.processing}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {isSame ? t("Extend plan") : t("Change plan")}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
