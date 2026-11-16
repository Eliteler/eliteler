import { Head, Link, router } from "@inertiajs/react";
import { Package, Plus, Search, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Input } from "@admin/components/ui/input";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { cn } from "@admin/lib/utils";

import { PlanCard } from "./components/plan-card";
import type { Plan, PlanStatus, Plugins } from "./types";

type Tab = "all" | PlanStatus;
type Pending = { plan: Plan; action: "enable" | "disable" | "delete" };

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
    plans: Paginated<Plan> | Plan[];
    tab: Tab;
    search: string;
    counts: Record<Tab, number>;
    plugins: Plugins;
};

export default function PlansIndex({
    plans,
    tab,
    search: initialSearch,
    counts,
    plugins,
}: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(initialSearch);
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);
    const firstRender = useRef(true);

    // Safely extract plans array whether paginated or plain array
    const planList: Plan[] = Array.isArray(plans)
        ? plans
        : plans && Array.isArray(plans.data)
          ? plans.data
          : [];

    // Extract pagination metadata
    const pagination = Array.isArray(plans) ? null : plans;

    const visit = (next: { tab?: Tab; search?: string }) => {
        const nextTab = next.tab ?? tab;
        const value = next.search ?? search;
        router.get(
            route("admin.plans"),
            {
                ...(nextTab !== "all" ? { status: nextTab } : {}),
                ...(value ? { search: value } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const timer = window.setTimeout(() => visit({ search }), 300);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const run = () => {
        if (!pending) return;
        const { plan, action } = pending;
        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        };
        if (action === "delete") {
            router.post(route("admin.delete.plan"), { id: plan.id }, options);
        } else {
            router.post(
                route("admin.status.plan"),
                {
                    id: plan.id,
                    status: action === "enable" ? "active" : "disabled",
                },
                options,
            );
        }
    };

    const dialog = (() => {
        if (!pending) return null;
        const { plan, action } = pending;
        const subscribers =
            plan.subscribers > 0
                ? ` ${t(":count customers are on this plan and keep it until it expires.").replace(":count", String(plan.subscribers))}`
                : "";
        switch (action) {
            case "enable":
                return {
                    title: t("Enable “:name”?"),
                    description: t("Customers can buy this plan again."),
                    label: t("Enable"),
                };
            case "disable":
                return {
                    title: t("Disable “:name”?"),
                    description:
                        t(
                            "The plan is hidden from the pricing page and can't be bought.",
                        ) + subscribers,
                    label: t("Disable"),
                    destructive: true,
                };
            default:
                return {
                    title: t("Delete “:name”?"),
                    description:
                        t(
                            "The plan is removed from this list and can't be bought.",
                        ) + subscribers,
                    label: t("Delete"),
                    destructive: true,
                };
        }
    })();

    const tabs: { value: Tab; label: string }[] = [
        { value: "all", label: t("All") },
        { value: "active", label: t("Active") },
        { value: "disabled", label: t("Disabled") },
    ];

    return (
        <>
            <Head title={t("Plans")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Plans")}
                    description={t(
                        "Subscription plans customers can buy for their business cards and stores.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Plans") },
                    ]}
                    actions={
                        <Button asChild>
                            <Link href={route("admin.add.plan")}>
                                <Plus />
                                {t("New plan")}
                            </Link>
                        </Button>
                    }
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Tabs
                        value={tab}
                        onValueChange={(value) => visit({ tab: value as Tab })}
                    >
                        <TabsList>
                            {tabs.map((item) => (
                                <TabsTrigger
                                    key={item.value}
                                    value={item.value}
                                >
                                    {item.label}
                                    <span className="text-muted-foreground tabular-nums">
                                        {counts[item.value]}
                                    </span>
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                    <div className="relative sm:w-64">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t("Search plans...")}
                            className="ps-8"
                        />
                    </div>
                </div>

                {planList.length === 0 ? (
                    <EmptyState
                        icon={Package}
                        title={search ? t("No plans found") : t("No plans yet")}
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
                    <>
                        <div className="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                            {planList.map((plan) => (
                                <PlanCard
                                    key={plan.id}
                                    plan={plan}
                                    onToggle={() =>
                                        setPending({
                                            plan,
                                            action:
                                                plan.status === "active"
                                                    ? "disable"
                                                    : "enable",
                                        })
                                    }
                                    onDelete={() =>
                                        setPending({ plan, action: "delete" })
                                    }
                                    aiBuilderEnabled={plugins.aiBuilder}
                                    googleWalletInstalled={plugins.googleWallet}
                                    nfcEnabled={plugins.nfc}
                                />
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

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                title={
                    dialog?.title.replace(":name", pending?.plan.name ?? "") ??
                    ""
                }
                description={dialog?.description}
                confirmLabel={dialog?.label}
                destructive={dialog?.destructive}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
