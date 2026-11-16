import { Head, Link, router } from "@inertiajs/react";
import { Smartphone } from "lucide-react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Card } from "@admin/components/ui/card";
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
import { formatDate, fromNow } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";

type Row = {
    id: number;
    order: string | null;
    platform: "android" | "ios" | string;
    type: "one_time" | "subscription";
    item: string | null;
    kind: "plan" | "ai";
    currency: string;
    amount: number;
    status: string;
    date: string | null;
    customer: { id: string; name: string; email: string } | null;
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

type Props = {
    purchases: Paginated<Row> | Row[];
    filters: { tab: string; platform: string; search: string };
    counts: Record<string, number>;
};

export default function InAppPurchases({ purchases, filters, counts }: Props) {
    const { t } = useTranslation();
    const statusLabel: Record<string, string> = {
        success: t("Paid"),
        active: t("Active"),
        pending: t("Pending"),
        expired: t("Expired"),
        canceled: t("Cancelled"),
        cancelled: t("Cancelled"),
    };

    // Safely extract purchases array whether paginated or plain array
    const purchaseList: Row[] = Array.isArray(purchases)
        ? purchases
        : purchases && Array.isArray(purchases.data)
          ? purchases.data
          : [];

    // Extract pagination metadata
    const pagination = Array.isArray(purchases) ? null : purchases;

    const setPlatform = (platform: string) =>
        router.get(
            route("admin.in-app-purchases"),
            {
                ...(filters.tab !== "all" ? { tab: filters.tab } : {}),
                ...(platform !== "all" ? { platform } : {}),
                ...(filters.search ? { search: filters.search } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );

    return (
        <>
            <Head title={t("In-app purchases")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("In-app purchases")}
                    description={t(
                        "Plans and AI credits bought in the mobile app through the App Store or Google Play. Refunds and cancellations are handled in the store.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("In-app purchases") },
                    ]}
                />
                <ListToolbar
                    routeName="admin.in-app-purchases"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "active", label: t("Active") },
                        { value: "pending", label: t("Pending") },
                        { value: "ended", label: t("Expired or cancelled") },
                    ]}
                    tab={filters.tab}
                    counts={counts}
                    search={filters.search}
                    extraQuery={
                        filters.platform !== "all"
                            ? { platform: filters.platform }
                            : {}
                    }
                    placeholder={t("Search order ID or customer...")}
                >
                    <Select
                        value={filters.platform}
                        onValueChange={setPlatform}
                    >
                        <SelectTrigger className="w-36">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent align="end">
                            <SelectItem value="all">
                                {t("All platforms")}
                            </SelectItem>
                            <SelectItem value="android">Android</SelectItem>
                            <SelectItem value="ios">iOS</SelectItem>
                        </SelectContent>
                    </Select>
                </ListToolbar>
                {purchaseList.length === 0 ? (
                    <EmptyState
                        icon={Smartphone}
                        title={t("No in-app purchases yet")}
                        description={t(
                            "Purchases made in the mobile app appear here.",
                        )}
                    />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">
                                        {t("Purchase")}
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        {t("Customer")}
                                    </TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        {t("Platform")}
                                    </TableHead>
                                    <TableHead className="text-end">
                                        {t("Amount")}
                                    </TableHead>
                                    <TableHead className="pe-4">
                                        {t("Status")}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {purchaseList.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="max-w-[240px] ps-4">
                                            <span className="block truncate text-sm font-medium">
                                                {row.item ?? t("Deleted item")}
                                                <span className="ms-1.5 text-xs font-normal text-muted-foreground">
                                                    {row.kind === "ai"
                                                        ? t("AI credits")
                                                        : t("Plan")}{" "}
                                                    ·{" "}
                                                    {row.type === "one_time"
                                                        ? t("One-time")
                                                        : t("Subscription")}
                                                </span>
                                            </span>
                                            <span
                                                className="block truncate text-xs text-muted-foreground"
                                                title={formatDate(
                                                    row.date
                                                )}
                                            >
                                                {row.order ?? "—"} ·{" "}
                                                {fromNow(row.date)}
                                            </span>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[220px] md:table-cell">
                                            {row.customer ? (
                                                <Link
                                                    href={route(
                                                        "admin.view.customer",
                                                        row.customer.id,
                                                    )}
                                                    className="flex flex-col hover:underline"
                                                >
                                                    <span className="truncate text-sm">
                                                        {row.customer.name}
                                                    </span>
                                                    <span className="truncate text-xs text-muted-foreground">
                                                        {row.customer.email}
                                                    </span>
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">
                                                    {t("Deleted customer")}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden sm:table-cell">
                                            {row.platform === "ios"
                                                ? "iOS"
                                                : row.platform === "android"
                                                  ? "Android"
                                                  : row.platform}
                                        </TableCell>
                                        <TableCell className="text-end font-medium tabular-nums">
                                            {row.currency}{" "}
                                            {row.amount.toLocaleString(
                                                undefined,
                                                { minimumFractionDigits: 2 },
                                            )}
                                        </TableCell>
                                        <TableCell className="pe-4">
                                            <Badge
                                                variant="outline"
                                                className={cn(
                                                    [
                                                        "success",
                                                        "active",
                                                    ].includes(row.status) &&
                                                        "border-success/30 text-success",
                                                    row.status === "pending" &&
                                                        "border-warning/40 text-warning",
                                                    [
                                                        "expired",
                                                        "canceled",
                                                        "cancelled",
                                                    ].includes(row.status) &&
                                                        "text-muted-foreground",
                                                )}
                                            >
                                                {statusLabel[row.status] ??
                                                    row.status}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                {/* Pagination navigation controls */}
                {pagination && pagination.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            {t("Showing :from to :to of :total results")
                                .replace(":from", String(pagination.from ?? 0))
                                .replace(":to", String(pagination.to ?? 0))
                                .replace(":total", String(pagination.total))}
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
            </div>
        </>
    );
}
