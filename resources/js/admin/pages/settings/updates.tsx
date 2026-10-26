import { Deferred, Head, usePage } from "@inertiajs/react";
import axios from "axios";
import {
    AlertTriangle,
    CheckCircle2,
    Crown,
    Download,
    ExternalLink,
    Eye,
    EyeOff,
    FileText,
    Info,
    KeyRound,
    Loader2,
    Mail,
    RefreshCw,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
} from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import { CopyButton } from "@admin/components/copy-button";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardAction,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Skeleton } from "@admin/components/ui/skeleton";
import { toast } from "@admin/lib/toast";

import { InstallUpdateDialog } from "./components/install-update-dialog";

type Result =
    | { ok: false; message: string }
    | {
          ok: true;
          message: string;
          latestVersion: string;
          updateAvailable: boolean;
          notes: string;
          license: string;
          supportExpired: boolean;
      };

type Props = {
    currentVersion: string;
    purchaseCode: string;
    email: string;
    result?: Result;
    links: {
        purchaseCodeHelp: string;
        featureRequest: string;
        renewSupport: string;
        product: string;
    };
};

export default function SoftwareUpdates({
    currentVersion,
    purchaseCode,
    email,
    result,
    links,
}: Props) {
    const { t } = useTranslation();
    const [address, setAddress] = useState(email);
    const [checked, setChecked] = useState<Result | undefined>(result);
    const [checking, setChecking] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [confirm, setConfirm] = useState(false);
    const [installing, setInstalling] = useState(false);

    useEffect(() => setChecked(result), [result]);

    const check = () => {
        setChecking(true);
        setErrors({});
        axios
            .post(route("admin.check.update"), { email: address })
            .then(({ data }) => setChecked(data))
            .catch((error) => {
                const fieldErrors = error.response?.data?.errors;
                if (fieldErrors)
                    setErrors(
                        Object.fromEntries(
                            Object.entries(fieldErrors).map(([key, value]) => [
                                key,
                                (value as string[])[0],
                            ]),
                        ),
                    );
                else
                    toast.error(
                        t("Couldn't check for updates. Please try again."),
                    );
            })
            .finally(() => setChecking(false));
    };

    // ?install=1 (the dashboard's "Install" link) opens the install dialog once the update check confirms a new version
    const { url } = usePage();
    const [autoOpen, setAutoOpen] = useState(() => new URLSearchParams(url.split("?")[1] ?? "").has("install"));
    useEffect(() => {
        if (autoOpen && checked?.ok && checked.updateAvailable) {
            setConfirm(true);
            setAutoOpen(false);
        }
    }, [autoOpen, checked]);

    return (
        <>
            <Head title={t("Software updates")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6 min-w-0 max-w-full overflow-hidden">
                <PageHeader
                    title={t("Software updates")}
                    description={t(
                        "Check your license and install new versions of GoBiz.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Software updates") },
                    ]}
                    actions={
                        <Button variant="outline" asChild>
                            <a
                                href={links.featureRequest}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Sparkles />
                                {t("Request a feature")}
                            </a>
                        </Button>
                    }
                />

                <div className="grid gap-6 lg:grid-cols-[1fr_22rem] min-w-0 max-w-full">
                    {/* Status & Changelog Section: Prevents flex overflow */}
                    <div className="order-2 flex flex-col gap-6 lg:order-1 min-w-0 max-w-full">
                        <Deferred data="result" fallback={<StatusSkeleton />}>
                            <Status
                                result={checked}
                                currentVersion={currentVersion}
                                checking={checking}
                                onCheck={check}
                                onInstall={() => setConfirm(true)}
                                renewUrl={links.renewSupport}
                            />
                        </Deferred>
                    </div>

                    {/* License Section */}
                    <div className="order-1 flex flex-col gap-6 lg:order-2 min-w-0 max-w-full">
                        <LicenseCard
                            licenseKey={purchaseCode}
                            result={checked}
                            email={address}
                            onEmailChange={setAddress}
                            emailError={errors.email}
                            checking={checking}
                            disabled={checking || installing}
                            onCheck={check}
                            helpUrl={links.purchaseCodeHelp}
                        />

                        {checked?.ok &&
                            checked.license === "Regular License" && (
                                <Card className="min-w-0 max-w-full">
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2 text-base">
                                            <Crown className="size-4.5 shrink-0 text-warning" />
                                            {t("Regular license")}
                                        </CardTitle>
                                        <CardDescription>
                                            {t(
                                                "Upgrade to the Extended license if you charge customers for subscriptions.",
                                            )}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardFooter>
                                        <Button
                                            variant="outline"
                                            className="w-full"
                                            asChild
                                        >
                                            <a
                                                href={links.product}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                <ExternalLink />
                                                {t("Upgrade license")}
                                            </a>
                                        </Button>
                                    </CardFooter>
                                </Card>
                            )}
                    </div>
                </div>
            </div>

            <InstallUpdateDialog
                open={confirm}
                onOpenChange={setConfirm}
                currentVersion={currentVersion}
                latestVersion={checked?.ok ? checked.latestVersion : ""}
                email={address}
                onInstallingChange={setInstalling}
            />
        </>
    );
}

