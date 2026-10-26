import { usePage } from "@inertiajs/react";
import axios from "axios";
import { CheckCircle2, DatabaseBackup, Download, FileWarning, Loader2, Timer } from "lucide-react";
import { useEffect, useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { clearCacheStorage } from "@admin/components/clear-cache-dialog";
import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { toast } from "@admin/lib/toast";

type Step = "confirm" | "installing" | "done";

/**
 * Install a new GoBiz version: what to know first, a backup confirmation,
 * then the install. Afterwards the browser cache is cleared (the server
 * clears its own caches during the update) and the page reloads.
 */
export function InstallUpdateDialog({
    open,
    onOpenChange,
    currentVersion,
    latestVersion,
    email,
    onInstallingChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    currentVersion: string;
    latestVersion: string;
    email: string;
    onInstallingChange?: (installing: boolean) => void;
}) {
    const { t } = useTranslation();
    const { permissions } = usePage().props;
    const [step, setStep] = useState<Step>("confirm");
    const [backedUp, setBackedUp] = useState(false);
    const busy = step !== "confirm";

    useEffect(() => {
        if (open) {
            setStep("confirm");
            setBackedUp(false);
        }
    }, [open]);

    useEffect(() => onInstallingChange?.(step === "installing"), [step, onInstallingChange]);

    // Leaving mid-install can leave GoBiz half updated
    useEffect(() => {
        if (step !== "installing") return;
        const warn = (event: BeforeUnloadEvent) => event.preventDefault();
        window.addEventListener("beforeunload", warn);
        return () => window.removeEventListener("beforeunload", warn);
    }, [step]);

    const install = () => {
        setStep("installing");
        axios
            .post(route("admin.update.code"), { app_version: latestVersion, email })
            .then(async ({ data }) => {
                if (!data.success) {
                    toast.error(data.message || t("The update couldn't be installed."));
                    setStep("confirm");
                    return;
                }
                setStep("done");
                await clearCacheStorage();
                window.setTimeout(() => window.location.replace(route("admin.check")), 1500);
            })
            .catch(() => {
                toast.error(t("The update couldn't be installed. Check your server's error log."));
                setStep("confirm");
            });
    };

    return (
        <Dialog open={open} onOpenChange={(value) => !busy && onOpenChange(value)}>
            <DialogContent className="sm:max-w-lg" showCloseButton={!busy} onInteractOutside={(event) => busy && event.preventDefault()}>
                {step === "done" ? (
                    <div className="flex flex-col items-center gap-3 py-6 text-center">
                        <CheckCircle2 className="size-10 text-success" />
                        <DialogTitle>{t("GoBiz :version is installed").replace(":version", latestVersion)}</DialogTitle>
                        <DialogDescription>{t("Clearing the cache and reloading so you see the new version…")}</DialogDescription>
                        <Loader2 className="size-5 animate-spin text-muted-foreground" />
                    </div>
                ) : (
                    <>
                        <DialogHeader>
                            <DialogTitle>{t("Install GoBiz :version").replace(":version", latestVersion)}</DialogTitle>
                            <DialogDescription>{t("You're on version :version. Please read this before you update.").replace(":version", currentVersion)}</DialogDescription>
                        </DialogHeader>

                        <ul className="flex flex-col gap-3">
                            <Point icon={<DatabaseBackup />} title={t("Back up first")}>
                                {t("Take a backup of your files and database. If anything goes wrong, a backup is the only way to get back to how things were.")}
                                {permissions.backup && (
                                    <>
                                        {" "}
                                        <a href={route("admin.backups")} target="_blank" rel="noreferrer" className="font-medium text-primary hover:underline">
                                            {t("Open System Backups")}
                                        </a>
                                    </>
                                )}
                            </Point>
                            <Point icon={<FileWarning />} title={t("Custom code changes will be replaced")} warning>
                                {t("The update replaces GoBiz's files with the new version. Any changes made directly to those files, such as edited code, built-in themes or templates, will be lost. Your data, uploaded files and settings aren't changed.")}
                            </Point>
                            <Point icon={<Timer />} title={t("Keep this page open")}>
                                {t("Installing can take a few minutes. Don't close or reload the page until it finishes. Visitors may briefly see errors while files are replaced.")}
                            </Point>
                        </ul>

                        <label className="flex cursor-pointer items-start gap-2.5 rounded-lg border p-3 text-sm has-[:disabled]:cursor-default">
                            <input
                                type="checkbox"
                                checked={backedUp}
                                onChange={(event) => setBackedUp(event.target.checked)}
                                disabled={busy}
                                className="mt-0.5 size-4 accent-[var(--primary)]"
                            />
                            <span>{t("I've backed up my files and database, and I understand that custom code changes will be replaced.")}</span>
                        </label>

                        {step === "installing" && (
                            <p className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Loader2 className="size-4 animate-spin" />
                                {t("Downloading and installing… Please keep this page open.")}
                            </p>
                        )}

                        <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <Button variant="outline" onClick={() => onOpenChange(false)} disabled={busy}>
                                {t("Cancel")}
                            </Button>
                            <Button onClick={install} disabled={!backedUp || busy}>
                                {step === "installing" ? <Loader2 className="animate-spin" /> : <Download />}
                                {t("Install update")}
                            </Button>
                        </div>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

function Point({ icon, title, warning = false, children }: { icon: ReactNode; title: string; warning?: boolean; children: ReactNode }) {
    return (
        <li className="flex gap-3">
            <span className={warning ? "mt-0.5 text-warning [&_svg]:size-5" : "mt-0.5 text-muted-foreground [&_svg]:size-5"}>{icon}</span>
            <div className="flex flex-col gap-0.5">
                <span className="text-sm font-medium">{title}</span>
                <p className="text-sm text-muted-foreground">{children}</p>
            </div>
        </li>
    );
}
