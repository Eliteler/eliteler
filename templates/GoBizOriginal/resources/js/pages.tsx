/*
 | GoBiz Original — website pages and custom pages.
 | Built into ../../dist with `npm run plugin:build -- GoBizOriginal` and shipped in the template zip.
 */
import { Head, Link, router } from "@inertiajs/react";
import { ExternalLink, FilePlus2, FileText, Pencil, Settings, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import {
    Badge,
    Button,
    Card,
    CardContent,
    ConfirmDialog,
    EmptyState,
    PageHeader,
    Switch,
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
    Tooltip,
    TooltipContent,
    TooltipTrigger,
    fromNow,
} from "@gobiz/admin";

type StaticPage = { key: string; name: string; path: string | null; active: boolean; canDeactivate: boolean };
type CustomPage = { id: number; name: string; slug: string; active: boolean; updated: string | null };
type Props = { tab: "pages" | "custom"; siteUrl: string; staticPages: StaticPage[]; customPages: CustomPage[] };

export default function TemplatePages({ tab, siteUrl, staticPages, customPages }: Props) {
    const { t } = useTranslation();
    const [current, setCurrent] = useState<string>(tab);
    const [busy, setBusy] = useState<string | null>(null);
    const [deleting, setDeleting] = useState<CustomPage | null>(null);

    const toggle = (type: "static" | "custom", id: string | number) =>
        router.post(
            route("admin.web-template.gobiz-original.update-page-status"),
            { type, id: String(id) },
            { preserveScroll: true, onStart: () => setBusy(`${type}:${id}`), onFinish: () => setBusy(null) },
        );

    const remove = () =>
        deleting &&
        router.post(
            route("admin.web-template.gobiz-original.delete-page"),
            { id: deleting.id },
            { preserveScroll: true, onStart: () => setBusy("delete"), onFinish: () => (setBusy(null), setDeleting(null)) },
        );

    const address = (path: string) => (
        <a href={siteUrl + path} target="_blank" rel="noreferrer" className="inline-flex min-w-0 items-center gap-1 text-xs text-muted-foreground hover:text-foreground hover:underline">
            <span className="truncate">{path}</span>
            <ExternalLink className="size-3 shrink-0" />
        </a>
    );

    return (
        <>
            <Head title="GoBiz Original" />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title="GoBiz Original"
                    description={t("Edit your website's pages and add pages of your own.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Web templates"), href: route("admin.web-templates") },
                        { label: "GoBiz Original" },
                    ]}
                    actions={
                        <>
                            <Button variant="secondary" asChild>
                                <Link href={route("admin.web-template.gobiz-original.config")}>
                                    <Settings />
                                    {t("Template settings")}
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={route("admin.web-template.gobiz-original.add-custom-page")}>
                                    <FilePlus2 />
                                    {t("Add page")}
                                </Link>
                            </Button>
                        </>
                    }
                />

                <Tabs value={current} onValueChange={setCurrent} className="max-w-4xl">
                    <TabsList>
                        <TabsTrigger value="pages">{t("Website pages")}</TabsTrigger>
                        <TabsTrigger value="custom">
                            {t("Custom pages")}
                            {customPages.length > 0 && <Badge variant="secondary">{customPages.length}</Badge>}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="pages">
                        <Card className="py-0">
                            <CardContent className="divide-y px-0">
                                {staticPages.map((page) => {
                                    const locked = page.active && !page.canDeactivate;
                                    return (
                                        <div key={page.key} className="flex items-center gap-3 px-4 py-3">
                                            <div className="flex min-w-0 flex-1 flex-col">
                                                <span className="truncate text-sm font-medium">{page.name}</span>
                                                {page.path ? address(page.path) : <span className="text-xs text-muted-foreground">{t("Part of the home page")}</span>}
                                            </div>
                                            <Tooltip>
                                                <TooltipTrigger asChild>
                                                    <span>
                                                        <Switch
                                                            checked={page.active}
                                                            disabled={locked || busy !== null}
                                                            onCheckedChange={() => toggle("static", page.key)}
                                                            aria-label={t("Show :page").replace(":page", page.name)}
                                                        />
                                                    </span>
                                                </TooltipTrigger>
                                                <TooltipContent>{locked ? t("Your site needs this page, so it stays on.") : page.active ? t("Shown on your site") : t("Hidden from your site")}</TooltipContent>
                                            </Tooltip>
                                            <Button variant="secondary" size="sm" asChild>
                                                <Link href={route("admin.web-template.gobiz-original.edit-page", page.key)}>
                                                    <Pencil />
                                                    {t("Edit")}
                                                </Link>
                                            </Button>
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="custom">
                        {customPages.length === 0 ? (
                            <EmptyState
                                icon={FileText}
                                title={t("No custom pages yet")}
                                description={t("Add pages such as a shipping policy or an About the team page. They appear at /p/your-page.")}
                                action={
                                    <Button asChild>
                                        <Link href={route("admin.web-template.gobiz-original.add-custom-page")}>
                                            <FilePlus2 />
                                            {t("Add page")}
                                        </Link>
                                    </Button>
                                }
                            />
                        ) : (
                            <Card className="py-0">
                                <CardContent className="divide-y px-0">
                                    {customPages.map((page) => (
                                        <div key={page.id} className="flex items-center gap-3 px-4 py-3">
                                            <div className="flex min-w-0 flex-1 flex-col">
                                                <span className="truncate text-sm font-medium">{page.name}</span>
                                                <span className="flex min-w-0 items-center gap-2">
                                                    {address(`/p/${page.slug}`)}
                                                    {page.updated && <span className="hidden text-xs text-muted-foreground sm:inline">· {t("Updated :time").replace(":time", fromNow(page.updated))}</span>}
                                                </span>
                                            </div>
                                            <Switch
                                                checked={page.active}
                                                disabled={busy !== null}
                                                onCheckedChange={() => toggle("custom", page.id)}
                                                aria-label={t("Show :page").replace(":page", page.name)}
                                            />
                                            <Button variant="secondary" size="sm" asChild>
                                                <Link href={route("admin.web-template.gobiz-original.edit-custom-page", page.id)}>
                                                    <Pencil />
                                                    {t("Edit")}
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" aria-label={t("Delete")} onClick={() => setDeleting(page)}>
                                                <Trash2 />
                                            </Button>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>
                </Tabs>
            </div>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={t("Delete this page?")}
                description={t(":page will be removed from your site. This can't be undone.").replace(":page", deleting?.name ?? "")}
                confirmLabel={t("Delete")}
                destructive
                processing={busy === "delete"}
                onConfirm={remove}
            />
        </>
    );
}
