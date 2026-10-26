import { Link } from "@inertiajs/react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useTranslation } from "react-i18next";

import { buttonVariants } from "@admin/components/ui/button";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

/**
 * Pagination for a Laravel LengthAwarePaginator passed as an Inertia prop.
 */
export function Pagination({ data, className }: { data: Paginated<unknown>; className?: string }) {
    const { t } = useTranslation();

    if (data.last_page <= 1) return null;

    // Laravel's links: [« Previous, 1, 2, …, Next »]
    const pages = data.links.slice(1, -1);

    return (
        <div className={cn("flex flex-col items-center justify-between gap-3 sm:flex-row", className)}>
            <p className="text-sm text-muted-foreground">
                {t("Showing")} {data.from}–{data.to} {t("of")} {data.total}
            </p>
            <nav className="flex items-center gap-1" aria-label="Pagination">
                <PageLink href={data.prev_page_url} label={t("Previous")}>
                    <ChevronLeft className="rtl:rotate-180" />
                </PageLink>
                {pages.map((link, i) =>
                    link.url ? (
                        <Link
                            key={i}
                            href={link.url}
                            preserveScroll
                            className={cn(
                                buttonVariants({ variant: link.active ? "outline" : "ghost", size: "icon-sm" }),
                                "hidden tabular-nums sm:inline-flex",
                            )}
                        >
                            {link.label}
                        </Link>
                    ) : (
                        <span key={i} className="hidden px-1 text-sm text-muted-foreground sm:inline">
                            …
                        </span>
                    ),
                )}
                <PageLink href={data.next_page_url} label={t("Next")}>
                    <ChevronRight className="rtl:rotate-180" />
                </PageLink>
            </nav>
        </div>
    );
}

function PageLink({ href, label, children }: { href: string | null; label: string; children: React.ReactNode }) {
    const classes = buttonVariants({ variant: "ghost", size: "icon-sm" });

    if (!href) {
        return (
            <span className={cn(classes, "pointer-events-none opacity-40")} aria-disabled>
                {children}
            </span>
        );
    }

    return (
        <Link href={href} preserveScroll className={classes} aria-label={label}>
            {children}
        </Link>
    );
}
