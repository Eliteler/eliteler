/*
 | WhatsApp chat button — admin settings.
 | Built into ../../dist with `npm run plugin:build -- WhatsAppChatButton` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2, MessageCircle } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Switch, Textarea } from "@gobiz/admin";

type Values = {
    show_whatsapp_chatbot: boolean;
    show_whatsapp_chatbot_on_customer_panel: boolean;
    whatsapp_chatbot_mobile_number: string;
    whatsapp_chatbot_message: string;
};

export default function WhatsAppChatButtonSettings({ values }: { values: Values }) {
    const { t } = useTranslation();
    const form = useForm(values);
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            show_whatsapp_chatbot: current.show_whatsapp_chatbot ? 1 : 0,
            show_whatsapp_chatbot_on_customer_panel: current.show_whatsapp_chatbot_on_customer_panel ? 1 : 0,
        }));
        form.post(route("admin.whatsapp_chat_button_settings.update"), { preserveScroll: true });
    };

    const toggle = (id: "show_whatsapp_chatbot" | "show_whatsapp_chatbot_on_customer_panel", label: string, description: string) => (
        <div className="flex items-start justify-between gap-4">
            <div className="flex flex-col gap-1">
                <Label htmlFor={id}>{label}</Label>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
            <Switch id={id} checked={data[id]} onCheckedChange={(checked) => setData(id, checked)} />
        </div>
    );

    return (
        <>
            <Head title={t("WhatsApp chat button")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("WhatsApp chat button")}
                    description={t("A floating button that opens a WhatsApp chat with you.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("WhatsApp chat button") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MessageCircle className="size-5 text-primary" />
                                {t("Where to show it")}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            {toggle("show_whatsapp_chatbot", t("On your website"), t("Visitors can message you from any public page."))}
                            {toggle("show_whatsapp_chatbot_on_customer_panel", t("In the customer dashboard"), t("Signed-in customers can message you for help."))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Chat details")}</CardTitle>
                            <CardDescription>{t("The chat opens in WhatsApp with this number and the message already typed.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex flex-col gap-2 sm:max-w-xs">
                                <Label htmlFor="whatsapp_chatbot_mobile_number">{t("WhatsApp number")}</Label>
                                <Input
                                    id="whatsapp_chatbot_mobile_number"
                                    type="tel"
                                    value={data.whatsapp_chatbot_mobile_number}
                                    onChange={(event) => setData("whatsapp_chatbot_mobile_number", event.target.value)}
                                    placeholder="919876543210"
                                    className="font-mono text-sm"
                                    aria-invalid={!!errors.whatsapp_chatbot_mobile_number}
                                />
                                <p className="text-xs text-muted-foreground">{t("Include the country code, without + or spaces.")}</p>
                                <FieldError message={errors.whatsapp_chatbot_mobile_number} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="whatsapp_chatbot_message">{t("First message")}</Label>
                                <Textarea
                                    id="whatsapp_chatbot_message"
                                    value={data.whatsapp_chatbot_message}
                                    onChange={(event) => setData("whatsapp_chatbot_message", event.target.value)}
                                    placeholder={t("Hi, I have a question about…")}
                                    maxLength={500}
                                    className="min-h-20"
                                    aria-invalid={!!errors.whatsapp_chatbot_message}
                                />
                                <FieldError message={errors.whatsapp_chatbot_message} />
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
