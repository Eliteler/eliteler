import { Link, usePage } from "@inertiajs/react";
import { ArrowUpRight, ChevronRight, FileText } from "lucide-react";
import type { ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { cn } from "@admin/lib/utils";

export type Crumb = { label: string; href?: string };

/**
 * Standard page heading: breadcrumbs, title, description and actions.
 * On plugin and web template pages it also links their docs ("documentation_url" in
 * plugin.json / template.json, passed as documentationUrl by App\Support\Admin\PluginPage).
 */
export function PageHeader({
    title,
    description,
    breadcrumbs = [],
    actions,
}: {
    title: ReactNode;
    description?: ReactNode;
    breadcrumbs?: Crumb[];
    actions?: ReactNode;
}) {
    const { t } = useTranslation();
    const docsUrl = (usePage().props as { documentationUrl?: string | null }).documentationUrl;

    return (
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div className="flex min-w-0 flex-col gap-1">
                {breadcrumbs.length > 0 && (
                    <nav aria-label="Breadcrumb" className="mb-1 flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
                        {breadcrumbs.map((crumb, i) => (
                            <span key={i} className="flex items-center gap-1">
                                {i > 0 && <ChevronRight className="size-3.5 rtl:rotate-180" />}
                                {crumb.href ? (
                                    <Link href={crumb.href} className="transition-colors hover:text-foreground">
                                        {crumb.label}
                                    </Link>
                                ) : (
                                    <span className="text-foreground">{crumb.label}</span>
                                )}
                            </span>
                        ))}
                    </nav>
                )}
                <h1 className="truncate text-2xl font-semibold tracking-tight">{title}</h1>
                {description && <p className="text-sm text-muted-foreground">{description}</p>}
            </div>
            {(actions || docsUrl) && (
                <div className={cn("flex flex-wrap items-center gap-2", docsUrl && "md:self-center")}>
                    {docsUrl && (
                        <Button variant="outline" asChild>
                            <a href={docsUrl} target="_blank" rel="noopener noreferrer">
                                <FileText />
                                {t("Documentation")}
                                <ArrowUpRight />
                            </a>
                        </Button>
                    )}
                    {actions}
                </div>
            )}
        </div>
    );
}
