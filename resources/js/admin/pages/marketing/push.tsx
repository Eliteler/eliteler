import { Head, Link, useForm } from "@inertiajs/react";
import { Bell, Loader2, Send } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Textarea } from "@admin/components/ui/textarea";

export default function PushNotification({ configured, enabled, maxUploadKb }: { configured: boolean; enabled: boolean; maxUploadKb: number }) {
    const { t, i18n } = useTranslation();
    const form = useForm({ title: "", message: "", target_url: "", image: null as File | null });
    const icon = form.data.image ? URL.createObjectURL(form.data.image) : null;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.marketing.pusher.notification.send"), { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <>
            <Head title={t("Send notification")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Send notification")}
                    description={t("A browser notification to everyone who allowed notifications on your website.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Web notifications") }]}
                />
                {(!configured || !enabled) && (
                    <Alert variant="warning" className="max-w-4xl">
                        <AlertDescription className="text-foreground">
                            <span>
                                {!configured ? t("Pusher Beams isn’t set up yet, so notifications can’t be sent.") : t("The subscribe prompt is turned off, so no new visitors can subscribe.")}{" "}
                                <Link href={route("admin.marketing.pusher")} className="font-medium underline">{t("Open Pusher settings")}</Link>
                            </span>
                        </AlertDescription>
                    </Alert>
                )}
                <div className="grid max-w-4xl gap-6 md:grid-cols-5">
                    <Card className="md:col-span-3">
                        <CardHeader>
                            <CardTitle>{t("Message")}</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="title">{t("Title")}</Label>
                                <Input id="title" value={form.data.title} onChange={(e) => form.setData("title", e.target.value)} maxLength={255} placeholder={t("e.g., New themes just arrived")} required />
                                <FieldError message={form.errors.title} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="message">{t("Message")}</Label>
                                <Textarea id="message" value={form.data.message} onChange={(e) => form.setData("message", e.target.value)} maxLength={255} className="min-h-20" required />
                                <span className="text-end text-xs text-muted-foreground tabular-nums">{form.data.message.length} / 255</span>
                                <FieldError message={form.errors.message} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="target_url">{t("Link (optional)")}</Label>
                                <Input id="target_url" type="url" value={form.data.target_url} onChange={(e) => form.setData("target_url", e.target.value)} placeholder="https://" dir="ltr" />
                                <p className="text-xs text-muted-foreground">{t("Opens when someone clicks the notification.")}</p>
                                <FieldError message={form.errors.target_url} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label>{t("Icon (optional)")}</Label>
                                <ImageInput value={form.data.image} onChange={(file) => form.setData("image", file)} maxKb={maxUploadKb} previewClassName="aspect-square w-16" />
                                <FieldError message={form.errors.image} />
                            </div>
                        </CardContent>
                        <CardFooter className="justify-end border-t [.border-t]:pt-6">
                            <Button type="submit" disabled={form.processing || !configured}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Send />}
                                {t("Send to all subscribers")}
                            </Button>
                        </CardFooter>
                    </Card>
                    <Card className="h-fit md:col-span-2">
                        <CardHeader>
                            <CardTitle>{t("Preview")}</CardTitle>
                            <CardDescription>{t("Looks slightly different on each browser.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex gap-3 rounded-lg border bg-muted/30 p-3 shadow-sm" dir={i18n.dir()}>
                                <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-muted">
                                    {icon ? <img src={icon} alt="" className="size-full object-cover" /> : <Bell className="size-5 text-muted-foreground" />}
                                </div>
                                <div className="flex min-w-0 flex-col">
                                    <span className="truncate text-sm font-medium">{form.data.title || t("Title")}</span>
                                    <span className="line-clamp-3 text-xs text-muted-foreground">{form.data.message || t("Your message appears here.")}</span>
                                    {form.data.target_url && <span className="truncate text-[11px] text-muted-foreground" dir="ltr">{form.data.target_url.replace(/^https?:\/\//, "")}</span>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </>
    );
}
