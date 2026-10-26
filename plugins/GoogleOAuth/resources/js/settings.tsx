/*
 | Sign in with Google — admin settings.
 | Built into ../../dist with `npm run plugin:build -- GoogleOAuth` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, CopyButton, FieldError, Input, Label, PageHeader, Switch } from "@gobiz/admin";

type Props = {
    values: { google_auth_enable: boolean; google_client_id: string };
    savedSecret: boolean;
    redirectUrl: string;
};

export default function GoogleOAuthSettings({ values, savedSecret, redirectUrl }: Props) {
    const { t } = useTranslation();
    const form = useForm({ ...values, google_client_secret: "" });
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ ...current, google_auth_enable: current.google_auth_enable ? 1 : 0 }));
        form.post(route("admin.google_oauth_settings.update"), { preserveScroll: true, onSuccess: () => setData("google_client_secret", "") });
    };

    return (
        <>
            <Head title={t("Sign in with Google")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Sign in with Google")}
                    description={t("Let customers sign up and sign in to your website with their Google account.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Sign in with Google") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Google Cloud OAuth client")}</CardTitle>
                            <CardDescription>
                                {t("In Google Cloud console, open APIs & Services → Credentials, create an OAuth client ID (Web application) and add the redirect URL below.")}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="google_auth_enable">{t("Turn on Sign in with Google")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("A “Continue with Google” button appears on the sign-in and sign-up pages.")}</p>
                                </div>
                                <Switch id="google_auth_enable" checked={data.google_auth_enable} onCheckedChange={(checked) => setData("google_auth_enable", checked)} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="google_client_id">{t("Client ID")}</Label>
                                <Input
                                    id="google_client_id"
                                    value={data.google_client_id}
                                    onChange={(event) => setData("google_client_id", event.target.value)}
                                    placeholder="1234567890-abc.apps.googleusercontent.com"
                                    className="font-mono text-sm"
                                    aria-invalid={!!errors.google_client_id}
                                />
                                <FieldError message={errors.google_client_id} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="google_client_secret">{t("Client secret")}</Label>
                                <Input
                                    id="google_client_secret"
                                    type="password"
                                    autoComplete="off"
                                    value={data.google_client_secret}
                                    onChange={(event) => setData("google_client_secret", event.target.value)}
                                    placeholder={savedSecret ? t("Saved. Leave blank to keep it.") : ""}
                                    className="font-mono text-sm"
                                    aria-invalid={!!errors.google_client_secret}
                                />
                                <FieldError message={errors.google_client_secret} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label>{t("Authorized redirect URL")}</Label>
                                <div className="flex items-center gap-2">
                                    <code className="min-w-0 flex-1 truncate rounded-md border bg-muted px-3 py-2 font-mono text-xs">{redirectUrl}</code>
                                    <CopyButton value={redirectUrl} />
                                </div>
                                <p className="text-xs text-muted-foreground">{t("Paste this into “Authorized redirect URIs” in your OAuth client.")}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button type="submit" disabled={form.processing || !form.isDirty}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save changes")}
                    </Button>
                </div>
            </form>
        </>
    );
}
