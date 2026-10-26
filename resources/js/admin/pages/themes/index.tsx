import { Head, router } from "@inertiajs/react";
import { Palette, Search, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Button } from "@admin/components/ui/button";
import { Input } from "@admin/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import type { Paginated } from "@admin/types";

import { PreviewDialog } from "./components/preview-dialog";
import { StatusToggleDialog } from "./components/status-toggle-dialog";
import { ThemeCard } from "./components/theme-card";
import { THEME_TYPE_LABELS, type ThemeItem } from "./types";

type Filters = { status: "all" | "active" | "disabled"; type: "all" | "vcard" | "store"; search: string };

type Props = {
    themes: Paginated<ThemeItem>;
    filters: Filters;
    counts: { all: number; vcard: number; store: number };
};

export default function ThemesIndex({ themes, filters, counts }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(filters.search);
    const [preview, setPreview] = useState<ThemeItem | null>(null);
    const [toggle, setToggle] = useState<ThemeItem | null>(null);
    const [processing, setProcessing] = useState(false);
    const firstRender = useRef(true);

    const visit = (next: Partial<Filters>) => {
        const merged = { ...filters, search, ...next };
        router.get(
            route("admin.themes", { status: merged.status }),
            {
                ...(merged.type !== "all" ? { type: merged.type } : {}),
                ...(merged.search ? { search: merged.search } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    // Debounced search
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const timer = window.setTimeout(() => visit({ search }), 350);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const confirmToggle = () => {
        if (!toggle) return;
        router.post(
            route("admin.update.theme.status"),
            { id: toggle.id, status: toggle.active ? "disable" : "enable" },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setToggle(null);
                },
            },
        );
    };

    const filtered = filters.status !== "all" || filters.type !== "all" || filters.search !== "";

    return (
        <>
            <Head title={t("Themes")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Themes")}
                    description={t("Design templates customers can choose for their business cards and stores.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Themes") }]}
                />

                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <Tabs value={filters.type} onValueChange={(type) => visit({ type: type as Filters["type"] })}>
                        <TabsList>
                            <TabsTrigger value="all">
                                {t("All")} <span className="text-muted-foreground tabular-nums">{counts.all}</span>
                            </TabsTrigger>
                            <TabsTrigger value="vcard">
                                {t("Business cards")} <span className="text-muted-foreground tabular-nums">{counts.vcard}</span>
                            </TabsTrigger>
                            <TabsTrigger value="store">
                                {t("Stores")} <span className="text-muted-foreground tabular-nums">{counts.store}</span>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <div className="flex gap-2">
                        <div className="relative flex-1 lg:w-64 lg:flex-none">
                            <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder={t("Search themes...")}
                                className="ps-8"
                            />
                        </div>
                        <Select value={filters.status} onValueChange={(status) => visit({ status: status as Filters["status"] })}>
                            <SelectTrigger className="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent align="end">
                                <SelectItem value="all">{t("All statuses")}</SelectItem>
                                <SelectItem value="active">{t("Active")}</SelectItem>
                                <SelectItem value="disabled">{t("Inactive")}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {themes.data.length === 0 ? (
                    <EmptyState
                        icon={Palette}
                        title={t("No themes found")}
                        description={filtered ? t("Try a different search or filter.") : undefined}
                        action={
                            filtered ? (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setSearch("");
                                        visit({ status: "all", type: "all", search: "" });
                                    }}
                                >
                                    <X />
                                    {t("Clear filters")}
                                </Button>
                            ) : undefined
                        }
                    />
                ) : (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 2xl:grid-cols-6">
                        {themes.data.map((theme) => (
                            <ThemeCard
                                key={theme.id}
                                name={theme.name}
                                thumbnail={theme.thumbnail}
                                active={theme.active}
                                usage={theme.usage}
                                badge={t(THEME_TYPE_LABELS[theme.type])}
                                customCode={theme.customCode}
                                editHref={route("admin.edit.theme", theme.id)}
                                onPreview={() => setPreview(theme)}
                                onToggle={() => setToggle(theme)}
                            />
                        ))}
                    </div>
                )}

                <Pagination data={themes} />
            </div>

            <PreviewDialog
                open={preview !== null}
                onOpenChange={(open) => !open && setPreview(null)}
                title={preview?.name ?? ""}
                description={preview ? t(THEME_TYPE_LABELS[preview.type]) : undefined}
                src={preview?.thumbnail ?? null}
            />
            <StatusToggleDialog
                target={toggle}
                kind="theme"
                processing={processing}
                onCancel={() => setToggle(null)}
                onConfirm={confirmToggle}
            />
        </>
    );
}
