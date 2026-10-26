import type { LucideIcon } from "lucide-react";
import type { ReactNode } from "react";

import { cn } from "@admin/lib/utils";

export function EmptyState({
    icon: Icon,
    title,
    description,
    action,
    className,
}: {
    icon: LucideIcon;
    title: string;
    description?: string;
    action?: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                "flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center",
                className,
            )}
        >
            <span className="flex size-12 items-center justify-center rounded-full bg-muted">
                <Icon className="size-6 text-muted-foreground" />
            </span>
            <div className="flex flex-col gap-1">
                <p className="font-medium">{title}</p>
                {description && <p className="max-w-sm text-sm text-muted-foreground">{description}</p>}
            </div>
            {action}
        </div>
    );
}
