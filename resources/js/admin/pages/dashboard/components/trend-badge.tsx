import { Minus, TrendingDown, TrendingUp } from "lucide-react";

import { Badge } from "@admin/components/ui/badge";
import { cn } from "@admin/lib/utils";

export function TrendBadge({ value, className }: { value: number; className?: string }) {
    const Icon = value > 0 ? TrendingUp : value < 0 ? TrendingDown : Minus;

    return (
        <Badge
            variant="outline"
            className={cn(
                "gap-1 tabular-nums",
                value > 0 && "text-success",
                value < 0 && "text-destructive",
                value === 0 && "text-muted-foreground",
                className,
            )}
        >
            <Icon />
            {value > 0 ? "+" : ""}
            {value}%
        </Badge>
    );
}
