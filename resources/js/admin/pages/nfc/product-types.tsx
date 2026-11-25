import { Head, router, useForm } from "@inertiajs/react";
import {
    Loader2,
    MoreHorizontal,
    Pencil,
    Plus,
    Tags,
    Trash2,
} from "lucide-react";
import { useState, type FormEvent } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
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
import type { Paginated } from "@admin/types";

type ProductType = {
    id: number;
    name: string;
    productsCount: number;
};

export default function ProductTypes({
    productTypes,
}: {
    productTypes: Paginated<ProductType>;
}) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<ProductType | "new" | null>(null);
    const [deleting, setDeleting] = useState<number | null>(null);
    const [deleteError, setDeleteError] = useState("");
    const [processing, setProcessing] = useState(false);
    const pending = productTypes.data.find((type) => type.id === deleting);

    function closeDelete() {
        setDeleting(null);
        setDeleteError("");
    }

    function deleteType() {
        if (!pending || processing) return;

        setDeleteError("");
        router.delete(route("admin.smart-product-types.destroy", pending.id), {
            data: { expected_product_count: pending.productsCount },
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onSuccess: closeDelete,
            onError: (errors) => setDeleteError(errors.product_usage ?? ""),
            onFinish: () => setProcessing(false),
        });
    }

    const warning = pending?.productsCount
        ? pending.productsCount === 1
            ? t("One product will move to Generic.")
            : t(":count products will move to Generic.").replace(
                  ":count",
                  String(pending.productsCount),
              )
        : undefined;

    return (
        <>
            <Head title={t("Product Types")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Product Types")}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        {
                            label: t("Smart Products"),
                            href: route("admin.designs"),
                        },
                        { label: t("Product Types") },
                    ]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add product type")}
                        </Button>
                    }
                />

                <Card className="gap-0 py-0">
                    <CardContent className="p-0">
                        {productTypes.data.length ? (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="ps-4">
                                            {t("Name")}
                                        </TableHead>
                                        <TableHead>{t("Products")}</TableHead>
                                        <TableHead className="pe-4 text-end">
                                            <span className="sr-only">
                                                {t("Actions")}
                                            </span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {productTypes.data.map((type) => (
                                        <TableRow key={type.id}>
                                            <TableCell className="ps-4 font-medium">
                                                {type.name}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant="secondary"
                                                    className="tabular-nums"
                                                >
                                                    {type.productsCount}
                                                </Badge>
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
                                                                setEditing(type)
                                                            }
                                                        >
                                                            <Pencil />
                                                            {t("Edit")}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            onSelect={() =>
                                                                setDeleting(
                                                                    type.id,
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
                        ) : (
                            <EmptyState
                                icon={Tags}
                                title={t("No product types yet")}
                                className="border-0"
                            />
                        )}
                    </CardContent>
                    <Pagination data={productTypes} className="border-t p-4" />
                </Card>
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
            >
                <DialogContent className="sm:max-w-md">
                    {editing !== null && (
                        <ProductTypeForm
                            key={editing === "new" ? "new" : editing.id}
                            type={editing === "new" ? null : editing}
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={pending !== undefined}
                onOpenChange={(open) => !open && !processing && closeDelete()}
                title={t("Delete “:name”?").replace(
                    ":name",
                    pending?.name ?? "",
                )}
                description={
                    [deleteError, warning].filter(Boolean).join(" ") ||
                    undefined
                }
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={deleteType}
            />
        </>
    );
}

function ProductTypeForm({
    type,
    onClose,
}: {
    type: ProductType | null;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm({ name: type?.name ?? "" });

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onClose };

        if (type) {
            form.patch(
                route("admin.smart-product-types.update", type.id),
                options,
            );
        } else {
            form.post(route("admin.smart-product-types.store"), options);
        }
    }

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {type ? t("Edit product type") : t("Add product type")}
                </DialogTitle>
                <DialogDescription>
                    {t("This name appears on products assigned to this type.")}
                </DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="product-type-name">{t("Name")}</Label>
                <Input
                    id="product-type-name"
                    value={form.data.name}
                    onChange={(event) =>
                        form.setData("name", event.target.value)
                    }
                    aria-invalid={!!form.errors.name}
                    maxLength={100}
                    required
                />
                <FieldError message={form.errors.name} />
            </div>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button
                    type="button"
                    variant="outline"
                    onClick={onClose}
                    disabled={form.processing}
                >
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Save")}
                </Button>
            </div>
        </form>
    );
}
