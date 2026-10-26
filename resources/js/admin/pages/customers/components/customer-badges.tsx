import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";
import { usePage } from "@inertiajs/react";

import type { CustomerPlan, CustomerState } from "../types";

const STATE_STYLES: Record<CustomerState, string> = {
    active: "border-success/30 text-success",
    inactive: "text-muted-foreground",
    suspended: "border-destructive/30 text-destructive",
    deleted: "border-transparent bg-muted text-muted-foreground",
};

const STATE_LABELS: Record<CustomerState, string> = {
    active: "Active",
    inactive: "Inactive",
    suspended: "Suspended",
    deleted: "Deleted",
};

export function StateBadge({ state }: { state: CustomerState }) {
    const { t } = useTranslation();
    return (
        <Badge variant="outline" className={STATE_STYLES[state]}>
            <span className="size-1.5 rounded-full bg-current" />
            {t(STATE_LABELS[state])}
        </Badge>
    );
}

export function UnverifiedBadge() {
    const { t } = useTranslation();
    return (
        <Badge variant="outline" className="border-warning/40 text-warning">
            {t("Unverified")}
        </Badge>
    );
}

/** Plan name + price, and the expiry line. */
export function PlanCell({ plan, className }: { plan: CustomerPlan | null; className?: string }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;

    if (!plan) {
        return <span className="text-sm text-muted-foreground">{t("No plan")}</span>;
    }

    return (
        <div className={cn("flex flex-col gap-0.5", className)}>
            <span className="text-sm font-medium">
                {plan.name}
                {plan.price !== null && (
                    <span className="ms-1.5 text-xs font-normal text-muted-foreground">
                        {plan.price > 0 ? formatCurrency(plan.price, currency) : t("Free")}
                    </span>
                )}
            </span>
            <span
                className={cn(
                    "text-xs",
                    plan.expired ? "text-destructive" : plan.daysLeft <= 7 ? "text-warning" : "text-muted-foreground",
                )}
            >
                {plan.expired
                    ? `${t("Expired")} ${formatDate(plan.expiresAt)}`
                    : plan.daysLeft > 3650
                      ? t("Lifetime")
                      : `${t("Until")} ${formatDate(plan.expiresAt)}`}
            </span>
        </div>
    );
}
