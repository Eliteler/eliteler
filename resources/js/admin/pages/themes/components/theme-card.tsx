import { Link } from "@inertiajs/react";
import { Code2, Eye, ImageOff, MoreHorizontal, Pencil, Power, PowerOff, Users } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Tooltip, TooltipContent, TooltipTrigger } from "@admin/components/ui/tooltip";
import { cn } from "@admin/lib/utils";

/**
 * Portrait screenshot card shared by themes and intros.
 */
export function ThemeCard({
    name,
    thumbnail,
    active,
    usage,
    badge,
    customCode,
    editHref,
    onEdit,
    onPreview,
    onToggle,
}: {
    name: string;
    thumbnail: string | null;
    active: boolean;
    usage: number;
    badge?: string;
    customCode?: boolean;
    editHref?: string;
    onEdit?: () => void;
    onPreview: () => void;
    onToggle: () => void;
}) {
    const { t } = useTranslation();

    return (
        <div className="group flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs transition-shadow hover:shadow-md">
            <button
                type="button"
                onClick={onPreview}
                className="relative aspect-[4/5] overflow-hidden bg-muted outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                aria-label={`${t("Preview")} ${name}`}
            >
                {thumbnail ? (
                    <img
                        src={thumbnail}
                        alt={name}
                        loading="lazy"
                        className={cn(
                            "size-full object-cover object-top transition-transform duration-300 group-hover:scale-[1.03]",
                            !active && "grayscale-[60%]",
                        )}
                    />
                ) : (
                    <ImageOff className="absolute inset-0 m-auto size-8 text-muted-foreground" />
                )}
                <span className="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition-all group-hover:bg-black/30 group-hover:opacity-100">
                    <span className="flex items-center gap-1.5 rounded-full bg-background/90 px-3 py-1.5 text-xs font-medium text-foreground shadow">
                        <Eye className="size-3.5" />
                        {t("Preview")}
                    </span>
                </span>
                <span className="absolute start-2 top-2 flex gap-1">
                    {!active && (
                        <Badge variant="secondary" className="bg-background/90 shadow-sm">
                            {t("Inactive")}
                        </Badge>
                    )}
                    {customCode && (
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Badge variant="secondary" className="bg-background/90 shadow-sm">
                                    <Code2 />
                                </Badge>
                            </TooltipTrigger>
                            <TooltipContent>{t("Has custom CSS or JS")}</TooltipContent>
                        </Tooltip>
                    )}
                </span>
            </button>
            <div className="flex items-start gap-2 p-3">
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <span className="truncate text-sm font-medium" title={name}>
                        {name}
                    </span>
                    <span className="flex items-center gap-2 text-xs text-muted-foreground">
                        {badge && <span>{badge}</span>}
                        {badge && <span aria-hidden>·</span>}
                        <span className="flex items-center gap-1 tabular-nums" title={t("Business cards using it")}>
                            <Users className="size-3" />
                            {usage}
                        </span>
                    </span>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon-sm" className="-me-1 shrink-0" aria-label={t("Actions")}>
                            <MoreHorizontal />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {editHref ? (
                            <DropdownMenuItem asChild>
                                <Link href={editHref}>
                                    <Pencil />
                                    {t("Edit")}
                                </Link>
                            </DropdownMenuItem>
                        ) : (
                            <DropdownMenuItem onSelect={onEdit}>
                                <Pencil />
                                {t("Edit")}
                            </DropdownMenuItem>
                        )}
                        <DropdownMenuItem onSelect={onPreview}>
                            <Eye />
                            {t("Preview")}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onSelect={onToggle} variant={active ? "destructive" : "default"}>
                            {active ? <PowerOff /> : <Power />}
                            {active ? t("Disable") : t("Enable")}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    );
}
