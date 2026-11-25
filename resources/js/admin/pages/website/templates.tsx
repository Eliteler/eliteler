import { Head, router, useForm, usePage } from "@inertiajs/react";
import {
    ArrowUpCircle,
    ArrowUpRight,
    CheckCircle2,
    FileText,
    LayoutTemplate,
    Loader2,
    MoreHorizontal,
    Settings2,
    Store,
    Trash2,
    Upload,
} from "lucide-react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { MarketplaceCard, type MarketplaceItem } from "@admin/components/marketplace-card";
import {
    MarketplaceUpdateBadge,
    MarketplaceUpdateButton,
} from "@admin/components/marketplace-update";
import { ZipDropzone } from "@admin/components/zip-dropzone";
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { cn } from "@admin/lib/utils";
import type { Paginated, PluginNotice } from "@admin/types";

type Template = {
    id: string;
    name: string;
    version: string | null;
    updateVersion: string | null;
    image: string | null;
    active: boolean;
    builtIn: boolean;
    pagesUrl: string | null;
    settingsUrl: string | null;
    legacy: boolean;
};

type Props = {
    templates: Paginated<Template> | null;
    activeTemplateName: string | null;
    marketplace: Paginated<MarketplaceItem> | null;
    tab: "installed" | "updates" | "marketplace";
    counts: { installed: number; updates: number | null; marketplace: number };
    maxUploadKb: number;
};

type Pending = { template: Template; action: "activate" | "delete" };

