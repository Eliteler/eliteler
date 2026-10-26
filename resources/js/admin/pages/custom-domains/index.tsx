import { Head, Link, router } from "@inertiajs/react";
import { ArrowRight, Check, ExternalLink, Globe, MoreHorizontal, RotateCcw, Search, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate, fromNow } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

type Status = "pending" | "approved" | "rejected";
type Tab = "all" | Status;
type Action = "approve" | "reject" | "pending";

type DomainRequest = {
    id: string;
    domain: string;
    previous: string | null;
    status: Status;
    live: boolean;
    card: { title: string; url: string; type: "vcard" | "store" } | null;
    owner: { id: string; name: string; email: string } | null;
    created: string | null;
    updated: string | null;
};

type Props = {
    requests: Paginated<DomainRequest>;
    tab: Tab;
    search: string;
    counts: Record<Tab, number>;
};

const STATUS_STYLES: Record<Status, string> = {
    pending: "border-warning/40 text-warning",
    approved: "border-success/30 text-success",
    rejected: "border-destructive/30 text-destructive",
};

const ACTION_ROUTES: Record<Action, string> = {
    approve: "admin.approved.custom.domain.requests",
    reject: "admin.rejected.custom.domain.requests",
    pending: "admin.process.custom.domain.requests",
};

