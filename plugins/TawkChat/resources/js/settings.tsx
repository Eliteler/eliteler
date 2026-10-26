/*
 | Tawk.to live chat — admin settings.
 | Built into ../../dist with `npm run plugin:build -- TawkChat` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader } from "@gobiz/admin";

export default function TawkChatSettings({ tawk_chat_bot_key }: { tawk_chat_bot_key: string }) {
    const { t } = useTranslation();
    const form = useForm({ tawk_chat_bot_key });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.tawkchat_settings.update"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Tawk.to live chat")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Tawk.to live chat")}
                    description={t("Chat with your customers from inside their dashboard, using Tawk.to.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("Tawk.to live chat") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Chat widget")}</CardTitle>
                            <CardDescription>{t("In Tawk.to, open Administration → Chat widget and copy the widget code. Paste the link or just the part after embed.tawk.to/.")}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-col gap-2 sm:max-w-sm">
                                <Label htmlFor="tawk_chat_bot_key">{t("Widget ID")}</Label>
                                <Input
                                    id="tawk_chat_bot_key"
                                    value={form.data.tawk_chat_bot_key}
                                    onChange={(event) => form.setData("tawk_chat_bot_key", event.target.value)}
                                    placeholder="64f1a2b3c4d5e6/1h2j3k4l5"
                                    className="font-mono text-sm"
                                    aria-invalid={!!form.errors.tawk_chat_bot_key}
                                />
                                <p className="text-xs text-muted-foreground">{t("Leave blank to turn the chat off.")}</p>
                                <FieldError message={form.errors.tawk_chat_bot_key} />
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
