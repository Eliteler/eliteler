import { Link } from "@inertiajs/react";
import { Info, LifeBuoy, X } from "lucide-react";
import { useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Button } from "@admin/components/ui/button";

import type { SystemNotice } from "../types";

const RENEW_SUPPORT_URL = "https://gobizsaas.com/renew-support";

/** Software update / support plan notices from the license server (DashboardController systemNotice). */
export function SystemNotices({ notice }: { notice: SystemNotice | null }) {
    const { t } = useTranslation();
    const [dismissed, setDismissed] = useState<string[]>([]);

    if (!notice) return null;

    const notices: { key: string; icon: typeof Info; variant: "info" | "warning"; content: ReactNode }[] = [];

    if (notice.updateAvailable) {
        notices.push({
            key: "update",
            icon: Info,
            variant: "info",
            content: (
                <>
                    {notice.latestVersion ? t("Version :version is available.").replace(":version", notice.latestVersion) : t("A new version is available.")}{" "}
                    <Link href={route("admin.check", { install: 1 })} className="font-medium underline underline-offset-4">
                        {t("Install")}
                    </Link>
                </>
            ),
        });
    }

    if (notice.supportExpired) {
        notices.push({
            key: "support",
            icon: LifeBuoy,
            variant: "warning",
            content: (
                <>
                    {t("Your support plan has ended.")}{" "}
                    <a href={RENEW_SUPPORT_URL} target="_blank" rel="noreferrer" className="font-medium underline underline-offset-4">
                        {t("Renew")}
                    </a>
                </>
            ),
        });
    }

    const visible = notices.filter((item) => !dismissed.includes(item.key));
    if (!visible.length) return null;

    return (
        <div className="flex flex-col gap-2">
            {visible.map(({ key, icon: Icon, variant, content }) => (
                // One-line notices: a centered row instead of the Alert's top-aligned grid
                <Alert key={key} variant={variant} className="flex items-center gap-3 pe-12 [&>svg]:translate-y-0">
                    <Icon className="shrink-0" />
                    <AlertDescription className="min-w-0 flex-1 text-foreground">
                        <p>{content}</p>
                    </AlertDescription>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        className="absolute end-1.5 top-1/2 size-7 -translate-y-1/2"
                        onClick={() => setDismissed((keys) => [...keys, key])}
                        aria-label={t("Dismiss")}
                    >
                        <X />
                    </Button>
                </Alert>
            ))}
        </div>
    );
}