// Masked by default so the key isn't exposed in screenshots or screen shares
const maskKey = (key: string) =>
    key.length > 8 ? `${key.slice(0, 4)}${"•".repeat(12)}${key.slice(-4)}` : "•".repeat(key.length);

function LicenseCard({
    licenseKey,
    result,
    email,
    onEmailChange,
    emailError,
    checking,
    disabled,
    onCheck,
    helpUrl,
}: {
    licenseKey: string;
    result?: Result;
    email: string;
    onEmailChange: (email: string) => void;
    emailError?: string;
    checking: boolean;
    disabled: boolean;
    onCheck: () => void;
    helpUrl: string;
}) {
    const { t } = useTranslation();
    const [revealed, setRevealed] = useState(false);
    const hasKey = licenseKey !== "" && licenseKey !== "YOUR_ENVATO_PURCHASE_CODE";

    return (
        <Card className="min-w-0 max-w-full">
            {/* Same header style as the version card: small inline icon, one left edge for everything */}
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">
                    <KeyRound className="size-4.5 shrink-0 text-primary" />
                    {t("License")}
                </CardTitle>
                <CardDescription>
                    {t("Used to verify your purchase and download updates.")}
                </CardDescription>
                {result && (
                    <CardAction>
                        {result.ok ? (
                            <Badge variant="outline" className="border-success/30 text-success">
                                <ShieldCheck />
                                {t("Verified")}
                            </Badge>
                        ) : (
                            <Badge variant="outline" className="border-warning/40 text-warning">
                                <ShieldAlert />
                                {t("Not verified")}
                            </Badge>
                        )}
                    </CardAction>
                )}
            </CardHeader>
            <CardContent className="flex flex-col gap-5">
                <div className="flex flex-col gap-2">
                    <div className="flex items-center justify-between gap-2">
                        <Label>{t("License key")}</Label>
                        <a
                            href={helpUrl}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                        >
                            {t("Find my license key")}
                            <ExternalLink className="size-3" />
                        </a>
                    </div>
                    {/* Always the PURCHASE_CODE from .env: the check, the dashboard notice and the install all use it */}
                    {hasKey ? (
                        <div className="flex h-9 items-center gap-0.5 rounded-md border bg-muted/50 ps-3 pe-1">
                            <span className="min-w-0 flex-1 truncate font-mono text-xs" title={revealed ? licenseKey : undefined}>
                                {revealed ? licenseKey : maskKey(licenseKey)}
                            </span>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                onClick={() => setRevealed((value) => !value)}
                                aria-label={revealed ? t("Hide") : t("Show")}
                            >
                                {revealed ? <EyeOff /> : <Eye />}
                            </Button>
                            <CopyButton value={licenseKey} />
                        </div>
                    ) : (
                        <div className="flex h-9 items-center gap-2 rounded-md border border-warning/40 bg-warning/10 px-3 text-sm">
                            <AlertTriangle className="size-4 shrink-0 text-warning" />
                            {t("No license key set.")}
                        </div>
                    )}
                    <p className="text-xs text-muted-foreground">
                        {t("Read from PURCHASE_CODE in your .env file. Edit .env to change it.")}
                    </p>
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="email">{t("Email for update notices")}</Label>
                    <div className="relative">
                        <Mail className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="email"
                            type="email"
                            value={email}
                            onChange={(event) => onEmailChange(event.target.value)}
                            placeholder="you@example.com"
                            className="ps-8"
                            aria-invalid={!!emailError}
                        />
                    </div>
                    <FieldError message={emailError} />
                </div>
            </CardContent>
            <CardFooter>
                <Button className="w-full" onClick={onCheck} disabled={disabled}>
                    {checking ? <Loader2 className="animate-spin" /> : <RefreshCw />}
                    {t("Check for updates")}
                </Button>
            </CardFooter>
        </Card>
    );
}

