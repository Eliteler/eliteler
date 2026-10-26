import { Head, useForm } from "@inertiajs/react";
import { Eye, EyeOff, Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { formatDate } from "@admin/lib/date";

type Account = {
    name: string;
    email: string;
    avatar: string;
    hasAvatar: boolean;
    role: string;
    joinedAt: string | null;
};

type Props = { account: Account; maxUploadKb: number };

export default function AccountPage({ account, maxUploadKb }: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t("My account")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("My account")}
                    description={t("Your profile and sign-in details.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("My account") }]}
                />

                <div className="grid max-w-4xl gap-6">
                    <Card className="flex-row items-center gap-4 p-6">
                        <img src={account.avatar} alt="" className="size-16 shrink-0 rounded-full border object-cover" />
                        <div className="flex min-w-0 flex-col gap-1">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="truncate text-lg font-semibold">{account.name}</span>
                                <Badge variant="secondary">{account.role}</Badge>
                            </div>
                            <span className="truncate text-sm text-muted-foreground">{account.email}</span>
                            {account.joinedAt && (
                                <span className="text-xs text-muted-foreground">
                                    {t("Member since :date").replace(":date", formatDate(account.joinedAt))}
                                </span>
                            )}
                        </div>
                    </Card>

                    <ProfileForm account={account} maxUploadKb={maxUploadKb} />
                    <PasswordForm />
                </div>
            </div>
        </>
    );
}

function ProfileForm({ account, maxUploadKb }: Props) {
    const { t } = useTranslation();
    const form = useForm<{ name: string; email: string; profile_picture: File | null }>({
        name: account.name,
        email: account.email,
        profile_picture: null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.account"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.setData("profile_picture", null),
        });
    };

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Profile")}</CardTitle>
                    <CardDescription>{t("Your name and photo appear in the admin panel. You sign in with this email.")}</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-5 sm:grid-cols-[auto_1fr]">
                    <div className="flex flex-col gap-2">
                        <Label>{t("Photo")}</Label>
                        <ImageInput
                            value={form.data.profile_picture}
                            currentUrl={account.hasAvatar ? account.avatar : null}
                            onChange={(file) => form.setData("profile_picture", file)}
                            maxKb={maxUploadKb}
                            invalid={!!form.errors.profile_picture}
                            previewClassName="size-20 rounded-full object-cover"
                        />
                        <FieldError message={form.errors.profile_picture} />
                    </div>
                    <div className="flex flex-col gap-4">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="name">{t("Name")}</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(event) => form.setData("name", event.target.value)}
                                autoComplete="name"
                                aria-invalid={!!form.errors.name}
                                required
                            />
                            <FieldError message={form.errors.name} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="email">{t("Email")}</Label>
                            <Input
                                id="email"
                                type="email"
                                value={form.data.email}
                                onChange={(event) => form.setData("email", event.target.value)}
                                autoComplete="email"
                                aria-invalid={!!form.errors.email}
                                required
                            />
                            <FieldError message={form.errors.email} />
                        </div>
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
    );
}

function PasswordForm() {
    const { t } = useTranslation();
    const [show, setShow] = useState(false);
    const form = useForm({ current_password: "", new_password: "", new_password_confirmation: "" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.password"), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: () => form.reset("current_password"),
        });
    };

    const field = (name: keyof typeof form.data, label: string, autoComplete: string) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Input
                id={name}
                type={show ? "text" : "password"}
                value={form.data[name]}
                onChange={(event) => form.setData(name, event.target.value)}
                autoComplete={autoComplete}
                aria-invalid={!!form.errors[name]}
                required
            />
            <FieldError message={form.errors[name]} />
        </div>
    );

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Password")}</CardTitle>
                    <CardDescription>{t("Use at least 8 characters. You'll stay signed in on this device.")}</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4 sm:grid-cols-3">
                    {field("current_password", t("Current password"), "current-password")}
                    {field("new_password", t("New password"), "new-password")}
                    {field("new_password_confirmation", t("Confirm new password"), "new-password")}
                </CardContent>
                <CardFooter className="justify-between border-t pt-6">
                    <Button type="button" variant="secondary" size="sm" onClick={() => setShow((value) => !value)}>
                        {show ? <EyeOff /> : <Eye />}
                        {show ? t("Hide passwords") : t("Show passwords")}
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Change password")}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    );
}
