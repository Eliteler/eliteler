/*
 | WhatsApp chat button — admin settings (up to 6 numbers).
 | Built into ../../dist with `npm run plugin:build -- WhatsAppChatButton` and shipped in the zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { Loader2, MessageCircle, Plus, Trash2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Switch, Textarea } from "@gobiz/admin";

type Contact = { name: string; number: string; message: string };

type Values = {
    show_whatsapp_chatbot: boolean;
    show_whatsapp_chatbot_on_customer_panel: boolean;
    contacts: Contact[];
};

const MAX_CONTACTS = 6;
const emptyContact = (): Contact => ({ name: "", number: "", message: "" });

export default function WhatsAppChatButtonSettings({ values }: { values: Values }) {
    const { t } = useTranslation();
    const form = useForm<Values>({
        ...values,
        contacts: values.contacts.length ? values.contacts : [emptyContact()],
    });
    const { data, setData, errors } = form;
    const fieldError = (index: number, field: keyof Contact) =>
        (errors as Record<string, string | undefined>)[`contacts.${index}.${field}`];

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            show_whatsapp_chatbot: current.show_whatsapp_chatbot ? 1 : 0,
            show_whatsapp_chatbot_on_customer_panel: current.show_whatsapp_chatbot_on_customer_panel ? 1 : 0,
        }));
        form.post(route("admin.whatsapp_chat_button_settings.update"), { preserveScroll: true });
    };

    const updateContact = (index: number, field: keyof Contact, value: string) =>
        setData(
            "contacts",
            data.contacts.map((contact, i) => (i === index ? { ...contact, [field]: value } : contact)),
        );
    const addContact = () => {
        if (data.contacts.length < MAX_CONTACTS) setData("contacts", [...data.contacts, emptyContact()]);
    };
    const removeContact = (index: number) =>
        setData("contacts", data.contacts.length === 1 ? [emptyContact()] : data.contacts.filter((_, i) => i !== index));

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
                            <CardTitle>{t("Contacts")}</CardTitle>
                            <CardDescription>{t("You can add up to 6 numbers. Visitors pick who to chat with.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <FieldError message={(errors as Record<string, string | undefined>).contacts} />
                            {data.contacts.map((contact, index) => (
                                <div key={index} className="flex flex-col gap-4 rounded-lg border p-4">
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="text-sm font-medium">
                                            {t("Contact :n").replace(":n", String(index + 1))}
                                        </span>
                                        <Button type="button" variant="ghost" size="sm" onClick={() => removeContact(index)} aria-label={t("Remove")}>
                                            <Trash2 className="size-4" />
                                            {t("Remove")}
                                        </Button>
                                    </div>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor={`contact_name_${index}`}>{t("Contact name")}</Label>
                                            <Input
                                                id={`contact_name_${index}`}
                                                value={contact.name}
                                                onChange={(event) => updateContact(index, "name", event.target.value)}
                                                placeholder={t("Sales, Support…")}
                                                maxLength={50}
                                                aria-invalid={!!fieldError(index, "name")}
                                            />
                                            <FieldError message={fieldError(index, "name")} />
                                        </div>
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor={`contact_number_${index}`}>{t("WhatsApp number")}</Label>
                                            <Input
                                                id={`contact_number_${index}`}
                                                type="tel"
                                                value={contact.number}
                                                onChange={(event) => updateContact(index, "number", event.target.value)}
                                                placeholder="919876543210"
                                                className="font-mono text-sm"
                                                aria-invalid={!!fieldError(index, "number")}
                                            />
                                            <p className="text-xs text-muted-foreground">{t("Include the country code, without + or spaces.")}</p>
                                            <FieldError message={fieldError(index, "number")} />
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor={`contact_message_${index}`}>{t("First message")}</Label>
                                        <Textarea
                                            id={`contact_message_${index}`}
                                            value={contact.message}
                                            onChange={(event) => updateContact(index, "message", event.target.value)}
                                            placeholder={t("Hi, I have a question about…")}
                                            maxLength={500}
                                            className="min-h-16"
                                            aria-invalid={!!fieldError(index, "message")}
                                        />
                                        <FieldError message={fieldError(index, "message")} />
                                    </div>
                                </div>
                            ))}
                            {data.contacts.length < MAX_CONTACTS && (
                                <Button type="button" variant="secondary" onClick={addContact} className="self-start">
                                    <Plus className="size-4" />
                                    {t("Add another number")}
                                </Button>
                            )}
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
