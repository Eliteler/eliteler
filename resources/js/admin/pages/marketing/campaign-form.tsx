import { Head, Link, useForm } from "@inertiajs/react";
import { Loader2, Send } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";

import { MailgunNotConfigured } from "./not-configured";

type Props = {
    campaign: { id: string; name: string; description: string; group: string; subject: string; body: string } | null;
    groups: { id: string; name: string; members: number }[];
    configured: boolean;
};

export default function CampaignForm({ campaign, groups, configured }: Props) {
    const { t } = useTranslation();
    const form = useForm({
        campaign_name: campaign?.name ?? "",
        campaign_description: campaign?.description ?? "",
        group: campaign?.group ?? "",
        email_subject: campaign?.subject ?? "",
        email_body: campaign?.body ?? "<p>Hi #name,</p>\n<p></p>",
    });
    const group = groups.find((g) => g.id === form.data.group);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(campaign ? route("admin.marketing.campaigns.resend", { id: campaign.id }) : route("admin.marketing.campaigns.save"));
    };

    return (
        <>
            <Head title={campaign ? t("Send campaign again") : t("New campaign")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={campaign ? t("Send campaign again") : t("New campaign")}
                    description={t("The email is sent to every contact in the group as soon as you click Send.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Email campaigns"), href: route("admin.marketing.campaigns") },
                        { label: campaign ? campaign.name : t("New") },
                    ]}
                    actions={
                        <>
                            <Button variant="outline" asChild>
                                <Link href={route("admin.marketing.campaigns")}>{t("Cancel")}</Link>
                            </Button>
                            <Button type="submit" disabled={form.processing || !configured || !group}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Send />}
                                {group ? t("Send to :count contacts").replace(":count", String(group.members)) : t("Send")}
                            </Button>
                        </>
                    }
                />
                {!configured && <MailgunNotConfigured />}
                <div className="grid max-w-6xl gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>{t("Email")}</CardTitle>
                            <CardDescription>{t("Write HTML. #name is replaced with each customer's name.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="email_subject">{t("Subject")}</Label>
                                <Input id="email_subject" value={form.data.email_subject} onChange={(e) => form.setData("email_subject", e.target.value)} required />
                                <FieldError message={form.errors.email_subject} />
                            </div>
                            <Tabs defaultValue="write">
                                <TabsList>
                                    <TabsTrigger value="write">{t("Write")}</TabsTrigger>
                                    <TabsTrigger value="preview">{t("Preview")}</TabsTrigger>
                                </TabsList>
                                <TabsContent value="write">
                                    <Textarea
                                        value={form.data.email_body}
                                        onChange={(e) => form.setData("email_body", e.target.value)}
                                        dir="ltr"
                                        spellCheck={false}
                                        className="min-h-80 font-mono text-xs [field-sizing:fixed]"
                                    />
                                </TabsContent>
                                <TabsContent value="preview">
                                    <div
                                        className="min-h-80 rounded-lg border bg-white p-6 text-sm text-neutral-900 [&_a]:underline [&_p]:my-2"
                                        dangerouslySetInnerHTML={{ __html: form.data.email_body.split("#name").join("Asha") }}
                                    />
                                </TabsContent>
                            </Tabs>
                            <FieldError message={form.errors.email_body} />
                        </CardContent>
                    </Card>
                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Recipients")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2">
                                <Select value={form.data.group} onValueChange={(value) => form.setData("group", value)}>
                                    <SelectTrigger className="w-full"><SelectValue placeholder={t("Choose a contact group")} /></SelectTrigger>
                                    <SelectContent>
                                        {groups.map((g) => (
                                            <SelectItem key={g.id} value={g.id}>{g.name} · {g.members}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.group} />
                                <Link href={route("admin.marketing.groups", { new: 1 })} className="text-xs text-primary hover:underline">{t("Create a contact group")}</Link>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("For your records")}</CardTitle>
                                <CardDescription>{t("Not shown to customers.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="campaign_name">{t("Campaign name")}</Label>
                                    <Input id="campaign_name" value={form.data.campaign_name} onChange={(e) => form.setData("campaign_name", e.target.value)} placeholder={t("e.g., October newsletter")} required />
                                    <FieldError message={form.errors.campaign_name} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="campaign_description">{t("Description")}</Label>
                                    <Textarea id="campaign_description" value={form.data.campaign_description} onChange={(e) => form.setData("campaign_description", e.target.value)} className="min-h-16" required />
                                    <FieldError message={form.errors.campaign_description} />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </>
    );
}
