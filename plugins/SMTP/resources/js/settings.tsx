/*
 | Email (SMTP) — admin settings.
 | Built into ../../dist with `npm run plugin:build -- SMTP` and shipped in the zip.
 */
import { Head, router, useForm } from "@inertiajs/react";
import { Loader2, MailCheck } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import {
    Button,
    Card,
    CardContent, 
    CardDescription,
    CardHeader,
    CardTitle,
    FieldError,
    Input,
    Label,
    PageHeader,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Switch,
} from "@gobiz/admin";

type Option = { value: string; label: string };

type Values = {
    mail_sender: string;
    mail_address: string;
    mail_driver: string;
    mail_host: string;
    mail_port: string;
    mail_encryption: string;
    mail_username: string;
    require_verification: boolean;
};

type Props = { values: Values; savedPassword: boolean; drivers: Option[]; encryptions: Option[]; adminEmail: string };

export default function SmtpSettings({ values, savedPassword, drivers, encryptions, adminEmail }: Props) {
    const { t } = useTranslation();
    const form = useForm({ ...values, mail_password: "" });
    const { data, setData, errors } = form;
    const [sending, setSending] = useState(false);
    const smtp = data.mail_driver === "smtp";

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ ...current, require_verification: current.require_verification ? 1 : 0 }));
        form.post(route("admin.smtp_settings.update"), { preserveScroll: true, onSuccess: () => setData("mail_password", "") });
    };

    const sendTest = () =>
        router.post(route("admin.plugin.test.email"), {}, { preserveScroll: true, preserveState: true, onStart: () => setSending(true), onFinish: () => setSending(false) });

    const text = (key: Exclude<keyof Values, "require_verification">, label: string, props: React.ComponentProps<typeof Input> = {}) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Input id={key} value={data[key]} onChange={(event) => setData(key, event.target.value)} aria-invalid={!!errors[key]} {...props} />
            <FieldError message={errors[key]} />
        </div>
    );

    const select = (key: "mail_driver" | "mail_encryption", label: string, options: Option[]) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Select value={data[key]} onValueChange={(value) => setData(key, value)}>
                <SelectTrigger id={key} className="w-full" aria-invalid={!!errors[key]}>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <FieldError message={errors[key]} />
        </div>
    );

    return (
        <>
            <Head title={t("Email (SMTP)")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Email (SMTP)")}
                    description={t("How your website sends emails: invoices, password resets, notifications and more.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Email (SMTP)") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Sender")}</CardTitle>
                            <CardDescription>{t("Who emails appear to come from.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            {text("mail_sender", t("Sender name"), { required: true })}
                            {text("mail_address", t("Sender email"), { type: "email", placeholder: "no-reply@yourdomain.com", required: true })}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Mail server")}</CardTitle>
                            <CardDescription>{t("Your email provider gives you these details (for example Gmail, Zoho, Mailgun or your hosting).")}</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            {select("mail_driver", t("Mailer"), drivers)}
                            {smtp && select("mail_encryption", t("Encryption"), encryptions)}
                            {smtp && text("mail_host", t("Host"), { placeholder: "smtp.gmail.com", className: "font-mono text-sm" })}
                            {smtp && text("mail_port", t("Port"), { type: "number", min: 1, max: 65535, placeholder: "587" })}
                            {smtp && text("mail_username", t("Username"), { autoComplete: "off" })}
                            {smtp && (
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="mail_password">{t("Password")}</Label>
                                    <Input
                                        id="mail_password"
                                        type="password"
                                        autoComplete="new-password"
                                        value={data.mail_password}
                                        onChange={(event) => setData("mail_password", event.target.value)}
                                        placeholder={savedPassword ? t("Saved. Leave blank to keep it.") : ""}
                                        aria-invalid={!!errors.mail_password}
                                    />
                                    <FieldError message={errors.mail_password} />
                                </div>
                            )}
                            <div className="flex flex-col gap-2 sm:col-span-2">
                                <div>
                                    <Button type="button" variant="secondary" onClick={sendTest} disabled={sending || form.isDirty}>
                                        {sending ? <Loader2 className="animate-spin" /> : <MailCheck />}
                                        {t("Send test email")}
                                    </Button>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {form.isDirty
                                        ? t("Save your changes first. The test uses the saved settings.")
                                        : t("Sends a test email to :email using the saved settings.").replace(":email", adminEmail)}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Customer email verification")}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="require_verification">{t("Ask new customers to verify their email")}</Label>
                                    <p className="text-sm text-muted-foreground">{t("They get a link by email after signing up and must open it before they can sign in.")}</p>
                                </div>
                                <Switch id="require_verification" checked={data.require_verification} onCheckedChange={(checked) => setData("require_verification", checked)} />
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