function Status({
    result,
    currentVersion,
    checking,
    onCheck,
    onInstall,
    renewUrl,
}: {
    result?: Result;
    currentVersion: string;
    checking: boolean;
    onCheck: () => void;
    onInstall: () => void;
    renewUrl: string;
}) {
    const { t } = useTranslation();

    if (!result || !result.ok) {
        return (
            <Card className="min-w-0 max-w-full">
                <CardContent className="flex flex-col items-center gap-3 py-10 text-center">
                    <AlertTriangle className="size-8 text-warning" />
                    <div className="flex flex-col gap-1">
                        <span className="font-medium">
                            {t("Couldn't check for updates")}
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {result?.message}
                        </span>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {t("You're on version :version.").replace(
                            ":version",
                            currentVersion,
                        )}
                    </p>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={onCheck}
                        disabled={checking}
                    >
                        {checking ? (
                            <Loader2 className="animate-spin" />
                        ) : (
                            <RefreshCw />
                        )}
                        {t("Try again")}
                    </Button>
                </CardContent>
            </Card>
        );
    }

    return (
        <>
            {result.supportExpired && (
                <div
                    role="alert"
                    className="flex flex-col gap-4 rounded-xl border border-warning/40 bg-warning/10 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5"
                >
                    <div className="flex min-w-0 flex-1 items-center gap-3.5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning">
                            <ShieldAlert className="size-5" />
                        </span>
                        <div className="flex min-w-0 flex-col gap-0.5">
                            <span className="text-sm font-semibold text-foreground">
                                {t("Your support has ended")}
                            </span>
                            <span className="text-sm text-muted-foreground">
                                {t(
                                    "Renew to keep getting updates, priority support and new features.",
                                )}
                            </span>
                        </div>
                    </div>
                    <Button asChild className="w-full shrink-0 sm:w-auto">
                        <a href={renewUrl} target="_blank" rel="noreferrer">
                            {t("Renew support")}
                            <ExternalLink />
                        </a>
                    </Button>
                </div>
            )}

            {/* Compact card with gap-2 and py-2 to eliminate excessive vertical spacing */}
            <Card className="gap-2 py-2 w-full max-w-full min-w-0 overflow-hidden">
                {/* Header: Responsive flex-col on mobile, flex-row on larger screens */}
                <CardHeader className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b bg-muted/20 p-3.5 sm:px-5 sm:py-3.5">
                    <div className="flex flex-col gap-0.5 min-w-0 max-w-full">
                        <CardTitle className="flex items-center gap-2 text-base">
                            {result.updateAvailable ? (
                                <>
                                    <Download className="size-4.5 shrink-0 text-primary" />
                                    <span className="truncate">
                                        {t(
                                            "Version :version is available",
                                        ).replace(
                                            ":version",
                                            result.latestVersion,
                                        )}
                                    </span>
                                </>
                            ) : (
                                <>
                                    <CheckCircle2 className="size-4.5 shrink-0 text-success" />
                                    <span>{t("You're up to date")}</span>
                                </>
                            )}
                        </CardTitle>
                        {/* break-all prevents unbroken license keys from stretching mobile width */}
                        <CardDescription className="text-xs break-all leading-normal text-muted-foreground">
                            {t("Installed: :version").replace(
                                ":version",
                                currentVersion,
                            )}
                            {result.license && ` · ${/\s/.test(result.license) ? result.license : maskKey(result.license)}`}
                        </CardDescription>
                    </div>
                    {result.updateAvailable && (
                        <Badge className="self-start sm:self-auto shrink-0 px-2 py-0.5 text-xs font-semibold">
                            {t("New update")}
                        </Badge>
                    )}
                </CardHeader>

                <CardContent className="flex flex-col gap-2.5 p-3 sm:p-5 min-w-0 max-w-full">
                    {result.updateAvailable && result.notes && (
                        <div className="flex flex-col gap-2 min-w-0 max-w-full">
                            <div className="flex items-center gap-2 text-sm font-semibold text-foreground mb-2">
                                <FileText className="size-4 text-primary" />
                                {t("Changelog & Release Notes")}
                            </div>
                            <ChangelogViewer rawNotes={result.notes} />
                        </div>
                    )}
                </CardContent>

                {result.updateAvailable && (
                    <CardFooter className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t bg-muted/10 p-3.5 sm:p-4">
                        <span className="text-xs text-muted-foreground flex items-center gap-1.5">
                            <Info className="size-3.5 shrink-0" />
                            {t("Please take a backup before updating.")}
                        </span>
                        <Button
                            onClick={onInstall}
                            size="default"
                            className="w-full sm:w-auto shadow-xs"
                        >
                            <Download className="size-4" />
                            {t("Install update now")}
                        </Button>
                    </CardFooter>
                )}
            </Card>
        </>
    );
}

