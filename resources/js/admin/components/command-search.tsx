import { router, usePage } from "@inertiajs/react";
import axios from "axios";
import { CornerDownLeft, SearchIcon } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import { openClearCache } from "@admin/components/clear-cache-dialog";
import { NavIcon } from "@admin/components/nav-icon";
import { Button } from "@admin/components/ui/button";
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@admin/components/ui/command";
import type { NavAction, NavLink } from "@admin/types";

type SearchResult = { label: string; url: string };

/**
 * ⌘K / Ctrl+K palette: sidebar pages plus the admin page search endpoint
 * (admin.page.search), which replaces the old Blade spotlight.
 */
export function CommandSearch() {
    const { t } = useTranslation();
    const { navigation } = usePage().props;
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState("");
    const [results, setResults] = useState<SearchResult[]>([]);

    const pages = useMemo(() => {
        const links: (NavLink & { section: string })[] = [];
        for (const section of navigation) {
            for (const entry of section.items) {
                if ("items" in entry) {
                    entry.items.forEach((item) => links.push({ ...item, icon: entry.icon, section: entry.title }));
                } else {
                    links.push({ ...entry, section: section.title });
                }
            }
        }
        return links;
    }, [navigation]);

    useEffect(() => {
        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === "k" && (event.metaKey || event.ctrlKey)) {
                event.preventDefault();
                setOpen((value) => !value);
            }
        };
        document.addEventListener("keydown", onKeyDown);
        return () => document.removeEventListener("keydown", onKeyDown);
    }, []);

    useEffect(() => {
        if (!open || query.trim().length < 2) {
            setResults([]);
            return;
        }

        const controller = new AbortController();
        const timer = window.setTimeout(() => {
            axios
                .get<SearchResult[]>(route("admin.page.search"), { params: { q: query }, signal: controller.signal })
                .then(({ data }) => setResults(data.slice(0, 8)))
                .catch(() => undefined);
        }, 250);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [open, query]);

    const go = (href: string, inertia: boolean, action: NavAction | null = null) => {
        setOpen(false);
        if (action) {
            openClearCache();
        } else if (inertia) {
            router.visit(href);
        } else {
            window.location.href = href;
        }
    };

    const knownUrls = new Set(pages.map((page) => page.href));

    return (
        <>
            <Button
                variant="outline"
                className="relative size-9 justify-center gap-2 rounded-md bg-muted/40 p-0 text-sm font-normal text-muted-foreground shadow-none sm:w-56 sm:justify-start sm:px-3 lg:w-72"
                onClick={() => setOpen(true)}
                aria-label={t("Search pages...")}
            >
                <SearchIcon className="size-4" />
                {/* Icon only on phones; the full search box from sm up */}
                <span className="hidden truncate sm:inline">{t("Search pages...")}</span>
                <kbd className="pointer-events-none ms-auto hidden h-5 items-center gap-1 rounded border bg-background px-1.5 font-mono text-[10px] font-medium select-none sm:flex">
                    <span className="text-xs">⌘</span>K
                </kbd>
            </Button>
            <CommandDialog
                open={open}
                onOpenChange={setOpen}
                title={t("Search")}
                description={t("Search pages...")}
            >
                <CommandInput placeholder={t("Search pages...")} value={query} onValueChange={setQuery} />
                <CommandList>
                    <CommandEmpty>{t("No results found.")}</CommandEmpty>
                    <CommandGroup heading={t("Pages")}>
                        {pages.map((page) => (
                            <CommandItem
                                key={page.href}
                                value={`${page.section} ${page.title}`}
                                onSelect={() => go(page.href, page.inertia, page.action)}
                            >
                                <NavIcon name={page.icon} />
                                <span>{page.title}</span>
                                <span className="ms-auto text-xs text-muted-foreground">{page.section}</span>
                            </CommandItem>
                        ))}
                    </CommandGroup>
                    {results.filter((result) => !knownUrls.has(result.url)).length > 0 && (
                        <CommandGroup heading={t("More")}>
                            {results
                                .filter((result) => !knownUrls.has(result.url))
                                .map((result) => (
                                    <CommandItem
                                        key={result.url}
                                        value={`${result.label} ${result.url}`}
                                        onSelect={() => go(result.url, false)}
                                    >
                                        <CornerDownLeft />
                                        <span>{result.label}</span>
                                    </CommandItem>
                                ))}
                        </CommandGroup>
                    )}
                </CommandList>
            </CommandDialog>
        </>
    );
}
