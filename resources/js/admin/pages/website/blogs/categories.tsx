import { Head, router, useForm } from "@inertiajs/react";
import {
    Eye,
    EyeOff,
    Folder,
    Loader2,
    MoreHorizontal,
    Pencil,
    Plus,
    Trash2,
} from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent } from "@admin/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@admin/components/ui/table";

type Category = {
    id: string;
    name: string;
    slug: string;
    visible: boolean;
    posts: number;
    isFallback: boolean;
};

type Props = { categories: Category[]; open: string | null };

type Pending = { category: Category; mode: "publish" | "unpublish" | "delete" };

const slugify = (text: string) =>
    text
        .toLowerCase()
        .normalize("NFKD")
        .replace(/[̀-ͯ]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

export default function BlogCategories({ categories, open }: Props) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<Category | "new" | null>(
        open === "new"
            ? "new"
            : (categories.find((category) => category.id === open) ?? null),
    );
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(
            route("admin.action.blog.category"),
            { id: pending.category.id, mode: pending.mode },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setPending(null);
                },
            },
        );
    };

    const copy =
        pending &&
        {
            publish: {
                title: t("Show “:name”?"),
                description: t("The category appears on your blog again."),
                label: t("Show"),
            },
            unpublish: {
                title: t("Hide “:name”?"),
                description: t(
                    "The category is hidden from your blog. Its posts stay published.",
                ),
                label: t("Hide"),
            },
            delete: {
                title: t("Delete “:name”?"),
                description: pending.category.posts
                    ? t("Its :count posts move to Uncategorized.").replace(
                          ":count",
                          String(pending.category.posts),
                      )
                    : t("It has no posts, so nothing else changes."),
                label: t("Delete"),
            },
        }[pending.mode];

    return (
        <>
            <Head title={t("Blog categories")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Blog categories")}
                    description={t("Group your blog posts by topic.")}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Blog categories") },
                    ]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add category")}
                        </Button>
                    }
                />

                <Card className="gap-0 py-0">
                    <CardContent className="p-0">
                        {categories.length === 0 ? (
                            <EmptyState
                                icon={Folder}
                                title={t("No categories yet")}
                                className="border-0"
                            />
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="ps-4">
                                            {t("Name")}
                                        </TableHead>
                                        <TableHead>{t("URL")}</TableHead>
                                        <TableHead>{t("Posts")}</TableHead>
                                        <TableHead>{t("Status")}</TableHead>
                                        <TableHead className="pe-4 text-end">
                                            <span className="sr-only">
                                                {t("Actions")}
                                            </span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {categories.map((category) => (
                                        <TableRow key={category.id}>
                                            <TableCell className="ps-4">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setEditing(category)
                                                    }
                                                    className="font-medium hover:underline"
                                                >
                                                    {t(category.name)}
                                                </button>
                                            </TableCell>
                                            <TableCell className="font-mono text-xs text-muted-foreground">
                                                {category.slug}
                                            </TableCell>
                                            <TableCell className="tabular-nums">
                                                {category.posts}
                                            </TableCell>
                                            <TableCell>
                                                {category.visible ? (
                                                    <Badge variant="secondary">
                                                        {t("Visible")}
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">
                                                        {t("Hidden")}
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="pe-4 text-end">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
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
                                                        <DropdownMenuItem
                                                            onSelect={() =>
                                                                setEditing(
                                                                    category,
                                                                )
                                                            }
                                                        >
                                                            <Pencil />
                                                            {t("Edit")}
                                                        </DropdownMenuItem>

                                                        {!category.isFallback &&
                                                            category.slug !==
                                                                "uncategorized" && (
                                                                <DropdownMenuItem
                                                                    onSelect={() =>
                                                                        setPending(
                                                                            {
                                                                                category,
                                                                                mode: category.visible
                                                                                    ? "unpublish"
                                                                                    : "publish",
                                                                            },
                                                                        )
                                                                    }
                                                                >
                                                                    {category.visible ? (
                                                                        <EyeOff />
                                                                    ) : (
                                                                        <Eye />
                                                                    )}
                                                                    {category.visible
                                                                        ? t(
                                                                              "Hide",
                                                                          )
                                                                        : t(
                                                                              "Show",
                                                                          )}
                                                                </DropdownMenuItem>
                                                            )}

                                                        {!category.isFallback &&
                                                            category.slug !==
                                                                "uncategorized" && (
                                                                <>
                                                                    <DropdownMenuSeparator />
                                                                    <DropdownMenuItem
                                                                        variant="destructive"
                                                                        onSelect={() =>
                                                                            setPending(
                                                                                {
                                                                                    category,
                                                                                    mode: "delete",
                                                                                },
                                                                            )
                                                                        }
                                                                    >
                                                                        <Trash2 />
                                                                        {t(
                                                                            "Delete",
                                                                        )}
                                                                    </DropdownMenuItem>
                                                                </>
                                                            )}
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
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(isOpen) => !isOpen && setEditing(null)}
            >
                <DialogContent className="sm:max-w-md">
                    {editing && (
                        <CategoryForm
                            key={editing === "new" ? "new" : editing.id}
                            category={editing === "new" ? null : editing}
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(isOpen) => !isOpen && setPending(null)}
                title={
                    copy?.title.replace(
                        ":name",
                        pending?.category.name ?? "",
                    ) ?? ""
                }
                description={copy?.description}
                confirmLabel={copy?.label}
                destructive={pending?.mode === "delete"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function CategoryForm({
    category,
    onClose,
}: {
    category: Category | null;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm({
        category_name: category?.name ?? "",
        category_slug: category?.slug ?? "",
    });
    const [slugTouched, setSlugTouched] = useState(!!category);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(
            category
                ? route("admin.update.blog.category", category.id)
                : route("admin.publish.blog.category"),
            { preserveScroll: true, onSuccess: onClose },
        );
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {category ? t("Edit category") : t("Add category")}
                </DialogTitle>
                <DialogDescription>
                    {t("The URL is used in links to this category's posts.")}
                </DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="category_name">{t("Name")}</Label>
                <Input
                    id="category_name"
                    value={form.data.category_name}
                    onChange={(event) =>
                        form.setData((current) => ({
                            ...current,
                            category_name: event.target.value,
                            category_slug: slugTouched
                                ? current.category_slug
                                : slugify(event.target.value),
                        }))
                    }
                    aria-invalid={!!form.errors.category_name}
                    required
                />
                <FieldError message={form.errors.category_name} />
            </div>
            <div className="flex flex-col gap-2">
                <Label htmlFor="category_slug">{t("URL")}</Label>
                <Input
                    id="category_slug"
                    value={form.data.category_slug}
                    disabled={category?.isFallback}
                    onChange={(event) => {
                        setSlugTouched(true);
                        form.setData("category_slug", event.target.value);
                    }}
                    onBlur={() =>
                        form.setData(
                            "category_slug",
                            slugify(form.data.category_slug),
                        )
                    }
                    className="font-mono text-sm"
                    aria-invalid={!!form.errors.category_slug}
                />
                <FieldError message={form.errors.category_slug} />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {category ? t("Save") : t("Add category")}
                </Button>
            </div>
        </form>
    );
}