// Dedicated SaaS Changelog Parser and Viewer without inner scrollbar
function ChangelogViewer({ rawNotes }: { rawNotes: string }) {
    const formattedHtml = useMemo(() => {
        if (!rawNotes) return "";
        let content = rawNotes;

        // Strip red/danger background box from the customization notice
        content = content.replace(
            /<div[^>]*class=["'][^"']*bg-danger[^"']*["'][^>]*>([\s\S]*?If you have customized the script[\s\S]*?)<\/div>/gi,
            '<div class="my-3 text-xs text-muted-foreground leading-relaxed">$1</div>',
        );

        // Make the customization text strong/bold without red box
        content = content.replace(
            /(If you have customized the script[^<]*)/gi,
            '<strong class="text-foreground">$1</strong>',
        );

        // Convert Bootstrap fw-bold class
        content = content.replace(
            /class=["']fw-bold["']/gi,
            'class="font-semibold text-foreground mr-1"',
        );

        return content;
    }, [rawNotes]);

    return (
        <div
            className="w-full max-w-full min-w-0 rounded-xl border border-border/70 bg-muted/20 p-3.5 sm:p-4 text-xs sm:text-sm text-foreground break-words [overflow-wrap:anywhere]
            [&_h3]:text-sm sm:[&_h3]:text-base [&_h3]:font-bold [&_h3]:text-foreground [&_h3]:mb-2.5 [&_h3]:pb-1.5 [&_h3]:border-b [&_h3]:border-border/50
            [&_ul]:space-y-1.5 [&_ul]:mt-2 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:ps-4 sm:[&_ul]:ps-5
            [&_li]:text-muted-foreground [&_li]:leading-relaxed
            [&_strong]:font-semibold [&_strong]:text-foreground
            [&_ul+p]:mt-4
            [&_p]:text-muted-foreground [&_p]:my-2 [&_p]:leading-relaxed
            [&_a]:text-primary [&_a]:font-medium [&_a]:underline-offset-4 hover:[&_a]:underline [&_a]:break-all"
            dangerouslySetInnerHTML={{ __html: formattedHtml }}
        />
    );
}

function StatusSkeleton() {
    const { t } = useTranslation();
    return (
        <Card className="min-w-0 max-w-full">
            <CardContent className="flex flex-col gap-3 py-6">
                <span className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Loader2 className="size-4 animate-spin" />
                    {t("Checking for updates…")}
                </span>
                <Skeleton className="h-5 w-56" />
                <Skeleton className="h-4 w-40" />
            </CardContent>
        </Card>
    );
}
