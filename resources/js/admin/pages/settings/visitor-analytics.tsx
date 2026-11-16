import { Head, router, useForm } from "@inertiajs/react";
import { Database, Download, ExternalLink, Loader2, MapPinned } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";

import { MusicTracks, type MusicTrack } from "./components/music-tracks";

type Props = {
    accountId: string;
    hasLicenseKey: boolean;
    lastUpdated: string | null;
    database: { available: boolean; size: number | null; build_date: string | null };
    unresolved: number;
    tracks: MusicTrack[];
    maxUploadMb: number;
};

const megabytes = (bytes: number) => `${(bytes / 1024 / 1024).toFixed(1)} MB`;

export default function VisitorAnalytics({ accountId, hasLicenseKey, lastUpdated, database, unresolved, tracks, maxUploadMb }: Props) {
    const { t } = useTranslation();
    const form = useForm({ account_id: accountId, license_key: "" });
    const [busy, setBusy] = useState<"download" | "backfill" | null>(null);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.visitor.analytics.update"), { preserveScroll: true, onSuccess: () => form.setData("license_key", "") });
    };

    const action = (kind: "download" | "backfill") =>
        router.post(route(kind === "download" ? "admin.visitor.analytics.download" : "admin.visitor.analytics.backfill"), {}, {
            preserveScroll: true,
            onStart: () => setBusy(kind),
            onFinish: () => setBusy(null),
        });

    const configured = accountId !== "" && hasLicenseKey;

    return (
        <>
            <Head title={t("Visitor Analytics")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Visitor Analytics")}
                    description={t("Where card visitors come from. Locations are looked up on your server with MaxMind's free GeoLite2 database.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Visitor Analytics") }]}
                />

                <div className="grid max-w-4xl gap-6">
                    <Card>
                        <form onSubmit={submit} className="flex flex-col gap-6">
                            <CardHeader>
                                <CardTitle>{t("MaxMind account")}</CardTitle>
                                <CardDescription>
                                    {t("Create a free GeoLite2 account at maxmind.com, then generate a license key under Manage License Keys.")}{" "}
                                    <a
                                        href="https://www.maxmind.com/en/geolite2/signup"
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1 font-medium text-primary underline-offset-4 hover:underline"
                                    >
                                        {t("Sign up")}
                                        <ExternalLink className="size-3.5" />
                                    </a>
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-5 sm:grid-cols-2">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="account_id">{t("Account ID")}</Label>
                                    <Input
                                        id="account_id"
                                        inputMode="numeric"
                                        value={form.data.account_id}
                                        onChange={(event) => form.setData("account_id", event.target.value)}
                                        placeholder="123456"
                                    />
                                    <FieldError message={form.errors.account_id} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="license_key">{t("License key")}</Label>
                                    <Input
                                        id="license_key"
                                        type="password"
                                        autoComplete="off"
                                        value={form.data.license_key}
                                        onChange={(event) => form.setData("license_key", event.target.value)}
                                        placeholder={hasLicenseKey ? t("Saved. Leave blank to keep it.") : ""}
                                    />
                                    <FieldError message={form.errors.license_key} />
                                </div>
                            </CardContent>
                            <CardFooter className="justify-end border-t pt-6">
                                <Button type="submit" disabled={form.processing || !form.isDirty}>
                                    {form.processing && <Loader2 className="animate-spin" />}
                                    {t("Save changes")}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Database className="size-4" />
                                {t("GeoLite2 database")}
                                {database.available ? <Badge variant="secondary">{t("Installed")}</Badge> : <Badge variant="outline">{t("Not installed")}</Badge>}
                            </CardTitle>
                            <CardDescription>
                                {t("Visits are only given a country and city while the database is installed. It refreshes every week through the scheduler.")}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl className="grid gap-4 text-sm sm:grid-cols-3">
                                <div>
                                    <dt className="text-muted-foreground">{t("Database date")}</dt>
                                    <dd className="font-medium">{database.build_date ?? "—"}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t("Last downloaded")}</dt>
                                    <dd className="font-medium">{lastUpdated ?? "—"}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t("Size")}</dt>
                                    <dd className="font-medium">{database.size ? megabytes(database.size) : "—"}</dd>
                                </div>
                            </dl>
                        </CardContent>
                        <CardFooter className="flex-wrap justify-end gap-2 border-t pt-6">
                            {!configured && <p className="me-auto text-sm text-muted-foreground">{t("Save your account ID and license key to download.")}</p>}
                            <Button type="button" onClick={() => action("download")} disabled={!configured || busy !== null}>
                                {busy === "download" ? <Loader2 className="animate-spin" /> : <Download />}
                                {database.available ? t("Update now") : t("Download now")}
                            </Button>
                        </CardFooter>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPinned className="size-4" />
                                {t("Visits without a location")}
                            </CardTitle>
                            <CardDescription>
                                {t("Visits recorded before the database was installed show as Unknown. Look them up from their saved IP address. Private and local addresses stay Unknown.")}
                            </CardDescription>
                        </CardHeader>
                        <CardFooter className="flex-wrap justify-between gap-2">
                            <p className="text-sm">
                                {t(":count visits without a location").replace(":count", unresolved.toLocaleString())}
                            </p>
                            <Button type="button" variant="outline" onClick={() => action("backfill")} disabled={!database.available || unresolved === 0 || busy !== null}>
                                {busy === "backfill" && <Loader2 className="animate-spin" />}
                                {t("Fill in locations")}
                            </Button>
                        </CardFooter>
                    </Card>

                    <MusicTracks tracks={tracks} maxUploadMb={maxUploadMb} />
                </div>
            </div>
        </>
    );
}
