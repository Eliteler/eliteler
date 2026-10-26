import { Head, Link, router } from "@inertiajs/react";
import { Contact, Copy, ExternalLink, Eye, Globe, MoreHorizontal, Search, Store, UserRound, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatNumber } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

import { CopyCardDialog } from "../customers/components/copy-card-dialog";

type CardState = "active" | "inactive" | "disabled";
type Tab = "all" | CardState;

type CardRow = {
    id: string;
    type: "vcard" | "store";
    title: string;
    url: string;
    customDomain: string | null;
    theme: string | null;
    state: CardState;
    visits: number;
    created: string | null;
    owner: { id: string; name: string; email: string; planExpires: string | null; planExpired: boolean };
};

type Filters = { tab: Tab; search: string; plan: string; theme: string; sort: string };

type Props = {
    type: "vcard" | "store";
    cards: Paginated<CardRow>;
    filters: Filters;
    counts: Record<Tab, number>;
    themes: { id: string; name: string }[];
};

const DEFAULTS: Filters = { tab: "all", search: "", plan: "all", theme: "all", sort: "newest" };

const STATE_STYLES: Record<CardState, string> = {
    active: "border-success/30 text-success",
    inactive: "text-muted-foreground",
    disabled: "border-destructive/30 text-destructive",
};