export default function WebTemplates({
    templates,
    activeTemplateName,
    marketplace,
    tab,
    counts,
    maxUploadKb,
}: Props) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const templateList = templates?.data ?? [];
    const marketplaceItems = marketplace?.data ?? [];
    const pagination = marketplace ?? templates;

    const { flash } = usePage().props;
    const [notice, setNotice] = useState<PluginNotice | null>(null);

    // An uploaded zip that wasn't installed (old Blade version, or older than installed)
    useEffect(
        () => setNotice(flash.template_notice ?? null),
        [flash.template_notice],
    );

    const changeTab = (value: string) => {
        router.get(
            route("admin.web-templates"),
            value === "installed" ? {} : { tab: value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const run = () => {
        if (!pending) return;
        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        };
        if (pending.action === "activate")
            router.post(
                route("admin.web-template.activate", pending.template.id),
                {},
                options,
            );
        else
            router.delete(
                route("admin.web-template.delete", pending.template.id),
                options,
            );
    };

    return (
        <>
            <Head title={t("Website templates")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Website templates")}
                    description={t(
                        "Choose the design of your public website, then edit its pages and settings.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Website templates") },
                    ]}
                    actions={
                        <Button onClick={() => setUploading(true)}>
                            <Upload />
                            {t("Install template")}
                        </Button>
                    }
                />

                <Tabs value={tab} onValueChange={changeTab}>
                    <TabsList className="h-auto flex-wrap justify-start">
                        <TabsTrigger value="installed">
                            {t("Installed")}
                            <span className="text-muted-foreground tabular-nums">
                                {counts.installed}
                            </span>
                        </TabsTrigger>
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

                {tab === "marketplace" ? (
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {marketplaceItems.map((item) => (
                            <MarketplaceCard key={item.id} item={item} template />
                        ))}
                    </div>
                ) : templateList.length === 0 ? (
                    <EmptyState
                        icon={tab === "updates" ? ArrowUpCircle : LayoutTemplate}
                        title={
                            tab === "updates"
                                ? t("No template updates available")
                                : t("No templates installed")
                        }
                        description={
                            tab === "installed" ? t("Install a template from a .zip file.") : undefined
                        }
                    />
                ) : (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {templateList.map((template) => (
                                <Card
                                    key={template.id}
                                    className={cn(
                                        "gap-0 overflow-hidden py-0",
                                        template.active &&
                                            "ring-2 ring-primary",
                                    )}
                                >
                                    <div className="relative aspect-[16/10] overflow-hidden border-b bg-muted">
                                        {template.image ? (
                                            <img
                                                src={template.image}
                                                alt=""
                                                className="size-full object-cover object-top"
                                            />
                                        ) : (
                                            <div className="flex size-full items-center justify-center">
                                                <LayoutTemplate className="size-10 text-muted-foreground" />
                                            </div>
                                        )}
                                        {template.active && (
                                            <Badge className="absolute start-3 top-3 shadow">
                                                <CheckCircle2 />
                                                {t("Live on your website")}
                                            </Badge>
                                        )}
                                        <MarketplaceUpdateBadge
                                            version={template.updateVersion}
                                        />
                                    </div>
                                    <CardContent className="flex items-start justify-between gap-2 p-4">
                                        <div className="flex min-w-0 flex-col gap-0.5">
                                            <div className="flex items-center gap-2">
                                                <span className="truncate font-medium">
                                                    {template.name}
                                                </span>
                                                {template.legacy && (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-warning/40 text-warning"
                                                    >
                                                        {t("Update required")}
                                                    </Badge>
                                                )}
                                            </div>
                                            <span className="text-xs text-muted-foreground">
                                                {template.version &&
                                                    `v${template.version}`}
                                                {template.builtIn &&
                                                    ` · ${t("Built-in")}`}
                                            </span>
                                            {template.legacy && (
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {t(
                                                        "A new version is available. Upload the latest version to edit this template.",
                                                    )}
                                                </p>
                                            )}
                                        </div>
                                        {!template.active &&
                                            !template.builtIn && (
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon-sm"
                                                            aria-label={t(
                                                                "Actions",
                                                            )}
                                                        >
                                                            <MoreHorizontal />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            onSelect={() =>
                                                                setPending({
                                                                    template,
                                                                    action: "delete",
                                                                })
                                                            }
                                                        >
                                                            <Trash2 />
                                                            {t("Remove")}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            )}
                                    </CardContent>
                                    <CardFooter className="mt-auto flex flex-wrap gap-2 border-t p-3">
                                        {template.updateVersion && (
                                            <MarketplaceUpdateButton />
                                        )}
                                        {template.pagesUrl && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <a href={template.pagesUrl}>
                                                    <FileText />
                                                    {t("Pages")}
                                                </a>
                                            </Button>
                                        )}
                                        {template.settingsUrl && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <a href={template.settingsUrl}>
                                                    <Settings2 />
                                                    {t("Settings")}
                                                </a>
                                            </Button>
                                        )}
                                        {!template.active &&
                                            !template.legacy && (
                                                <Button
                                                    size="sm"
                                                    className="ms-auto"
                                                    onClick={() =>
                                                        setPending({
                                                            template,
                                                            action: "activate",
                                                        })
                                                    }
                                                >
                                                    {t("Use this template")}
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
                                              ":name :version is an older version that doesn't work with this version of GoBiz, so it wasn't installed. Download the latest version of the template and upload it here.",
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
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                title={
                    pending?.action === "activate"
                        ? t("Switch your website to “:name”?").replace(
                              ":name",
                              pending.template.name,
                          )
                        : t("Remove “:name”?").replace(
                              ":name",
                              pending?.template.name ?? "",
                          )
                }
                description={
                    pending?.action === "activate"
                        ? t(
                              "Visitors see the new design right away. :current stays installed, so you can switch back.",
                          ).replace(
                              ":current",
                              activeTemplateName ?? t("The current template"),
                          )
                        : t(
                              "The template's files are deleted from the server. You can install it again later.",
                          )
                }
                confirmLabel={
                    pending?.action === "activate"
                        ? t("Switch template")
                        : t("Remove")
                }
                destructive={pending?.action === "delete"}
                processing={processing}
                onConfirm={run}
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
        form.post(route("admin.web-template.upload"), {
            forceFormData: true,
            onSuccess: onClose,
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{t("Install template")}</DialogTitle>
                <DialogDescription>
                    {t(
                        "Upload the template's .zip file. If it's already installed, it will be updated.",
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
