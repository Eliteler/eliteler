import { usePage } from "@inertiajs/react";
import axios from "axios";
import { CheckCircle2, DatabaseBackup, Download, FileWarning, Loader2, Timer } from "lucide-react";
import { useEffect, useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { clearCacheStorage } from "@admin/components/clear-cache-dialog";
import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Progress } from "@admin/components/ui/progress";
import { toast } from "@admin/lib/toast";

type Step = "confirm" | "installing" | "done";

interface UpdateStepResponse {
    success: boolean;
    message?: string;
    step?: string;
    done?: boolean;
    total_files?: number;
    batch_size?: number;
    offset?: number;
    extracted?: number;
    deployed?: number;
    percent?: number;
}

/**
 * Install a new GoBiz version: what to know first, a backup confirmation,
 * then phased adaptive AJAX install (download -> batched extract -> migrate -> batched deploy -> finalize)
 * to completely eliminate timeouts and memory exhaustion on 1 Core, 2 GB VPS and shared hosting.
 * Seamlessly handles any package size from 2 MB minor patches to 200 MB+ major releases.
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
    const [currentStage, setCurrentStage] = useState(1);
    const [statusText, setStatusText] = useState("");
    const [progressValue, setProgressValue] = useState(0);
    const [failureMessage, setFailureMessage] = useState("");
    const busy = step !== "confirm";

    useEffect(() => {
        if (open) {
            setStep("confirm");
            setBackedUp(false);
            setCurrentStage(1);
            setStatusText("");
            setProgressValue(0);
            setFailureMessage("");
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

    /**
     * Post to update endpoint with automatic transient network retry (up to 3 attempts).
     */
    const postWithRetry = async (payload: Record<string, unknown>, maxAttempts = 3): Promise<UpdateStepResponse> => {
        let lastError: unknown;
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                const res = await axios.post<UpdateStepResponse>(route("admin.update.code"), payload, { timeout: 120000 });
                if (res.data?.success) {
                    return res.data;
                }
                throw new Error(res.data?.message || t("The update couldn't be installed."));
            } catch (err) {
                lastError = err;
                if (attempt < maxAttempts) {
                    await new Promise((resolve) => setTimeout(resolve, 1500));
                }
            }
        }
        throw lastError;
    };

    const install = async () => {
        setStep("installing");
        setFailureMessage("");

        try {
            // Stage 1: Download package directly to disk (0% -> 25%)
            setCurrentStage(1);
            setStatusText(t("Downloading update package (Step 1/4)…"));
            setProgressValue(10);
            await postWithRetry({
                app_version: latestVersion,
                email,
                step: "download",
            });
            setProgressValue(25);

            // Stage 2: Dynamic Batched Extraction (25% -> 50%)
            setCurrentStage(2);
            setStatusText(t("Preparing files for extraction (Step 2/4)…"));
            const initExtract = await postWithRetry({
                app_version: latestVersion,
                email,
                step: "extract-init",
            });

            if (!initExtract.done) {
                const total = initExtract.total_files || 1;
                const batchSize = initExtract.batch_size || 1000;
                let offset = 0;

                while (true) {
                    const batchRes = await postWithRetry({
                        app_version: latestVersion,
                        email,
                        step: "extract-batch",
                        offset,
                        batch_size: batchSize,
                    });

                    offset = batchRes.offset ?? offset + batchSize;
                    const extracted = batchRes.extracted ?? offset;
                    const extractPct = Math.min(25 + Math.round((extracted / total) * 25), 49);
                    setProgressValue(extractPct);

                    setStatusText(
                        t("Unpacking files: :current / :total (:percent%)")
                            .replace(":current", extracted.toLocaleString())
                            .replace(":total", total.toLocaleString())
                            .replace(":percent", String(batchRes.percent ?? Math.round((extracted / total) * 100)))
                    );

                    if (batchRes.done) break;
                }
            }
            setProgressValue(50);

            // Stage 3: Database & Settings Migration (50% -> 70%)
            setCurrentStage(3);
            setStatusText(t("Upgrading database & settings (Step 3/4)…"));
            setProgressValue(60);
            await postWithRetry({
                app_version: latestVersion,
                email,
                step: "migrate",
            });
            setProgressValue(70);

            // Stage 4: Dynamic Batched File Deployment (70% -> 98%)
            setCurrentStage(4);
            setStatusText(t("Preparing file deployment (Step 4/4)…"));
            const initDeploy = await postWithRetry({
                app_version: latestVersion,
                email,
                step: "deploy-init",
            });

            if (!initDeploy.done) {
                const total = initDeploy.total_files || 1;
                const batchSize = initDeploy.batch_size || 1500;
                let offset = 0;

                while (true) {
                    const batchRes = await postWithRetry({
                        app_version: latestVersion,
                        email,
                        step: "deploy-batch",
                        offset,
                        batch_size: batchSize,
                    });

                    offset = batchRes.offset ?? offset + batchSize;
                    const deployed = batchRes.deployed ?? offset;
                    const deployPct = Math.min(70 + Math.round((deployed / total) * 25), 95);
                    setProgressValue(deployPct);

                    setStatusText(
                        t("Deploying files: :current / :total (:percent%)")
                            .replace(":current", deployed.toLocaleString())
                            .replace(":total", total.toLocaleString())
                            .replace(":percent", String(batchRes.percent ?? Math.round((deployed / total) * 100)))
                    );

                    if (batchRes.done) break;
                }

                // Finalize deployment (clear caches, delete staging)
                setStatusText(t("Finalizing update and clearing cache…"));
                await postWithRetry({
                    app_version: latestVersion,
                    email,
                    step: "deploy-finalize",
                });
            }

            setProgressValue(100);
            setStep("done");
            await clearCacheStorage();
            window.setTimeout(() => window.location.replace(route("admin.check")), 1500);
        } catch (err: unknown) {
            let errorMsg = t("The update couldn't be installed. Check your server's error log.");
            if (axios.isAxiosError(err) && err.response?.data?.message) {
                errorMsg = err.response.data.message;
            } else if (err instanceof Error && err.message) {
                errorMsg = err.message;
            }
            toast.error(errorMsg);
            setFailureMessage(errorMsg);
            setStep("confirm");
        }
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

                        {failureMessage && (
                            <div className="rounded-lg border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive">
                                <p className="font-semibold">{t("Installation interrupted:")}</p>
                                <p className="mt-0.5">{failureMessage}</p>
                            </div>
                        )}

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
                            <div className="flex flex-col gap-2.5 rounded-lg border bg-muted/40 p-3.5">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="flex items-center gap-2 font-medium">
                                        <Loader2 className="size-4 animate-spin text-primary" />
                                        {statusText}
                                    </span>
                                    <span className="text-xs font-semibold text-muted-foreground">{progressValue}%</span>
                                </div>
                                <Progress value={progressValue} className="h-2" />
                                <p className="text-xs text-muted-foreground">
                                    {t("Processing step :current of 4. Please keep this browser window open.").replace(":current", String(currentStage))}
                                </p>
                            </div>
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
