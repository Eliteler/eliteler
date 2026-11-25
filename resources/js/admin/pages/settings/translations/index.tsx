import { Head, Link, router, useForm } from "@inertiajs/react";
import {
    ChevronLeft,
    ChevronRight,
    Download,
    FileArchive,
    Loader2,
    MoreHorizontal,
    Pencil,
    Plus,
    Search,
    Square,
    Trash2,
    Upload,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@admin/components/ui/card";
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
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Progress } from "@admin/components/ui/progress";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@admin/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@admin/components/ui/table";
import { cn } from "@admin/lib/utils";

export type SyncState = {
    state: "queued" | "running" | "completed" | "failed" | "stopped";
    message?: string;
} | null;

type Language = {
    code: string;
    name: string;
    isSource: boolean;
    isDefault?: boolean;
    enabled: boolean;
    total: number;
    done: number;
    sync: SyncState;
};

type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type Props = {
    languages: PaginatedResponse<Language>;
    source: string;
    search?: string;
};

const isValidLocale = (code?: string): boolean => {
    return typeof code === "string" && /^[a-z]{2,3}(_[A-Za-z]{2,4})?$/.test(code);
};

export default function Translations({
    languages,
    source,
    search = "",
}: Props) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(search);
    const isFirstRender = useRef(true);

    const [adding, setAdding] = useState(() =>
        new URLSearchParams(window.location.search).has("new"),
    );
    const [importing, setImporting] = useState<string | null>(null);
    const [deleting, setDeleting] = useState<Language | null>(null);
    const [processing, setProcessing] = useState(false);

    const syncing = languages.data.some(
        (language) =>
            language.sync &&
            ["queued", "running"].includes(language.sync.state),
    );

    // Refresh progress while a background translation runs
    useEffect(() => {
        if (!syncing) return;
        const timer = window.setInterval(
            () => router.reload({ only: ["languages"] }),
            5000,
        );
        return () => window.clearInterval(timer);
    }, [syncing]);

    // Handle real-time debounced search
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timer = setTimeout(() => {
            router.get(
                route("translation-manager.index"),
                {
                    search: searchTerm || undefined,
                    per_page: languages.per_page,
                    page: 1,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [searchTerm]);

    // Change per-page records count
    const handlePerPageChange = (newPerPage: string) => {
        router.get(
            route("translation-manager.index"),
            {
                per_page: newPerPage,
                search: searchTerm || undefined,
                page: 1,
            },
            { preserveState: true, replace: true },
        );
    };

    // Go to a specific page
    const goToPage = (pageNumber: number) => {
        router.get(
            route("translation-manager.index"),
            {
                page: pageNumber,
                per_page: languages.per_page,
                search: searchTerm || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const remove = () => {
        if (!deleting || !isValidLocale(deleting.code)) return;
        router.delete(route("translation-manager.destroy", deleting.code), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    return (
        <>
            <Head title={t("Translations")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Translations")}
                    description={t(
                        "Every language your website and admin panel can be shown in. :source is the original text the others are translated from.",
                    ).replace(
                        ":source",
                        languages.data.find((language) => language.isSource)
                            ?.name ?? source.toUpperCase(),
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Translations") },
                    ]}
                    actions={
                        <Button onClick={() => setAdding(true)}>
                            <Plus />
                            {t("Add language")}
                        </Button>
                    }
                />

                <Card className="gap-0 py-0">
                    {/* Top Toolbar: Left (Per-page) & Right (Search) */}
                    <CardHeader className="flex flex-row items-center justify-between gap-2 border-b p-3 sm:p-4">
                        {/* Left: Per page selector */}
                        <div className="flex items-center gap-1.5 shrink-0">
                            <span className="hidden text-xs text-muted-foreground sm:inline sm:text-sm">
                                {t("Show")}
                            </span>
                            <Select
                                value={String(languages.per_page)}
                                onValueChange={handlePerPageChange}
                            >
                                <SelectTrigger className="h-8 w-16 text-xs sm:h-9 sm:w-20 sm:text-sm">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="10">10</SelectItem>
                                    <SelectItem value="25">25</SelectItem>
                                    <SelectItem value="50">50</SelectItem>
                                    <SelectItem value="100">100</SelectItem>
                                </SelectContent>
                            </Select>
                            <span className="text-xs text-muted-foreground sm:text-sm">
                                {t("entries")}
                            </span>
                        </div>

                        {/* Right: Real-time search box */}
                        <div className="relative w-full max-w-[160px] sm:max-w-xs">
                            <Search className="absolute left-2.5 top-2 size-3.5 text-muted-foreground sm:top-2.5 sm:size-4" />
                            <Input
                                type="search"
                                placeholder={t("Search...")}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-8 pl-8 text-xs sm:h-9 sm:pl-9 sm:text-sm"
                            />
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="ps-3 sm:ps-4">
                                        {t("Language")}
                                    </TableHead>
                                    <TableHead className="w-24 sm:w-60">
                                        {t("Translated")}
                                    </TableHead>
                                    <TableHead className="w-28 sm:w-36">{t("Status")}</TableHead>
                                    <TableHead className="pe-3 sm:pe-4 text-end">
                                        <span className="sr-only">
                                            {t("Actions")}
                                        </span>
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {languages.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="h-24 text-center"
                                        >
                                            {t("No languages found.")}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    languages.data.map((language) => {
                                        const percent = language.total
                                            ? Math.floor(
                                                  (language.done /
                                                      language.total) *
                                                      100,
                                              )
                                            : 0;
                                        const busy =
                                            language.sync &&
                                            ["queued", "running"].includes(
                                                language.sync.state,
                                            );
                                        return (
                                            <TableRow key={language.code}>
                                                <TableCell className="ps-3 sm:ps-4">
                                                    <Link
                                                        href={
                                                            isValidLocale(language.code)
                                                                ? route(
                                                                      "translation-manager.edit",
                                                                      language.code,
                                                                  )
                                                                : "#"
                                                        }
                                                        className="flex flex-col sm:flex-row sm:items-center gap-0.5 sm:gap-2 font-medium hover:underline"
                                                    >
                                                        <span className="text-xs sm:text-sm font-medium leading-tight">
                                                            {language.name}
                                                        </span>
                                                        <span className="font-mono text-[10px] sm:text-xs font-normal text-muted-foreground">
                                                            {language.code}
                                                        </span>
                                                    </Link>
                                                </TableCell>
                                                <TableCell>
                                                    {language.isSource ? (
                                                        <span className="text-[11px] sm:text-sm text-muted-foreground whitespace-nowrap">
                                                            {t(
                                                                ":count texts",
                                                            ).replace(
                                                                ":count",
                                                                language.total.toLocaleString(),
                                                            )}
                                                        </span>
                                                    ) : (
                                                        <div className="flex items-center gap-1.5 sm:gap-3">
                                                            <Progress
                                                                value={percent}
                                                                className={cn(
                                                                    "h-1.5 w-12 sm:w-32",
                                                                    percent ===
                                                                        100 &&
                                                                        "[&>div]:bg-success",
                                                                )}
                                                            />
                                                            <span className="text-[10px] sm:text-xs text-muted-foreground tabular-nums">
                                                                {percent}%
                                                            </span>
                                                        </div>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-1 sm:gap-1.5 flex-nowrap whitespace-nowrap">
                                                        {language.isDefault && (
                                                            <Badge className="px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs">
                                                                {t("Default")}
                                                            </Badge>
                                                        )}
                                                        {language.isSource && !language.isDefault && (
                                                            <Badge variant="secondary" className="px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs">
                                                                {t("Source")}
                                                            </Badge>
                                                        )}
                                                        {language.enabled ? (
                                                            <Badge
                                                                variant="outline"
                                                                className="border-success/30 text-success px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs"
                                                            >
                                                                {t("Active")}
                                                            </Badge>
                                                        ) : (
                                                            <Badge
                                                                variant="outline"
                                                                className="text-muted-foreground px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs"
                                                            >
                                                                {t(
                                                                    "Inactive",
                                                                )}
                                                            </Badge>
                                                        )}
                                                        {busy && (
                                                            <Badge
                                                                variant="secondary"
                                                                className="px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs"
                                                                title={
                                                                    language
                                                                        .sync
                                                                        ?.message
                                                                }
                                                            >
                                                                <Loader2 className="animate-spin" />
                                                                {t(
                                                                    "Translating…",
                                                                )}
                                                            </Badge>
                                                        )}
                                                        {language.sync
                                                            ?.state ===
                                                            "failed" && (
                                                            <Badge
                                                                variant="destructive"
                                                                className="px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs"
                                                                title={
                                                                    language
                                                                        .sync
                                                                        .message
                                                                }
                                                            >
                                                                {t(
                                                                    "Auto-translate failed",
                                                                )}
                                                            </Badge>
                                                        )}
                                                        {language.sync
                                                            ?.state ===
                                                            "stopped" && (
                                                            <Badge
                                                                variant="outline"
                                                                className="px-1.5 py-0 text-[10px] sm:px-2.5 sm:py-0.5 sm:text-xs text-muted-foreground"
                                                            >
                                                                {t("Stopped")}
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="pe-3 sm:pe-4 text-end">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            className="h-7 px-2 text-xs sm:h-8 sm:px-3 sm:text-sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={
                                                                    isValidLocale(language.code)
                                                                        ? route(
                                                                              "translation-manager.edit",
                                                                              language.code,
                                                                          )
                                                                        : "#"
                                                                }
                                                            >
                                                                <Pencil className="size-3.5 sm:size-4" />
                                                                <span className="hidden sm:inline">
                                                                    {t("Edit")}
                                                                </span>
                                                            </Link>
                                                        </Button>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger
                                                                asChild
                                                            >
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon-sm"
                                                                    className="h-7 w-7 sm:h-8 sm:w-8"
                                                                    aria-label={t(
                                                                        "Actions",
                                                                    )}
                                                                >
                                                                    <MoreHorizontal className="size-3.5 sm:size-4" />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                {busy && (
                                                                    <>
                                                                        <DropdownMenuItem
                                                                            variant="destructive"
                                                                            onSelect={() =>
                                                                                router.post(
                                                                                    route(
                                                                                        "translation-manager.stop-sync",
                                                                                        language.code,
                                                                                    ),
                                                                                    {},
                                                                                    { preserveScroll: true },
                                                                                )
                                                                            }
                                                                        >
                                                                            <Square />
                                                                            {t(
                                                                                "Stop auto-translate",
                                                                            )}
                                                                        </DropdownMenuItem>
                                                                        <DropdownMenuSeparator />
                                                                    </>
                                                                )}
                                                                <DropdownMenuItem
                                                                    asChild
                                                                >
                                                                    <a
                                                                        href={
                                                                            isValidLocale(language.code)
                                                                                ? route(
                                                                                      "translation-manager.export",
                                                                                      language.code,
                                                                                  )
                                                                                : "#"
                                                                        }
                                                                    >
                                                                        <Download />
                                                                        {t(
                                                                            "Export as .zip",
                                                                        )}
                                                                    </a>
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem
                                                                    onSelect={() =>
                                                                        setImporting(
                                                                            language.code,
                                                                        )
                                                                    }
                                                                >
                                                                    <Upload />
                                                                    {t(
                                                                        "Import .zip",
                                                                    )}
                                                                </DropdownMenuItem>
                                                                {!language.isSource && !language.isDefault && (
                                                                    <>
                                                                        <DropdownMenuSeparator />
                                                                        <DropdownMenuItem
                                                                            variant="destructive"
                                                                            onSelect={() =>
                                                                                setDeleting(
                                                                                    language,
                                                                                )
                                                                            }
                                                                        >
                                                                            <Trash2 />
                                                                            {t(
                                                                                "Delete",
                                                                            )}
                                                                        </DropdownMenuItem>
                                                                    </>
                                                                )}
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>

                    {/* Pagination Footer */}
                    {languages.total > 0 && (
                        <CardFooter className="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                            <span className="text-xs text-muted-foreground">
                                {t("Showing :from to :to of :total languages")
                                    .replace(
                                        ":from",
                                        String(languages.from ?? 0),
                                    )
                                    .replace(":to", String(languages.to ?? 0))
                                    .replace(":total", String(languages.total))}
                            </span>

                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(languages.current_page - 1)
                                    }
                                    disabled={languages.current_page <= 1}
                                >
                                    <ChevronLeft className="size-4" />
                                    <span className="sr-only sm:not-sr-only sm:ms-1">
                                        {t("Previous")}
                                    </span>
                                </Button>

                                <span className="text-xs font-medium px-2">
                                    {t("Page :current of :total")
                                        .replace(
                                            ":current",
                                            String(languages.current_page),
                                        )
                                        .replace(
                                            ":total",
                                            String(languages.last_page),
                                        )}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(languages.current_page + 1)
                                    }
                                    disabled={
                                        languages.current_page >=
                                        languages.last_page
                                    }
                                >
                                    <span className="sr-only sm:not-sr-only sm:me-1">
                                        {t("Next")}
                                    </span>
                                    <ChevronRight className="size-4" />
                                </Button>
                            </div>
                        </CardFooter>
                    )}
                </Card>
                <p className="text-xs text-muted-foreground">
                    {t(
                        "“Not offered” languages have files but aren't in the language list customers can pick from. Turn them on in General settings.",
                    )}
                </p>
            </div>

            <Dialog open={adding} onOpenChange={setAdding}>
                <DialogContent className="sm:max-w-md">
                    {adding && (
                        <AddLanguage
                            languages={languages.data}
                            source={source}
                            onClose={() => setAdding(false)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <Dialog
                open={importing !== null}
                onOpenChange={(open) => !open && setImporting(null)}
            >
                <DialogContent className="sm:max-w-md">
                    {importing && (
                        <ImportLanguage
                            locale={importing}
                            name={
                                languages.data.find(
                                    (language) => language.code === importing,
                                )?.name ?? importing
                            }
                            onClose={() => setImporting(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={t("Delete :name?").replace(
                    ":name",
                    deleting?.name ?? "",
                )}
                description={t(
                    "Its translation files are deleted and it's removed from the language list. Export it first if you might need it again.",
                )}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}

function AddLanguage({
    languages,
    source,
    onClose,
}: {
    languages: Language[];
    source: string;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm({ name: "", code: "", copy_from: "none" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("translation-manager.store"));
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{t("Add language")}</DialogTitle>
                <DialogDescription>
                    {t(
                        "It starts as a copy of another language, then you translate it.",
                    )}
                </DialogDescription>
            </DialogHeader>
            <div className="grid gap-4 sm:grid-cols-[1fr_7rem]">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="name">{t("Name")}</Label>
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(event) =>
                            form.setData("name", event.target.value)
                        }
                        placeholder={t("e.g., English UK")}
                        aria-invalid={!!form.errors.name}
                        required
                    />
                    <FieldError message={form.errors.name} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="code">{t("Code")}</Label>
                    <Input
                        id="code"
                        value={form.data.code}
                        onChange={(event) =>
                            form.setData("code", event.target.value.trim())
                        }
                        placeholder={t("e.g., en_GB")}
                        className="font-mono"
                        aria-invalid={!!form.errors.code}
                        required
                    />
                </div>
            </div>
            <FieldError message={form.errors.code} />
            <p className="-mt-3 text-xs text-muted-foreground">
                {t("Use the 2-letter code, or a regional code like pt_BR.")}
            </p>
            <div className="flex flex-col gap-2">
                <Label>{t("Duplicate words from")}</Label>
                <Select
                    value={form.data.copy_from}
                    onValueChange={(value) => form.setData("copy_from", value)}
                >
                    <SelectTrigger className="w-full">
                        <SelectValue placeholder={t("Don't duplicate from any language")} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">
                            {t("Don't duplicate from any language")}
                        </SelectItem>
                        {languages.map((language) => (
                            <SelectItem
                                key={language.code}
                                value={language.code}
                            >
                                {language.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <FieldError message={form.errors.copy_from} />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Add language")}
                </Button>
            </div>
        </form>
    );
}

function ImportLanguage({
    locale,
    name,
    onClose,
}: {
    locale: string;
    name: string;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const input = useRef<HTMLInputElement>(null);
    const form = useForm<{ locale: string; zip_file: File | null }>({
        locale,
        zip_file: null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("translation-manager.import"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {t("Import :name").replace(":name", name)}
                </DialogTitle>
                <DialogDescription>
                    {t(
                        "Upload a .zip exported from this page. Matching files replace the current ones.",
                    )}
                </DialogDescription>
            </DialogHeader>
            <button
                type="button"
                onClick={() => input.current?.click()}
                className={cn(
                    "flex flex-col items-center gap-2 rounded-lg border border-dashed p-6 text-center hover:bg-muted/50",
                    form.errors.zip_file && "border-destructive",
                )}
            >
                <FileArchive className="size-8 text-muted-foreground" />
                <span className="text-sm">
                    {form.data.zip_file?.name ?? t("Choose a .zip file")}
                </span>
            </button>
            <input
                ref={input}
                type="file"
                accept=".zip"
                className="hidden"
                onChange={(event) =>
                    form.setData("zip_file", event.target.files?.[0] ?? null)
                }
            />
            <FieldError message={form.errors.zip_file ?? form.errors.locale} />
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button
                    type="submit"
                    disabled={!form.data.zip_file || form.processing}
                >
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Import")}
                </Button>
            </div>
        </form>
    );
}