export default function CustomDomains({ requests, tab, search: initialSearch, counts }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(initialSearch);
    const [pending, setPending] = useState<{ request: DomainRequest; action: Action } | null>(null);
    const [processing, setProcessing] = useState(false);
    const firstRender = useRef(true);

    const visit = (next: { tab?: Tab; search?: string }) => {
        const value = next.search ?? search;
        router.get(route("admin.custom.domain.requests", { status: next.tab ?? tab }), value ? { search: value } : {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const timer = window.setTimeout(() => visit({ search }), 350);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const run = () => {
        if (!pending) return;
        router.post(
            route(ACTION_ROUTES[pending.action]),
            { id: pending.request.id },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setPending(null);
                },
            },
        );
    };

    const statusLabel: Record<Status, string> = {
        pending: t("Pending"),
        approved: t("Approved"),
        rejected: t("Rejected"),
    };

    const dialog = (() => {
        if (!pending) return null;
        const { request, action } = pending;
        const card = request.card?.title ?? t("the card");
        switch (action) {
            case "approve":
                return {
                    title: t("Approve :domain?").replace(":domain", request.domain),
                    description: t(
                        "“:card” will be served on this domain. Any other domain approved for this card is rejected. The customer is emailed.",
                    ).replace(":card", card),
                    label: t("Approve"),
                    destructive: false,
                };
            case "reject":
                return {
                    title: t("Reject :domain?").replace(":domain", request.domain),
                    description: request.live
                        ? t("This domain is live. “:card” will stop being served on it. The customer is emailed.").replace(":card", card)
                        : t("The customer is emailed that the request was rejected."),
                    label: t("Reject"),
                    destructive: true,
                };
            default:
                return {
                    title: t("Move back to pending?"),
                    description: request.live
                        ? t("This domain is live. “:card” will stop being served on it until approved again. The customer is emailed.").replace(
                              ":card",
                              card,
                          )
                        : t("The request goes back to the pending queue. The customer is emailed."),
                    label: t("Move to pending"),
                    destructive: request.live,
                };
        }
    })();

    return (
        <>
            <Head title={t("Custom domains")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Custom domains")}
                    description={t("Review domains customers want to connect to their business cards and stores.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Customers"), href: route("admin.customers", { status: "all" }) },
                        { label: t("Custom domains") },
                    ]}
                />

                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <Tabs value={tab} onValueChange={(value) => visit({ tab: value as Tab })}>
                        <TabsList>
                            {(["all", "pending", "approved", "rejected"] as Tab[]).map((item) => (
                                <TabsTrigger key={item} value={item}>
                                    {item === "all" ? t("All") : statusLabel[item]}
                                    <span
                                        className={cn(
                                            "tabular-nums",
                                            item === "pending" && counts.pending > 0 ? "font-semibold text-warning" : "text-muted-foreground",
                                        )}
                                    >
                                        {counts[item]}
                                    </span>
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                    <div className="relative lg:w-72">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t("Search domain, card or customer...")}
                            className="ps-8"
                        />
                    </div>
                </div>

                {requests.data.length === 0 ? (
                    <EmptyState
                        icon={Globe}
                        title={search ? t("No requests found") : t("No custom domain requests")}
                        description={
                            search ? t("Try a different search.") : t("Requests appear here when customers connect their own domain.")
                        }
                        action={
                            search ? (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setSearch("");
                                        visit({ search: "" });
                                    }}
                                >
                                    <X />
                                    {t("Clear search")}
                                </Button>
                            ) : undefined
                        }
                    />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Domain")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Card / store")}</TableHead>
                                    <TableHead>{t("Status")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Requested")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {requests.data.map((request) => (
                                    <TableRow key={request.id}>
                                        <TableCell className="max-w-[280px] ps-4">
                                            <div className="flex flex-col gap-0.5" dir="ltr">
                                                <a
                                                    href={`https://${request.domain}`}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="flex items-center gap-1.5 truncate font-medium hover:underline"
                                                >
                                                    <Globe className="size-3.5 shrink-0 text-muted-foreground" />
                                                    {request.domain}
                                                </a>
                                                {request.previous && (
                                                    <span className="flex items-center gap-1 truncate text-xs text-muted-foreground">
                                                        {request.previous}
                                                        <ArrowRight className="size-3" />
                                                        {t("replaces")}
                                                    </span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[260px] md:table-cell">
                                            {request.card ? (
                                                <div className="flex flex-col">
                                                    <a
                                                        href={request.card.url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="truncate text-sm hover:underline"
                                                    >
                                                        {request.card.title}
                                                    </a>
                                                    {request.owner && (
                                                        <Link
                                                            href={route("admin.view.customer", request.owner.id)}
                                                            className="truncate text-xs text-muted-foreground hover:underline"
                                                        >
                                                            {request.owner.name}
                                                        </Link>
                                                    )}
                                                </div>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Card deleted")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                <Badge variant="outline" className={STATUS_STYLES[request.status]}>
                                                    {statusLabel[request.status]}
                                                </Badge>
                                                {request.live && (
                                                    <Badge variant="secondary">
                                                        <span className="size-1.5 rounded-full bg-success" />
                                                        {t("Live")}
                                                    </Badge>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden lg:table-cell">
                                            <span className="text-sm" title={formatDate(request.created, "dd MMM yyyy, HH:mm")}>
                                                {fromNow(request.created)}
                                            </span>
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            {request.status === "pending" ? (
                                                <div className="flex justify-end gap-1">
                                                    <Button size="sm" variant="outline" onClick={() => setPending({ request, action: "reject" })}>
                                                        {t("Reject")}
                                                    </Button>
                                                    <Button size="sm" onClick={() => setPending({ request, action: "approve" })}>
                                                        {t("Approve")}
                                                    </Button>
                                                </div>
                                            ) : (
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                            <MoreHorizontal />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <a href={`https://${request.domain}`} target="_blank" rel="noreferrer">
                                                                <ExternalLink />
                                                                {t("Open domain")}
                                                            </a>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        {request.status === "rejected" && (
                                                            <DropdownMenuItem onSelect={() => setPending({ request, action: "approve" })}>
                                                                <Check />
                                                                {t("Approve")}
                                                            </DropdownMenuItem>
                                                        )}
                                                        <DropdownMenuItem onSelect={() => setPending({ request, action: "pending" })}>
                                                            <RotateCcw />
                                                            {t("Move back to pending")}
                                                        </DropdownMenuItem>
                                                        {request.status === "approved" && (
                                                            <DropdownMenuItem
                                                                variant="destructive"
                                                                onSelect={() => setPending({ request, action: "reject" })}
                                                            >
                                                                <X />
                                                                {t("Reject")}
                                                            </DropdownMenuItem>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                <Pagination data={requests} />
            </div>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                title={dialog?.title ?? ""}
                description={dialog?.description}
                confirmLabel={dialog?.label}
                destructive={dialog?.destructive}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
