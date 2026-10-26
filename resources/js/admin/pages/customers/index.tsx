import { Head, Link, router } from "@inertiajs/react";
import { Contact, Plus, Search, Store, Users, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { initials } from "@admin/components/nav-user";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Avatar, AvatarFallback, AvatarImage } from "@admin/components/ui/avatar";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate, fromNow } from "@admin/lib/date";
import type { Paginated } from "@admin/types";

import { CustomerActions } from "./components/customer-actions";
import { PlanCell, StateBadge, UnverifiedBadge } from "./components/customer-badges";
import type { CustomerRow, CustomerTab, PlanOption } from "./types";

type Filters = { search: string; plan: string; subscription: string; sort: string };

type Props = {
    customers: Paginated<CustomerRow>;
    tab: CustomerTab;
    counts: Record<CustomerTab, number>;
    filters: Filters;
    plans: PlanOption[];
};

const TABS: { value: CustomerTab; label: string }[] = [
    { value: "all", label: "All" },
    { value: "verified", label: "Verified" },
    { value: "unverified", label: "Unverified" },
    { value: "inactive", label: "Inactive" },
    { value: "banned", label: "Suspended" },
    { value: "deleted", label: "Deleted" },
];

const DEFAULTS: Filters = { search: "", plan: "all", subscription: "all", sort: "newest" };

export default function CustomersIndex({ customers, tab, counts, filters, plans }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(filters.search);
    const firstRender = useRef(true);

    const visit = (next: Partial<Filters> & { tab?: CustomerTab }) => {
        const { tab: nextTab = tab, ...rest } = next;
        const merged = { ...filters, search, ...rest };
        const query = Object.fromEntries(
            Object.entries(merged).filter(([key, value]) => value !== DEFAULTS[key as keyof Filters]),
        );
        router.get(route("admin.customers", { status: nextTab }), query, {
            preserveState: false,
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

    const filtered = (Object.keys(DEFAULTS) as (keyof Filters)[]).some((key) => filters[key] !== DEFAULTS[key]);

    return (
        <>
            <Head title={t("Customers")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Customers")}
                    description={t("Everyone who signed up for a business card or store.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Customers") }]}
                    actions={
                        <Button asChild>
                            <Link href={route("admin.create.customer")}>
                                <Plus />
                                {t("Add customer")}
                            </Link>
                        </Button>
                    }
                />

                <div className="overflow-x-auto">
                    <Tabs value={tab} onValueChange={(value) => visit({ tab: value as CustomerTab })}>
                        <TabsList>
                            {TABS.map((item) => (
                                <TabsTrigger key={item.value} value={item.value}>
                                    {t(item.label)}
                                    <span className="text-muted-foreground tabular-nums">{counts[item.value]}</span>
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                </div>

                <Card className="gap-0 py-0">
                    <div className="flex flex-col gap-2 border-b p-3 lg:flex-row lg:items-center">
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder={t("Search by name, email or customer ID...")}
                                className="ps-8"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2 sm:flex">
                            <Select value={filters.plan} onValueChange={(plan) => visit({ plan })}>
                                <SelectTrigger className="w-full sm:w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="all">{t("All plans")}</SelectItem>
                                    <SelectItem value="none">{t("No plan")}</SelectItem>
                                    {plans.map((plan) => (
                                        <SelectItem key={plan.id} value={plan.id}>
                                            {plan.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select value={filters.subscription} onValueChange={(subscription) => visit({ subscription })}>
                                <SelectTrigger className="w-full sm:w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="all">{t("Any subscription")}</SelectItem>
                                    <SelectItem value="active">{t("Plan active")}</SelectItem>
                                    <SelectItem value="expiring">{t("Expiring in 7 days")}</SelectItem>
                                    <SelectItem value="expired">{t("Plan expired")}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select value={filters.sort} onValueChange={(sort) => visit({ sort })}>
                                <SelectTrigger className="col-span-2 w-full sm:w-36">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent align="end">
                                    <SelectItem value="newest">{t("Newest first")}</SelectItem>
                                    <SelectItem value="oldest">{t("Oldest first")}</SelectItem>
                                    <SelectItem value="name">{t("Name A–Z")}</SelectItem>
                                    <SelectItem value="expiring">{t("Plan ending soonest")}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {customers.data.length === 0 ? (
                        <EmptyState
                            icon={Users}
                            title={t("No customers found")}
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
                                    <TableHead className="ps-4">{t("Customer")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Plan")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Cards & stores")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Status")}</TableHead>
                                    <TableHead className="hidden xl:table-cell">{t("Joined")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {customers.data.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell className="ps-4">
                                            <Link
                                                href={route("admin.view.customer", customer.id)}
                                                className="flex max-w-[280px] items-center gap-3"
                                            >
                                                <Avatar className="size-9">
                                                    {customer.avatar && <AvatarImage src={customer.avatar} alt={customer.name} />}
                                                    <AvatarFallback className="text-xs">{initials(customer.name)}</AvatarFallback>
                                                </Avatar>
                                                <span className="flex min-w-0 flex-col">
                                                    <span className="truncate font-medium hover:underline">{customer.name}</span>
                                                    <span className="truncate text-xs text-muted-foreground">{customer.email}</span>
                                                </span>
                                            </Link>
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <PlanCell plan={customer.plan} />
                                        </TableCell>
                                        <TableCell className="hidden lg:table-cell">
                                            <span className="flex items-center gap-3 text-sm text-muted-foreground tabular-nums">
                                                <span className="flex items-center gap-1" title={t("Business cards")}>
                                                    <Contact className="size-3.5" />
                                                    {customer.vcards}
                                                </span>
                                                <span className="flex items-center gap-1" title={t("Stores")}>
                                                    <Store className="size-3.5" />
                                                    {customer.stores}
                                                </span>
                                            </span>
                                        </TableCell>
                                        <TableCell className="hidden sm:table-cell">
                                            <div className="flex flex-wrap gap-1">
                                                <StateBadge state={customer.state} />
                                                {!customer.verified && customer.state === "active" && <UnverifiedBadge />}
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden xl:table-cell">
                                            <span className="text-sm" title={formatDate(customer.joined)}>
                                                {fromNow(customer.joined)}
                                            </span>
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <CustomerActions customer={customer} plans={plans} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </Card>

                <Pagination data={customers} />
            </div>
        </>
    );
}
