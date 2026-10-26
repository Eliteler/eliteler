import { Head, Link, router } from "@inertiajs/react";
import { Mail, MoreHorizontal, Plus, Send, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@admin/components/ui/dropdown-menu";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import type { Paginated } from "@admin/types";

import { MailgunNotConfigured } from "./not-configured";

type Campaign = { id: string; name: string; description: string; subject: string | null; group: string | null; recipients: number; sentAt: string | null };

export default function Campaigns({ campaigns, search, configured }: { campaigns: Paginated<Campaign>; search: string; configured: boolean }) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState<Campaign | null>(null);
    const [processing, setProcessing] = useState(false);

    const remove = () =>
        deleting &&
        router.post(route("admin.marketing.campaigns.delete"), { id: deleting.id }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });

    return (
        <>
            <Head title={t("Email campaigns")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Email campaigns")}
                    description={t("Send newsletters and announcements to a group of customers.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Email campaigns") }]}
                    actions={
                        <Button asChild>
                            <Link href={route("admin.marketing.campaigns.create")}>
                                <Plus />
                                {t("New campaign")}
                            </Link>
                        </Button>
                    }
                />
                {!configured && <MailgunNotConfigured />}
                <ListToolbar routeName="admin.marketing.campaigns" tabs={[{ value: "all", label: t("All") }]} tab="all" counts={{ all: campaigns.total }} search={search} placeholder={t("Search name or subject...")} />
                {campaigns.data.length === 0 ? (
                    <EmptyState icon={Mail} title={t("No campaigns yet")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Campaign")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Sent to")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Last sent")}</TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {campaigns.data.map((campaign) => (
                                    <TableRow key={campaign.id}>
                                        <TableCell className="max-w-[320px] ps-4">
                                            <span className="block truncate font-medium">{campaign.name}</span>
                                            <span className="block truncate text-xs text-muted-foreground">{campaign.subject ?? campaign.description}</span>
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <span className="text-sm">{campaign.group ?? t("Deleted group")}</span>
                                            <span className="block text-xs text-muted-foreground">{campaign.recipients} {t("contacts")}</span>
                                        </TableCell>
                                        <TableCell className="hidden text-sm sm:table-cell">{formatDate(campaign.sentAt, "dd MMM yyyy, HH:mm")}</TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}><MoreHorizontal /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route("admin.marketing.campaigns.recampaign", { id: campaign.id })}>
                                                            <Send />
                                                            {t("Edit and send again")}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(campaign)}>
                                                        <Trash2 />
                                                        {t("Delete")}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}
                <Pagination data={campaigns} />
            </div>
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(v) => !v && setDeleting(null)}
                title={t("Delete “:name”?").replace(":name", deleting?.name ?? "")}
                description={t("The campaign is removed from this list. Emails already sent are not affected.")}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}
