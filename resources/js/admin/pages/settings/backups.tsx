import { Head, router } from "@inertiajs/react";
import {
    Archive,
    ChevronLeft,
    ChevronRight,
    Database,
    FolderArchive,
    Loader2,
    Search,
    Trash2,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
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
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate } from "@admin/lib/date";

type Backup = {
    id: string;
    type: "file" | "database";
    version: string;
    fileName: string;
    sizeBytes: number | null;
    createdAt: string | null;
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

type Kind = "file" | "database";

type Props = {
    backups: PaginatedResponse<Backup>;
    version: string;
    search?: string;
    tab?: "all" | Kind;
};

const formatSize = (bytes: number) => {
    const units = ["B", "KB", "MB", "GB"];
    let size = bytes;
    let unit = 0;
    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit++;
    }
    return `${size.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`;
};

export default function Backups({
    backups,
    version,
    search = "",
    tab = "all",
}: Props) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(search);
    const isFirstRender = useRef(true);

    const [creating, setCreating] = useState<Kind | null>(null);
    const [deleting, setDeleting] = useState<Backup | null>(null);
    const [processing, setProcessing] = useState(false);

    // Real-time debounced search
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timer = setTimeout(() => {
            router.get(
                route("admin.backups"),
                {
                    search: searchTerm || undefined,
                    tab: tab !== "all" ? tab : undefined,
                    per_page: backups.per_page,
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

    // Handle tab change
    const handleTabChange = (newTab: string) => {
        router.get(
            route("admin.backups"),
            {
                tab: newTab !== "all" ? newTab : undefined,
                search: searchTerm || undefined,
                per_page: backups.per_page,
                page: 1,
            },
            { preserveState: true, replace: true },
        );
    };

    // Handle per page limit selection
    const handlePerPageChange = (newPerPage: string) => {
        router.get(
            route("admin.backups"),
            {
                per_page: newPerPage,
                tab: tab !== "all" ? tab : undefined,
                search: searchTerm || undefined,
                page: 1,
            },
            { preserveState: true, replace: true },
        );
    };

    // Navigate to a specific page
    const goToPage = (pageNumber: number) => {
        router.get(
            route("admin.backups"),
            {
                page: pageNumber,
                per_page: backups.per_page,
                tab: tab !== "all" ? tab : undefined,
                search: searchTerm || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const create = (kind: Kind) =>
        router.post(
            route(
                kind === "file"
                    ? "admin.create.file.backup"
                    : "admin.create.database.backup",
            ),
            {},
            {
                preserveScroll: true,
                onStart: () => setCreating(kind),
                onFinish: () => setCreating(null),
            },
        );

    const remove = () => {
        if (!deleting) return;
        router.post(
            route("admin.backup.delete"),
            { id: deleting.id },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setDeleting(null);
                },
            },
        );
    };

    const kinds = [
        {
            kind: "database" as const,
            icon: Database,
            title: t("Database backup"),
            description: t(
                "All your data: customers, cards, plans, payments and settings. Saved as a .sql file on the server.",
            ),
        },
        {
            kind: "file" as const,
            icon: FolderArchive,
            title: t("File backup"),
            description: t(
                "The whole app folder, including uploads and your .env file, as a .zip. Large sites can take several minutes.",
            ),
        },
    ];

    return (
        <>
            <Head title={t("Backups")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Backups")}
                    description={t(
                        "Create and manage backups of your database and files.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Backups") },
                    ]}
                />

                <div className="grid gap-4 md:grid-cols-2">
                    {kinds.map(({ kind, icon: Icon, title, description }) => (
                        <Card key={kind}>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Icon className="size-5 text-muted-foreground" />
                                    {title}
                                </CardTitle>
                                <CardDescription>{description}</CardDescription>
                            </CardHeader>
                            <CardFooter className="mt-auto">
                                <Button
                                    onClick={() => create(kind)}
                                    disabled={creating !== null}
                                >
                                    {creating === kind ? (
                                        <Loader2 className="animate-spin" />
                                    ) : (
                                        <Archive />
                                    )}
                                    {creating === kind
                                        ? t("Creating… keep this page open")
                                        : t("Create backup")}
                                </Button>
                            </CardFooter>
                        </Card>
                    ))}
                </div>

                <Card className="gap-0 py-0">
                    <CardHeader className="flex flex-col gap-3 border-b py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-col gap-1">
                            <CardTitle>{t("Saved backups")}</CardTitle>
                            <CardDescription>
                                {t(
                                    "Stored privately in storage/app/backups. Current version: :version",
                                ).replace(":version", version)}
                            </CardDescription>
                        </div>
                        <Tabs value={tab} onValueChange={handleTabChange}>
                            <TabsList>
                                <TabsTrigger value="all">
                                    {t("All")}
                                </TabsTrigger>
                                <TabsTrigger value="database">
                                    {t("Database")}
                                </TabsTrigger>
                                <TabsTrigger value="file">
                                    {t("Files")}
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </CardHeader>

                    {/* Top Toolbar: Left (Per-page) & Right (Search) - Mobile inline */}
                    <div className="flex flex-row items-center justify-between gap-2 border-b p-3 sm:p-4">
                        {/* Left: Per page selector */}
                        <div className="flex items-center gap-1.5 shrink-0">
                            <span className="hidden text-xs text-muted-foreground sm:inline sm:text-sm">
                                {t("Show")}
                            </span>
                            <Select
                                value={String(backups.per_page)}
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
                                placeholder={t("Search backups...")}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-8 pl-8 text-xs sm:h-9 sm:pl-9 sm:text-sm"
                            />
                        </div>
                    </div>

                    <CardContent className="p-0">
                        {backups.data.length === 0 ? (
                            <EmptyState
                                icon={Archive}
                                title={t("No backups yet")}
                                description={t("Create a backup above.")}
                                className="border-0"
                            />
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="ps-6">
                                            {t("Backup")}
                                        </TableHead>
                                        <TableHead>{t("Version")}</TableHead>
                                        <TableHead>{t("Size")}</TableHead>
                                        <TableHead>{t("Created")}</TableHead>
                                        <TableHead className="pe-6 text-end">
                                            <span className="sr-only">
                                                {t("Actions")}
                                            </span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {backups.data.map((backup) => (
                                        <TableRow key={backup.id}>
                                            <TableCell className="ps-6">
                                                <div className="flex items-center gap-3">
                                                    {backup.type ===
                                                    "database" ? (
                                                        <Database className="size-4 shrink-0 text-muted-foreground" />
                                                    ) : (
                                                        <FolderArchive className="size-4 shrink-0 text-muted-foreground" />
                                                    )}
                                                    <span
                                                        className="max-w-72 truncate font-mono text-xs"
                                                        title={backup.fileName}
                                                    >
                                                        {backup.fileName}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    v{backup.version}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="tabular-nums">
                                                {backup.sizeBytes === null ? (
                                                    <span className="text-destructive">
                                                        {t("File missing")}
                                                    </span>
                                                ) : (
                                                    formatSize(backup.sizeBytes)
                                                )}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {formatDate(
                                                    backup.createdAt,
                                                    "dd MMM yyyy, HH:mm",
                                                )}
                                            </TableCell>
                                            <TableCell className="pe-6">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon-sm"
                                                        className="text-muted-foreground hover:text-destructive"
                                                        onClick={() =>
                                                            setDeleting(backup)
                                                        }
                                                        aria-label={t("Delete")}
                                                        title={t("Delete")}
                                                    >
                                                        <Trash2 />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>

                    {/* Server Pagination Footer */}
                    {backups.total > 0 && (
                        <CardFooter className="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                            <span className="text-xs text-muted-foreground">
                                {t("Showing :from to :to of :total backups")
                                    .replace(":from", String(backups.from ?? 0))
                                    .replace(":to", String(backups.to ?? 0))
                                    .replace(":total", String(backups.total))}
                            </span>

                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(backups.current_page - 1)
                                    }
                                    disabled={backups.current_page <= 1}
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
                                            String(backups.current_page),
                                        )
                                        .replace(
                                            ":total",
                                            String(backups.last_page),
                                        )}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(backups.current_page + 1)
                                    }
                                    disabled={
                                        backups.current_page >=
                                        backups.last_page
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
            </div>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={t("Delete this backup?")}
                description={t(
                    "The file is removed from the server. This can't be undone.",
                )}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}
