import { Head, router, useForm, usePage } from "@inertiajs/react";
import {
    ArrowUpCircle,
    ArrowUpRight,
    CheckCircle2,
    Loader2,
    Puzzle,
    Search,
    Store,
    Trash2,
    Upload,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ZipDropzone } from "@admin/components/zip-dropzone";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { MarketplaceCard, type MarketplaceItem } from "@admin/components/marketplace-card";
import {
    MarketplaceUpdateBadge,
    MarketplaceUpdateButton,
} from "@admin/components/marketplace-update";
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
import { cn } from "@admin/lib/utils";
import type { Paginated, PluginNotice } from "@admin/types";

type Plugin = {
    id: string;
    name: string;
    description: string | null;
    version: string | null;
    updateVersion: string | null;
    image: string;
    settingsUrl: string | null;
    builtIn: boolean;
    legacy: boolean;
};

type Tab = "all" | "builtIn" | "added" | "updates" | "marketplace";

type Props = {
    plugins: Paginated<Plugin> | null;
    marketplace: Paginated<MarketplaceItem> | null;
    tab: Tab;
    search: string;
    counts: { all: number; builtIn: number; added: number; updates: number | null; marketplace: number };
    maxUploadKb: number;
};

export default function Plugins({
    plugins,
    marketplace,
    tab,
    search: initialSearch,
    counts,
    maxUploadKb,
}: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(initialSearch);
    const [uploading, setUploading] = useState(false);
    const [removing, setRemoving] = useState<Plugin | null>(null);
    const [processing, setProcessing] = useState(false);
    const { flash } = usePage().props;
    const [notice, setNotice] = useState<PluginNotice | null>(null);
    const searchTimer = useRef<number | undefined>(undefined);

    const pluginList = plugins?.data ?? [];
    const marketplaceItems = marketplace?.data ?? [];
    const pagination = marketplace ?? plugins;

    // An uploaded zip that wasn't installed (old Blade version, or older than installed)
    useEffect(
        () => setNotice(flash.plugin_notice ?? null),
        [flash.plugin_notice],
    );

    const visit = (nextTab: Tab, nextSearch = search) => {
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
        if (search === initialSearch) return;
        const timer = window.setTimeout(() => visit(tab, search), 300);
        searchTimer.current = timer;
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

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
                            window.clearTimeout(searchTimer.current);
                            visit(value as Tab);
                        }}
                    >
                        <TabsList className="h-auto flex-wrap justify-start">
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
                            {counts.updates !== null && (
                                <TabsTrigger value="updates">
                                    {t("Updates")}
                                    <span className="text-muted-foreground tabular-nums">
                                        {counts.updates}
                                    </span>
                                </TabsTrigger>
                            )}
                            {counts.marketplace > 0 && (
                                <TabsTrigger value="marketplace">
                                    <Store />
                                    {t("Marketplace")}
                                    <span className="text-muted-foreground tabular-nums">{counts.marketplace}</span>
                                </TabsTrigger>
                            )}
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
                    marketplaceItems.length > 0 ? (
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {marketplaceItems.map((item) => (
                                <MarketplaceCard key={item.id} item={item} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon={Store} title={t("No plugins match your search")} />
                    )
                ) : pluginList.length === 0 ? (
                    <EmptyState
                        icon={tab === "updates" ? ArrowUpCircle : Puzzle}
                        title={
                            tab === "updates"
                                ? initialSearch && counts.updates
                                    ? t("No plugin updates match your search")
                                    : t("No plugin updates available")
                                : initialSearch
                                  ? t("No plugins match your search")
                                  : t("No plugins installed")
                        }
                        description={
                            tab !== "updates" && !initialSearch && counts.all === 0
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
                                <Card
                                    key={plugin.id}
                                    className={cn(
                                        "relative gap-0 py-0",
                                        plugin.updateVersion && "pt-8",
                                    )}
                                >
                                    <MarketplaceUpdateBadge
                                        version={plugin.updateVersion}
                                    />
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
                                        {plugin.updateVersion && (
                                            <MarketplaceUpdateButton />
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

                    </>
                )}
                {pagination && <Pagination data={pagination} className="border-t pt-4" preserveState />}
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
