import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import {
    ArrowUpCircle,
    ArrowUpRight,
    CheckCircle2,
    ExternalLink,
    Loader2,
    Puzzle,
    Search,
    Store,
    Trash2,
    Upload,
} from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ZipDropzone } from "@admin/components/zip-dropzone";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardFooter } from "@admin/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import {
    loadMarketplace,
    openMarketplacePage,
    type MarketItem,
} from "@admin/lib/marketplace";
import { cn } from "@admin/lib/utils";
import type { PluginNotice } from "@admin/types";

type Plugin = {
    id: string;
    name: string;
    description: string | null;
    version: string | null;
    image: string;
    settingsUrl: string | null;
    builtIn: boolean;
    legacy: boolean;
};

type PaginationLinks = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLinks[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

type Tab = "all" | "builtIn" | "added" | "marketplace";

type Props = {
    plugins: Paginated<Plugin> | Plugin[];
    allPlugins?: Plugin[];
    tab?: Tab;
    search?: string;
    counts?: { all: number; builtIn: number; added: number };
    maxUploadKb: number;
};

const MARKETPLACE_DATA = "https://gobizsaas.com/data/plugins.json";

export default function Plugins({
    plugins,
    allPlugins,
    tab: serverTab = "all",
    search: initialSearch = "",
    counts: serverCounts,
    maxUploadKb,
}: Props) {
    const { t } = useTranslation();
    const [tab, setTab] = useState<Tab>(serverTab);
    const [search, setSearch] = useState(initialSearch);
    const [uploading, setUploading] = useState(false);
    const [removing, setRemoving] = useState<Plugin | null>(null);
    const [processing, setProcessing] = useState(false);
    const { flash } = usePage().props;
    const [notice, setNotice] = useState<PluginNotice | null>(null);
    const [market, setMarket] = useState<MarketItem[] | null>(null);
    const [loadingMarket, setLoadingMarket] = useState(false);
    const firstRender = useRef(true);

    // Safely extract plugin list whether paginated or plain array
    const pluginList: Plugin[] = Array.isArray(plugins)
        ? plugins
        : plugins && Array.isArray(plugins.data)
          ? plugins.data
          : [];

    const pagination = Array.isArray(plugins) ? null : plugins;
    const fullPlugins = allPlugins ?? pluginList;

    // An uploaded zip that wasn't installed (old Blade version, or older than installed)
    useEffect(
        () => setNotice(flash.plugin_notice ?? null),
        [flash.plugin_notice],
    );

    const counts = serverCounts ?? {
        all: fullPlugins.length,
        builtIn: fullPlugins.filter((p) => p.builtIn).length,
        added: fullPlugins.filter((p) => !p.builtIn).length,
    };

    const installed = useMemo(
        () => new Map(fullPlugins.map((plugin) => [plugin.id, plugin])),
        [fullPlugins],
    );

    const visibleMarket = useMemo(() => {
        const term = search.trim().toLowerCase();
        return (market ?? []).filter(
            (plugin) =>
                !term ||
                plugin.name.toLowerCase().includes(term) ||
                plugin.description?.toLowerCase().includes(term),
        );
    }, [market, search]);

    const visit = (next: { tab?: Tab; search?: string }) => {
        const nextTab = next.tab ?? tab;
        const nextSearch = next.search ?? search;

        if (nextTab === "marketplace") {
            openMarketplace();
            return;
        }

        router.get(
            route("admin.plugins"),
            {
                ...(nextTab !== "all" ? { tab: nextTab } : {}),
                ...(nextSearch ? { search: nextSearch } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    // Debounce search
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        if (tab === "marketplace") return;
        const timer = window.setTimeout(() => visit({ search }), 300);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    // Loaded once per visit; if the list can't be loaded, open the marketplace website instead
    const openMarketplace = async () => {
        if (market) return setTab("marketplace");

        setLoadingMarket(true);
        try {
            setMarket(await loadMarketplace(MARKETPLACE_DATA, "plugin_id"));
            setTab("marketplace");
        } catch {
            openMarketplacePage();
        } finally {
            setLoadingMarket(false);
        }
    };

    const remove = () => {
        if (!removing) return;
        router.delete(route("admin.plugins.delete", removing.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setRemoving(null);
            },
        });
    };

    return (
        <>
            <Head title={t("Plugins")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Plugins")}
                    description={t(
                        "Add features to GoBiz and manage their settings.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Plugins") },
                    ]}
                    actions={
                        <Button onClick={() => setUploading(true)}>
                            <Upload />
                            {t("Install plugin")}
                        </Button>
                    }
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Tabs
                        value={tab}
                        onValueChange={(value) => {
                            const newTab = value as Tab;
                            setTab(newTab);
                            if (newTab !== "marketplace") {
                                visit({ tab: newTab });
                            } else {
                                openMarketplace();
                            }
                        }}
                    >
                        <TabsList>
                            {(
                                [
                                    ["all", t("All")],
                                    ["builtIn", t("Built-in")],
                                    ["added", t("Added")],
                                ] as const
                            ).map(([value, label]) => (
                                <TabsTrigger key={value} value={value}>
                                    {label}
                                    <span className="text-muted-foreground tabular-nums">
                                        {counts[value]}
                                    </span>
                                </TabsTrigger>
                            ))}
                            <TabsTrigger
                                value="marketplace"
                                disabled={loadingMarket}
                                aria-busy={loadingMarket}
                            >
                                {loadingMarket ? (
                                    <Loader2 className="animate-spin" />
                                ) : (
                                    <Store />
                                )}
                                {t("Marketplace")}
                                {market ? (
                                    <span className="text-muted-foreground tabular-nums">
                                        {market.length}
                                    </span>
                                ) : (
                                    <Badge className="h-4 px-1.5 text-[10px] leading-none">
                                        {t("New")}
                                    </Badge>
                                )}
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <div className="relative sm:w-64">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t("Search plugins")}
                            className="ps-8"
                        />
                    </div>
                </div>

                {tab === "marketplace" ? (
                    visibleMarket.length === 0 ? (
                        <EmptyState
                            icon={Store}
                            title={t("No plugins match your search")}
                        />
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {visibleMarket.map((plugin) => (
                                <MarketplaceCard
                                    key={plugin.id}
                                    plugin={plugin}
                                    installed={installed.get(plugin.id)}
                                />
                            ))}
                        </div>
                    )
                ) : pluginList.length === 0 ? (
                    <EmptyState
                        icon={Puzzle}
                        title={
                            search
                                ? t("No plugins match your search")
                                : t("No plugins installed")
                        }
                        description={
                            !search && fullPlugins.length === 0
                                ? t(
                                      "Install a plugin from a .zip file to add features.",
                                  )
                                : undefined
                        }
                    />
                ) : (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {pluginList.map((plugin) => (
                                <Card key={plugin.id} className="gap-0 py-0">
                                    <CardContent className="flex flex-1 items-start gap-3 p-4">
                                        <div className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white p-1.5">
                                            <img
                                                src={plugin.image}
                                                alt=""
                                                className="max-h-full max-w-full object-contain"
                                            />
                                        </div>
                                        <div className="flex min-w-0 flex-1 flex-col gap-1">
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className="truncate font-medium"
                                                    title={plugin.name}
                                                >
                                                    {plugin.name}
                                                </span>
                                                {(plugin.builtIn ||
                                                    plugin.legacy) && (
                                                    <div className="ms-auto flex shrink-0 items-center gap-1.5">
                                                        {plugin.builtIn && (
                                                            <Badge variant="secondary">
                                                                {t("Built-in")}
                                                            </Badge>
                                                        )}
                                                        {plugin.legacy && (
                                                            <Badge
                                                                variant="outline"
                                                                className="border-warning/40 text-warning"
                                                            >
                                                                {t(
                                                                    "Update required",
                                                                )}
                                                            </Badge>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                            {plugin.legacy ? (
                                                <p className="text-sm text-muted-foreground">
                                                    {t(
                                                        "A new version is available. Upload the latest version to edit this plugin's settings.",
                                                    )}
                                                </p>
                                            ) : (
                                                plugin.description && (
                                                    <p className="line-clamp-2 text-sm text-muted-foreground">
                                                        {plugin.description}
                                                    </p>
                                                )
                                            )}
                                        </div>
                                    </CardContent>
                                    <CardFooter className="flex items-center gap-2 border-t p-3">
                                        <span className="me-auto text-xs text-muted-foreground tabular-nums">
                                            {plugin.version &&
                                                `v${plugin.version}`}
                                        </span>
                                        {!plugin.builtIn && (
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                className="text-muted-foreground hover:text-destructive"
                                                onClick={() =>
                                                    setRemoving(plugin)
                                                }
                                                aria-label={t("Remove")}
                                                title={t("Remove")}
                                            >
                                                <Trash2 />
                                            </Button>
                                        )}
                                        {plugin.settingsUrl && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <a href={plugin.settingsUrl}>
                                                    {t("Open")}
                                                    <ArrowUpRight />
                                                </a>
                                            </Button>
                                        )}
                                    </CardFooter>
                                </Card>
                            ))}
                        </div>

                        {/* Pagination navigation controls */}
                        {pagination && pagination.last_page > 1 && (
                            <div className="flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row">
                                <p className="text-sm text-muted-foreground">
                                    {t("Showing :from to :to of :total results")
                                        .replace(
                                            ":from",
                                            String(pagination.from ?? 0),
                                        )
                                        .replace(
                                            ":to",
                                            String(pagination.to ?? 0),
                                        )
                                        .replace(
                                            ":total",
                                            String(pagination.total),
                                        )}
                                </p>
                                <div className="flex flex-wrap items-center gap-1">
                                    {pagination.links.map((link, index) => {
                                        if (!link.url) {
                                            return (
                                                <span
                                                    key={index}
                                                    className="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-transparent px-3 text-sm text-muted-foreground/50 opacity-50"
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            );
                                        }
                                        return (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                preserveScroll
                                                className={cn(
                                                    "inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors",
                                                    link.active
                                                        ? "border-primary bg-primary text-primary-foreground"
                                                        : "border-input bg-background hover:bg-muted text-foreground",
                                                )}
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                            />
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>

            <Dialog open={uploading} onOpenChange={setUploading}>
                <DialogContent className="sm:max-w-xl">
                    {uploading && (
                        <UploadForm
                            maxUploadKb={maxUploadKb}
                            onClose={() => setUploading(false)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <Dialog
                open={notice !== null}
                onOpenChange={(open) => !open && setNotice(null)}
            >
                <DialogContent className="sm:max-w-md">
                    {notice && (
                        <>
                            <DialogHeader>
                                <div className="mb-2 flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <ArrowUpCircle className="size-5" />
                                </div>
                                <DialogTitle>
                                    {notice.kind === "legacy"
                                        ? t("A new version is available")
                                        : t("You already have a newer version")}
                                </DialogTitle>
                                <DialogDescription>
                                    {notice.kind === "legacy"
                                        ? t(
                                              ":name :version is an older version that doesn't work with this version of GoBiz, so it wasn't installed. Download the latest version of the plugin and upload it here.",
                                          )
                                              .replace(":name", notice.name)
                                              .replace(
                                                  ":version",
                                                  notice.version,
                                              )
                                        : t(
                                              ":name :version wasn't installed because version :installed is already installed.",
                                          )
                                              .replace(":name", notice.name)
                                              .replace(
                                                  ":version",
                                                  notice.version,
                                              )
                                              .replace(
                                                  ":installed",
                                                  notice.installed ?? "",
                                              )}
                                </DialogDescription>
                            </DialogHeader>
                            <div className="flex justify-end gap-2">
                                <Button
                                    variant="secondary"
                                    onClick={() => setNotice(null)}
                                >
                                    {t("Close")}
                                </Button>
                                {notice.kind === "legacy" && (
                                    <Button
                                        onClick={() => {
                                            setNotice(null);
                                            setUploading(true);
                                        }}
                                    >
                                        <Upload />
                                        {t("Upload latest version")}
                                    </Button>
                                )}
                            </div>
                        </>
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={removing !== null}
                onOpenChange={(open) => !open && setRemoving(null)}
                title={t("Remove “:name”?").replace(
                    ":name",
                    removing?.name ?? "",
                )}
                description={t(
                    "The plugin's files are deleted from the server. You can install it again later.",
                )}
                confirmLabel={t("Remove")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}

function MarketplaceCard({
    plugin,
    installed,
}: {
    plugin: MarketItem;
    installed?: Plugin;
}) {
    const { t } = useTranslation();
    const [logoFailed, setLogoFailed] = useState(false);

    return (
        <Card className="gap-0 py-0">
            <CardContent className="flex flex-1 items-start gap-3 p-4">
                <div className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white p-1.5">
                    {plugin.logo && !logoFailed ? (
                        <img
                            src={plugin.logo}
                            alt=""
                            loading="lazy"
                            onError={() => setLogoFailed(true)}
                            className="max-h-full max-w-full object-contain"
                        />
                    ) : (
                        <Puzzle className="size-5 text-muted-foreground" />
                    )}
                </div>
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <div className="flex items-center gap-2">
                        <span
                            className="truncate font-medium"
                            title={plugin.name}
                        >
                            {plugin.name}
                        </span>
                        {installed && (
                            <Badge
                                variant="outline"
                                className="ms-auto shrink-0 border-success/30 text-success"
                            >
                                <CheckCircle2 />
                                {t("Installed")}
                            </Badge>
                        )}
                    </div>
                    {plugin.description && (
                        <p className="line-clamp-2 text-sm text-muted-foreground">
                            {plugin.description}
                        </p>
                    )}
                </div>
            </CardContent>
            <CardFooter className="flex items-center justify-end gap-2 border-t p-3">
                {installed ? (
                    installed.settingsUrl && (
                        <Button variant="outline" size="sm" asChild>
                            <a href={installed.settingsUrl}>
                                <ExternalLink />
                                {t("Open")}
                            </a>
                        </Button>
                    )
                ) : (
                    <Button size="sm" asChild>
                        <a
                            href={plugin.url}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {t("Download")}
                            <ArrowUpRight />
                        </a>
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}

function UploadForm({
    maxUploadKb,
    onClose,
}: {
    maxUploadKb: number;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm<{ zip_file: File | null }>({ zip_file: null });
    const file = form.data.zip_file;
    const tooBig = !!file && file.size / 1024 > maxUploadKb;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.plugin.upload"), {
            forceFormData: true,
            onSuccess: onClose,
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{t("Install plugin")}</DialogTitle>
                <DialogDescription>
                    {t(
                        "Upload the plugin's .zip file. If it's already installed, it will be updated.",
                    )}
                </DialogDescription>
            </DialogHeader>

            <ZipDropzone
                file={file}
                onChange={(picked) => form.setData("zip_file", picked)}
                maxUploadKb={maxUploadKb}
                invalid={!!form.errors.zip_file || tooBig}
                disabled={form.processing}
            />
            <FieldError
                message={
                    tooBig
                        ? t("This file is larger than the upload limit.")
                        : form.errors.zip_file
                }
            />

            {form.processing && (
                <p className="text-sm text-muted-foreground">
                    {t("Installing… Please keep this page open.")}
                </p>
            )}

            <div className="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    onClick={onClose}
                    disabled={form.processing}
                >
                    {t("Cancel")}
                </Button>
                <Button
                    type="submit"
                    disabled={!file || tooBig || form.processing}
                >
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Install")}
                </Button>
            </div>
        </form>
    );
}
