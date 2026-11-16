import { Head, Link, router } from "@inertiajs/react";
import { Mail, MoreHorizontal, Plus, Send, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";

import { MailgunNotConfigured } from "./not-configured";

type Campaign = {
    id: string;
    name: string;
    description: string;
    subject: string | null;
    group: string | null;
    recipients: number;
    sentAt: string | null;
};

type PaginationLinks = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLinks[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    campaigns: Paginated<Campaign> | Campaign[];
    search: string;
    configured: boolean;
};

export default function Campaigns({ campaigns, search, configured }: Props) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState<Campaign | null>(null);
    const [processing, setProcessing] = useState(false);

    // Safely extract campaign list whether paginated or plain array
    const campaignList: Campaign[] = Array.isArray(campaigns)
        ? campaigns
        : campaigns && Array.isArray(campaigns.data)
          ? campaigns.data
          : [];

    // Extract pagination metadata
    const pagination = Array.isArray(campaigns) ? null : campaigns;

    const remove = () =>
        deleting &&
        router.post(
            route("admin.marketing.campaigns.delete"),
            { id: deleting.id },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setDeleting(null);
                },
            },
        );

    return (
        <>
            <Head title={t("Email campaigns")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Email campaigns")}
                    description={t(
                        "Send newsletters and announcements to a group of customers.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Email campaigns") },
                    ]}
                    actions={
                        <Button asChild>
                            <Link
                                href={route("admin.marketing.campaigns.create")}
                            >
                                <Plus />
                                {t("New campaign")}
                            </Link>
                        </Button>
                    }
                />
                {!configured && <MailgunNotConfigured />}
                <ListToolbar
                    routeName="admin.marketing.campaigns"
                    tabs={[{ value: "all", label: t("All") }]}
                    tab="all"
                    counts={{
                        all: pagination
                            ? pagination.total
                            : campaignList.length,
                    }}
                    search={search}
                    placeholder={t("Search name or subject...")}
                />
                {campaignList.length === 0 ? (
                    <EmptyState icon={Mail} title={t("No campaigns yet")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">
                                        {t("Campaign")}
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        {t("Sent to")}
                                    </TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        {t("Last sent")}
                                    </TableHead>
                                    <TableHead className="w-12 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {campaignList.map((campaign) => (
                                    <TableRow key={campaign.id}>
                                        <TableCell className="max-w-[320px] ps-4">
                                            <span className="block truncate font-medium">
                                                {campaign.name}
                                            </span>
                                            <span className="block truncate text-xs text-muted-foreground">
                                                {campaign.subject ??
                                                    campaign.description}
                                            </span>
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <span className="text-sm">
                                                {campaign.group ??
                                                    t("Deleted group")}
                                            </span>
                                            <span className="block text-xs text-muted-foreground">
                                                {campaign.recipients}{" "}
                                                {t("contacts")}
                                            </span>
                                        </TableCell>
                                        <TableCell className="hidden text-sm sm:table-cell">
                                            {formatDate(
                                                campaign.sentAt
                                            )}
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon-sm"
                                                        aria-label={t(
                                                            "Actions",
                                                        )}
                                                    >
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link
                                                            href={route(
                                                                "admin.marketing.campaigns.recampaign",
                                                                {
                                                                    id: campaign.id,
                                                                },
                                                            )}
                                                        >
                                                            <Send />
                                                            {t(
                                                                "Edit and send again",
                                                            )}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        variant="destructive"
                                                        onSelect={() =>
                                                            setDeleting(
                                                                campaign,
                                                            )
                                                        }
                                                    >
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

                {/* Pagination navigation controls */}
                {pagination && pagination.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            {t("Showing :from to :to of :total results")
                                .replace(":from", String(pagination.from ?? 0))
                                .replace(":to", String(pagination.to ?? 0))
                                .replace(":total", String(pagination.total))}
                        </p>
                        <div className="flex flex-wrap items-center gap-1">
                            {pagination.links.map((link, index) => {
                                if (!link.url) {
                                    return (
                                        <span
                                            key={index}
                                            className="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-transparent px-3 text-sm text-muted-foreground/50 opacity-50"
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    );
                                }
                                return (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        preserveScroll
                                        className={cn(
                                            "inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors",
                                            link.active
                                                ? "border-primary bg-primary text-primary-foreground"
                                                : "border-input bg-background hover:bg-muted text-foreground",
                                        )}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(v) => !v && setDeleting(null)}
                title={t("Delete “:name”?").replace(
                    ":name",
                    deleting?.name ?? "",
                )}
                description={t(
                    "The campaign is removed from this list. Emails already sent are not affected.",
                )}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}
