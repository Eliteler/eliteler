import { router } from "@inertiajs/react";
import { Search } from "lucide-react";
import { useEffect, useRef, useState, type ReactNode } from "react";

import { Input } from "@admin/components/ui/input";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { cn } from "@admin/lib/utils";

/**
 * Tabs with counts + debounced search, kept in the query string.
 */
export function ListToolbar({
    routeName,
    tabs,
    tab,
    counts,
    search: initialSearch,
    placeholder,
    extraQuery = {},
    highlight,
    children,
}: {
    routeName: string;
    tabs: { value: string; label: string }[];
    tab: string;
    counts: Record<string, number>;
    search: string;
    placeholder: string;
    extraQuery?: Record<string, string>;
    highlight?: string;
    children?: ReactNode;
}) {
    const [search, setSearch] = useState(initialSearch);
    const firstRender = useRef(true);

    const visit = (next: { tab?: string; search?: string }) => {
        const nextTab = next.tab ?? tab;
        const value = next.search ?? search;
        router.get(
            route(routeName),
            { ...extraQuery, ...(nextTab !== "all" ? { tab: nextTab } : {}), ...(value ? { search: value } : {}) },
            { preserveState: true, preserveScroll: true, replace: true },
        );
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

    return (
        <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div className="overflow-x-auto">
                <Tabs value={tab} onValueChange={(value) => visit({ tab: value })}>
                    <TabsList>
                        {tabs.map((item) => (
                            <TabsTrigger key={item.value} value={item.value}>
                                {item.label}
                                <span
                                    className={cn(
                                        "tabular-nums",
                                        item.value === highlight && counts[item.value] > 0 ? "font-semibold text-primary" : "text-muted-foreground",
                                    )}
                                >
                                    {counts[item.value] ?? 0}
                                </span>
                            </TabsTrigger>
                        ))}
                    </TabsList>
                </Tabs>
            </div>
            <div className="flex gap-2">
                {children}
                <div className="relative flex-1 xl:w-72 xl:flex-none">
                    <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder={placeholder} className="ps-8" />
                </div>
            </div>
        </div>
    );
}