export default function CardsIndex({ type, cards, filters, counts, themes }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(filters.search);
    const [copying, setCopying] = useState<CardRow | null>(null);
    const firstRender = useRef(true);
    const isStore = type === "store";
    const routeName = isStore ? "admin.created.stores" : "admin.created.cards";

    const visit = (next: Partial<Filters>) => {
        const merged = { ...filters, search, ...next };
        const query = Object.fromEntries(
            Object.entries(merged).filter(([key, value]) => value !== DEFAULTS[key as keyof Filters]),
        );
        router.get(route(routeName), query, { preserveState: true, preserveScroll: true, replace: true });
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

    const title = isStore ? t("Stores") : t("Business cards");
    const filtered = (Object.keys(DEFAULTS) as (keyof Filters)[]).some((key) => filters[key] !== DEFAULTS[key]);
    const stateLabel: Record<CardState, string> = {
        active: t("Active"),
        inactive: t("Inactive"),
        disabled: t("Archived"),
    };

    return (
        <>
            <Head title={title} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={title}
                    description={
                        isStore
                            ? t("WhatsApp stores created by your customers.")
                            : t("Digital business cards created by your customers.")
                    }
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Customers"), href: route("admin.customers", { status: "all" }) },
                        { label: title },
                    ]}
                />

                <Tabs value={filters.tab} onValueChange={(tab) => visit({ tab: tab as Tab })}>
                    <TabsList>
                        {(["all", "active", "inactive", "disabled"] as Tab[]).map((tab) => (
                            <TabsTrigger key={tab} value={tab}>
                                {tab === "all" ? t("All") : stateLabel[tab]}
                                <span className="text-muted-foreground tabular-nums">{counts[tab]}</span>
                            </TabsTrigger>
                        ))}
                    </TabsList>
                </Tabs>

                <Card className="gap-0 py-0">
                    <div className="flex flex-col gap-2 border-b p-3 lg:flex-row lg:items-center">
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder={t("Search by title, link, domain or customer...")}
                                className="ps-8"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2 sm:flex">
                            <Select value={filters.theme} onValueChange={(theme) => visit({ theme })}>
                                <SelectTrigger className="w-full sm:w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="all">{t("All themes")}</SelectItem>
                                    {themes.map((theme) => (
                                        <SelectItem key={theme.id} value={theme.id}>
                                            {theme.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select value={filters.plan} onValueChange={(plan) => visit({ plan })}>
                                <SelectTrigger className="w-full sm:w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="all">{t("Any owner plan")}</SelectItem>
                                    <SelectItem value="active">{t("Owner plan active")}</SelectItem>
                                    <SelectItem value="expired">{t("Owner plan expired")}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select value={filters.sort} onValueChange={(sort) => visit({ sort })}>
                                <SelectTrigger className="col-span-2 w-full sm:w-36">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="newest">{t("Newest first")}</SelectItem>
                                    <SelectItem value="oldest">{t("Oldest first")}</SelectItem>
                                    <SelectItem value="visits">{t("Most visited")}</SelectItem>
                                    <SelectItem value="title">{t("Title A–Z")}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {cards.data.length === 0 ? (
                        <EmptyState
                            icon={isStore ? Store : Contact}
                            title={isStore ? t("No stores found") : t("No business cards found")}
                            description={filtered ? t("Try a different search or filter.") : undefined}
                            className="m-4 border-0"
                            action={
                                filtered ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => {
                                            setSearch("");
                                            visit({ ...DEFAULTS });
                                        }}
                                    >
                                        <X />
                                        {t("Clear filters")}
                                    </Button>
                                ) : undefined
                            }
                        />
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{isStore ? t("Store") : t("Card")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Owner")}</TableHead>
                                    <TableHead className="hidden xl:table-cell">{t("Theme")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Status")}</TableHead>
                                    <TableHead className="hidden text-end lg:table-cell">{t("Visits")}</TableHead>
                                    <TableHead className="hidden xl:table-cell">{t("Created")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {cards.data.map((card) => (
                                    <TableRow key={card.id}>
                                        <TableCell className="max-w-[280px] ps-4">
                                            <div className="flex flex-col">
                                                <a
                                                    href={card.url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="truncate font-medium hover:underline"
                                                >
                                                    {card.title}
                                                </a>
                                                <span className="truncate text-xs text-muted-foreground" dir="ltr">
                                                    {card.url.replace(/^https?:\/\//, "")}
                                                </span>
                                                {card.customDomain && (
                                                    <span className="flex items-center gap-1 truncate text-xs text-primary" dir="ltr">
                                                        <Globe className="size-3" />
                                                        {card.customDomain}
                                                    </span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[240px] md:table-cell">
                                            <div className="flex flex-col">
                                                <Link
                                                    href={route("admin.view.customer", card.owner.id)}
                                                    className="truncate text-sm hover:underline"
                                                >
                                                    {card.owner.name}
                                                </Link>
                                                <span
                                                    className={cn(
                                                        "truncate text-xs",
                                                        card.owner.planExpired ? "text-destructive" : "text-muted-foreground",
                                                    )}
                                                >
                                                    {card.owner.planExpired
                                                        ? t("Plan expired")
                                                        : `${t("Plan until")} ${formatDate(card.owner.planExpires)}`}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden text-muted-foreground xl:table-cell">{card.theme ?? "—"}</TableCell>
                                        <TableCell className="hidden sm:table-cell">
                                            <Badge variant="outline" className={STATE_STYLES[card.state]}>
                                                {stateLabel[card.state]}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="hidden text-end tabular-nums lg:table-cell">
                                            {formatNumber(card.visits)}
                                        </TableCell>
                                        <TableCell className="hidden xl:table-cell">
                                            <span className="text-sm" title={formatDate(card.created)}>
                                                {fromNow(card.created)}
                                            </span>
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <a href={card.url} target="_blank" rel="noreferrer">
                                                            <ExternalLink />
                                                            {isStore ? t("Open store") : t("Open card")}
                                                        </a>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route("admin.view.customer", card.owner.id)}>
                                                            <UserRound />
                                                            {t("View owner")}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem onSelect={() => setCopying(card)}>
                                                        <Copy />
                                                        {t("Copy to another customer")}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </Card>

                <Pagination data={cards} />
            </div>

            <CopyCardDialog card={copying} ownerId={copying?.owner.id ?? ""} onClose={() => setCopying(null)} />
        </>
    );
}
