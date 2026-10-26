import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { cn } from "@admin/lib/utils";

import { DELIVERY_LABELS, PAYMENT_LABELS, type DeliveryStatus, type PaymentStatus } from "../types";

export function PaymentBadge({ status }: { status: string }) {
    const { t } = useTranslation();
    const key = status as PaymentStatus;
    return (
        <Badge
            variant="outline"
            className={cn(
                key === "success" && "border-success/30 text-success",
                (key === "failed" || key === "cancelled") && "border-destructive/30 text-destructive",
                key === "pending" && "border-warning/40 text-warning",
            )}
        >
            {t(PAYMENT_LABELS[key] ?? status)}
        </Badge>
    );
}

export function DeliveryBadge({ status }: { status: string }) {
    const { t } = useTranslation();
    const key = status as DeliveryStatus;
    return (
        <Badge
            variant="outline"
            className={cn(
                "capitalize",
                key === "processing" && "border-primary/30 text-primary",
                ["printing process begun", "shipped", "out for delivery"].includes(key) && "border-primary/30 bg-primary/5 text-primary",
                key === "delivered" && "border-success/30 text-success",
                key === "hold" && "border-warning/40 text-warning",
                key === "cancelled" && "border-destructive/30 text-destructive",
                key === "pending" && "text-muted-foreground",
            )}
        >
            {t(DELIVERY_LABELS[key] ?? status)}
        </Badge>
    );
}
