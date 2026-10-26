import { router } from "@inertiajs/react";
import { ChevronRight, Globe, Loader2, Server } from "lucide-react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@admin/components/ui/collapsible";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";

type Target = "browser" | "server" | "all";

// Opened from the sidebar and the ⌘K search ("Clear App Cache"); mounted once in the admin layout
const listeners = new Set<() => void>();

export function openClearCache() {
    listeners.forEach((listener) => listener());
}

// Service worker caches; the HTTP cache is dropped by the Clear-Site-Data header the server sends back
export async function clearCacheStorage() {
    if (!("caches" in window)) return;
    try {
        const keys = await caches.keys();
        await Promise.all(keys.map((key) => caches.delete(key)));
    } catch {
        // Not available here (e.g. not a secure context): the header and a hard refresh still work
    }
}

export function ClearCacheDialog() {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [sessions, setSessions] = useState(false);
    const [running, setRunning] = useState<Target | null>(null);

    useEffect(() => {
        const listener = () => {
            setSessions(false);
            setOpen(true);
        };
        listeners.add(listener);
        return () => {
            listeners.delete(listener);
        };
    }, []);

    const clear = async (target: Target) => {
        const browser = target !== "server";
        setRunning(target);
        if (browser) await clearCacheStorage();

        router.post(
            route("admin.clear.cache"),
            { browser, server: target !== "browser", sessions },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setOpen(false);
                    // Load the page again so the browser fetches fresh files
                    if (browser) window.setTimeout(() => window.location.reload(), 1500);
                },
                onFinish: () => setRunning(null),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={(value) => running === null && setOpen(value)}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>{t("Clear cache")}</DialogTitle>
                    <DialogDescription>
                        {t("Cached copies make the app faster, but they can show old content after an update or a settings change. Clearing them loads fresh copies.")}
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-3">
                    <div className="flex gap-3 rounded-lg border p-3">
                        <Server className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                        <div className="flex flex-col gap-1">
                            <span className="text-sm font-medium">{t("Server cache")}</span>
                            <p className="text-sm text-muted-foreground">
                                {t("Settings, routes, translations and pages saved on the server. Clear it after an update, or when a change you made doesn't show up for anyone.")}
                            </p>
                        </div>
                    </div>

                    <div className="flex gap-3 rounded-lg border p-3">
                        <Globe className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                        <div className="flex min-w-0 flex-col gap-1">
                            <span className="text-sm font-medium">{t("Browser cache")}</span>
                            <p className="text-sm text-muted-foreground">
                                {t("Scripts, styles and images this browser saved from the site. Clear it when pages look broken or outdated only on this device. It doesn't affect other people.")}
                            </p>
                            <Collapsible>
                                <CollapsibleTrigger className="group flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                    <ChevronRight className="size-4 transition-transform group-data-[state=open]:rotate-90 rtl:rotate-180" />
                                    {t("Still outdated? Clear it in your browser")}
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <ul className="mt-2 flex list-disc flex-col gap-1.5 ps-5 text-sm text-muted-foreground">
                                        <li>
                                            {t("Hard refresh the page:")} <kbd className="rounded border bg-muted px-1 font-mono text-xs">Ctrl + Shift + R</kbd> {t("on Windows and Linux,")}{" "}
                                            <kbd className="rounded border bg-muted px-1 font-mono text-xs">⌘ + Shift + R</kbd> {t("on Mac.")}
                                        </li>
                                        <li>{t("Chrome and Edge: Settings → Privacy → Delete browsing data → “Cached images and files”.")}</li>
                                        <li>{t("Firefox: Settings → Privacy & Security → Cookies and Site Data → Clear Data → “Cached Web Content”.")}</li>
                                        <li>{t("Safari: Settings → Advanced → turn on “Show features for web developers”, then Develop → Empty Caches.")}</li>
                                    </ul>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </div>

                    <div className="flex items-start justify-between gap-4 rounded-lg border p-3">
                        <div className="flex flex-col gap-1">
                            <Label htmlFor="clear-sessions">{t("Also sign everyone out")}</Label>
                            <p className="text-sm text-muted-foreground">
                                {t("Clears all login sessions. Every admin, staff member and customer has to sign in again, and you'll be signed out too.")}
                            </p>
                        </div>
                        <Switch id="clear-sessions" checked={sessions} onCheckedChange={setSessions} disabled={running !== null} />
                    </div>
                </div>

                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button variant="outline" onClick={() => clear("browser")} disabled={running !== null}>
                        {running === "browser" ? <Loader2 className="animate-spin" /> : <Globe />}
                        {t("Clear browser cache")}
                    </Button>
                    <Button variant="outline" onClick={() => clear("server")} disabled={running !== null}>
                        {running === "server" ? <Loader2 className="animate-spin" /> : <Server />}
                        {t("Clear server cache")}
                    </Button>
                    <Button variant="destructive" onClick={() => clear("all")} disabled={running !== null}>
                        {running === "all" && <Loader2 className="animate-spin" />}
                        {t("Clear all")}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
