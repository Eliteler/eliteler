/*
 | Google reCAPTCHA — admin settings.
 | Built into ../../dist with `npm run plugin:build -- GoogleRecaptcha` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { ExternalLink, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Switch } from "@gobiz/admin";

type Props = {
    values: { recaptcha_enable: boolean; recaptcha_site_key: string };
    savedSecret: boolean;
};

export default function GoogleRecaptchaSettings({ values, savedSecret }: Props) {
    const { t } = useTranslation();
    const form = useForm({ ...values, recaptcha_secret_key: "" });
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ ...current, recaptcha_enable: current.recaptcha_enable ? 1 : 0 }));
        form.post(route("admin.google_recaptcha_settings.update"), { preserveScroll: true, onSuccess: () => setData("recaptcha_secret_key", "") });
    };

    return (
        <>
            <Head title="Google reCAPTCHA" />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title="Google reCAPTCHA"
                    description={t("Stop bots and spam sign-ups with an “I'm not a robot” check.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: "Google reCAPTCHA" },
                    ]}
                    actions={
                        <Button type="button" variant="secondary" asChild>
                            <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noreferrer">
                                <ExternalLink />
                                {t("Get keys")}
                            </a>
                        </Button>
                    }
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("reCAPTCHA keys")}</CardTitle>
                            <CardDescription>{t("Register your site in the reCAPTCHA admin console as reCAPTCHA v2 (“I'm not a robot” checkbox) and add your domain.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="recaptcha_enable">{t("Turn on reCAPTCHA")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("Visitors tick the box before signing in or signing up.")}</p>
                                </div>
                                <Switch id="recaptcha_enable" checked={data.recaptcha_enable} onCheckedChange={(checked) => setData("recaptcha_enable", checked)} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="recaptcha_site_key">{t("Site key")}</Label>
                                <Input
                                    id="recaptcha_site_key"
                                    value={data.recaptcha_site_key}
                                    onChange={(event) => setData("recaptcha_site_key", event.target.value)}
                                    className="font-mono text-sm"
                                    aria-invalid={!!errors.recaptcha_site_key}
                                />
                                <FieldError message={errors.recaptcha_site_key} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="recaptcha_secret_key">{t("Secret key")}</Label>
                                <Input
                                    id="recaptcha_secret_key"
                                    type="password"
                                    autoComplete="off"
                                    value={data.recaptcha_secret_key}
                                    onChange={(event) => setData("recaptcha_secret_key", event.target.value)}
                                    placeholder={savedSecret ? t("Saved. Leave blank to keep it.") : ""}
                                    className="font-mono text-sm"
                                    aria-invalid={!!errors.recaptcha_secret_key}
                                />
                                <FieldError message={errors.recaptcha_secret_key} />
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
