import { router, usePage } from "@inertiajs/react";
import { ArrowLeftToLine, Loader2, UserRoundCog } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Impersonation = { name: string; admin: string; leaveUrl: string } | null;

/**
 * Shown while a super admin is signed in as this staff member (App\Support\Impersonation).
 * Floats at the bottom of the screen so it's visible on every page without moving the layout;
 * only the pill takes clicks, the empty strip around it passes them through to the page.
 */
export function ImpersonationBanner() {
    const { t } = useTranslation();
    const impersonation = (usePage().props as { impersonation?: Impersonation }).impersonation;
    const [leaving, setLeaving] = useState(false);

    if (!impersonation) return null;

    return (
        <div role="status" className="pointer-events-none fixed inset-x-3 bottom-3 z-50 flex justify-center print:hidden">
            <div className="pointer-events-auto flex max-w-full items-center gap-3 rounded-full border border-amber-300 bg-amber-50 py-1.5 ps-3 pe-1.5 text-sm text-amber-950 shadow-lg dark:border-amber-700 dark:bg-amber-950 dark:text-amber-50">
                <UserRoundCog className="size-4 shrink-0" aria-hidden="true" />
                <span className="min-w-0 truncate">
                    {t("Signed in as")} <strong className="font-semibold">{impersonation.name}</strong>
                </span>
                <button
                    type="button"
                    disabled={leaving}
                    onClick={() => router.post(impersonation.leaveUrl, {}, { onStart: () => setLeaving(true), onFinish: () => setLeaving(false) })}
                    className="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full bg-amber-900 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-800 disabled:opacity-60 dark:bg-amber-200 dark:text-amber-950 dark:hover:bg-amber-100"
                >
                    {leaving ? <Loader2 className="size-3.5 animate-spin" aria-hidden="true" /> : <ArrowLeftToLine className="size-3.5" aria-hidden="true" />}
                    {t("Back to admin")}
                </button>
            </div>
        </div>
    );
}
