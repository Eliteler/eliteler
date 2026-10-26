import { Head, Link, router } from "@inertiajs/react";
import { ExternalLink, Eye, EyeOff, FileText, ImageOff, MoreHorizontal, Pencil, Plus, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent } from "@admin/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import type { Paginated } from "@admin/types";

type Post = {
    id: string;
    title: string;
    summary: string;
    cover: string | null;
    category: string | null;
    tags: string[];
    published: boolean;
    url: string;
    createdAt: string | null;
};

type Props = {
    posts: Paginated<Post>;
    tab: "all" | "published" | "drafts";
    search: string;
    counts: Record<string, number>;
};

type Pending = { post: Post; mode: "publish" | "unpublish" | "delete" };

export default function Blogs({ posts, tab, search, counts }: Props) {
    const { t } = useTranslation();
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(route("admin.action.blog"), { id: pending.post.id, mode: pending.mode }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        });
    };

    const copy = pending && {
        publish: { title: t("Publish “:name”?"), description: t("The post appears on your blog straight away."), label: t("Publish") },
        unpublish: { title: t("Move “:name” to drafts?"), description: t("It's removed from your blog until you publish it again."), label: t("Move to drafts") },
        delete: { title: t("Delete “:name”?"), description: t("The post is removed from your blog and this list."), label: t("Delete") },
    }[pending.mode];

    return (
        <>
            <Head title={t("Blog posts")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Blog posts")}
                    description={t("Articles on your website's blog.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Blog posts") }]}
                    actions={
                        <Button asChild>
                            <Link href={route("admin.create.blog")}>
                                <Plus />
                                {t("New post")}
                            </Link>
                        </Button>
                    }
                />

                <ListToolbar
                    routeName="admin.blogs"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "published", label: t("Published") },
                        { value: "drafts", label: t("Drafts") },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    placeholder={t("Search by title or tag")}
                />
                <Card className="gap-0 py-0">
                    <CardContent className="p-0">
                        {posts.data.length === 0 ? (
                            <EmptyState
                                icon={FileText}
                                title={search ? t("No posts match your search") : t("No posts yet")}
                                description={search ? undefined : t("Write your first post to start your blog.")}
                                className="border-0"
                            />
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead className="w-full ps-4">{t("Post")}</TableHead>
                                        <TableHead className="hidden md:table-cell">{t("Category")}</TableHead>
                                        <TableHead className="hidden sm:table-cell">{t("Status")}</TableHead>
                                        <TableHead className="hidden lg:table-cell">{t("Created")}</TableHead>
                                        <TableHead className="w-12 pe-4 text-end">
                                            <span className="sr-only">{t("Actions")}</span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {posts.data.map((post) => (
                                        <TableRow key={post.id}>
                                            <TableCell className="max-w-0 ps-4 whitespace-normal">
                                                <Link href={route("admin.edit.blog", post.id)} className="flex items-center gap-3">
                                                    <div className="flex h-12 w-20 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-muted">
                                                        {post.cover ? (
                                                            <img src={post.cover} alt="" className="size-full object-cover" />
                                                        ) : (
                                                            <ImageOff className="size-4 text-muted-foreground" />
                                                        )}
                                                    </div>
                                                    <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                                                        <span className="truncate font-medium hover:underline">{post.title}</span>
                                                        <span className="line-clamp-1 text-xs text-muted-foreground">{post.summary}</span>
                                                        {post.tags.length > 0 && (
                                                            <span className="truncate text-xs text-muted-foreground">{post.tags.map((tag) => `#${tag}`).join(" ")}</span>
                                                        )}
                                                    </div>
                                                </Link>
                                            </TableCell>
                                            <TableCell className="hidden text-muted-foreground md:table-cell">{post.category ? t(post.category) : t("No category")}</TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                {post.published ? <Badge variant="secondary">{t("Published")}</Badge> : <Badge variant="outline">{t("Draft")}</Badge>}
                                            </TableCell>
                                            <TableCell className="hidden whitespace-nowrap text-muted-foreground lg:table-cell">{formatDate(post.createdAt)}</TableCell>
                                            <TableCell className="pe-4 text-end">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                            <MoreHorizontal />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route("admin.edit.blog", post.id)}>
                                                                <Pencil />
                                                                {t("Edit")}
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        {post.published && (
                                                            <DropdownMenuItem asChild>
                                                                <a href={post.url} target="_blank" rel="noreferrer">
                                                                    <ExternalLink />
                                                                    {t("View on website")}
                                                                </a>
                                                            </DropdownMenuItem>
                                                        )}
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onSelect={() => setPending({ post, mode: post.published ? "unpublish" : "publish" })}>
                                                            {post.published ? <EyeOff /> : <Eye />}
                                                            {post.published ? t("Move to drafts") : t("Publish")}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem variant="destructive" onSelect={() => setPending({ post, mode: "delete" })}>
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
                        )}
                    </CardContent>
                </Card>

                <Pagination data={posts} />
            </div>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                title={copy?.title.replace(":name", pending?.post.title ?? "") ?? ""}
                description={copy?.description}
                confirmLabel={copy?.label}
                destructive={pending?.mode === "delete"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}
