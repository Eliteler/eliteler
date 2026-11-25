import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import {
    ArrowDown,
    ArrowUp,
    Boxes,
    Images,
    Loader2,
    MoreHorizontal,
    Nfc,
    Package,
    Pencil,
    Plus,
    Power,
    PowerOff,
    Trash2,
    X,
} from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ListToolbar } from "@admin/components/list-toolbar";
import {
    MultiImageInput,
    photoKey,
    photosPayload,
    type PhotoItem,
} from "@admin/components/multi-image-input";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardFooter } from "@admin/components/ui/card";
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@admin/components/ui/select";
import { Separator } from "@admin/components/ui/separator";
import { Switch } from "@admin/components/ui/switch";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

type Photo = { path: string; url: string };

/** A file the customer is asked to upload for printing (name + suggested resolution). */
type ImageAsset = { key: string; name: string; resolution: string; required: boolean };

type Design = {
    id: string;
    name: string;
    type: string;
    short: string | null;
    description: string | null;
    photos: Photo[];
    price: number;
    compareAt: number | null;
    stock: number;
    generatesKeys: boolean;
    allowLogo: boolean;
    imageAssets: ImageAsset[];
    linkType: string;
    sortOrder: number;
    active: boolean;
    orders: number;
    sold: number;
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
    designs: Paginated<Design>;
    filters: { type: string; tab: string; search: string };
    counts: Record<string, number>;
    types: Record<string, string>;
    linkTypes: Record<string, string>;
    lowStock: number;
    maxPhotos: number;
    maxUploadKb: number;
    open: string | null;
};

type Pending = { design: Design; action: "activate" | "deactivate" | "delete" };

export default function NfcDesigns({
    designs,
    filters,
    counts,
    types,
    linkTypes,
    lowStock,
    maxPhotos,
    maxUploadKb,
    open,
}: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const cardList = designs.data;

    const [editing, setEditing] = useState<Design | "new" | null>(
        open === "new"
            ? "new"
            : (cardList.find((design) => design.id === open) ?? null),
    );
    const [stockFor, setStockFor] = useState<Design | null>(null);
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(
            route("admin.action.design"),
            { id: pending.design.id, status: pending.action },
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

    const filterType = (type: string) =>
        router.get(
            route("admin.designs"),
            {
                ...(type !== "all" ? { type } : {}),
                ...(filters.tab !== "all" ? { tab: filters.tab } : {}),
                ...(filters.search ? { search: filters.search } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );

    const confirmCopy =
        pending &&
        {
            activate: {
                title: t("Make “:name” available?"),
                description: t("Customers can order this product."),
                label: t("Make available"),
            },
            deactivate: {
                title: t("Hide “:name”?"),
                description: t(
                    "Customers can no longer order this product. Existing orders are not affected.",
                ),
                label: t("Hide"),
            },
            delete: {
                title: t("Delete “:name”?"),
                description: t(
                    "The product is removed from this list and can't be ordered. Existing orders are not affected.",
                ),
                label: t("Delete"),
            },
        }[pending.action];

    const filtered =
        filters.type !== "all" || filters.tab !== "all" || !!filters.search;

    return (
        <>
            <Head title={t("Smart Products")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Smart Products")}
                    description={t(
                        "NFC cards, stands, tags and other products customers can order from you.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Smart Products") },
                    ]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add product")}
                        </Button>
                    }
                />

                <ListToolbar
                    routeName="admin.designs"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "active", label: t("Available") },
                        { value: "hidden", label: t("Hidden") },
                    ]}
                    tab={filters.tab}
                    counts={counts}
                    search={filters.search}
                    placeholder={t("Search products")}
                    extraQuery={
                        filters.type !== "all" ? { type: filters.type } : {}
                    }
                >
                    <Select value={filters.type} onValueChange={filterType}>
                        <SelectTrigger className="w-40">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{t("All types")}</SelectItem>
                            {Object.entries(types).map(([value, label]) => (
                                <SelectItem key={value} value={value}>
                                    {label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </ListToolbar>

                {cardList.length === 0 ? (
                    <EmptyState
                        icon={Nfc}
                        title={
                            filtered
                                ? t("No products match")
                                : t("No products yet")
                        }
                        description={
                            filtered
                                ? t("Try another filter or search.")
                                : t("Add a product so customers can order it.")
                        }
                        action={
                            filtered ? undefined : (
                                <Button
                                    size="sm"
                                    onClick={() => setEditing("new")}
                                >
                                    <Plus />
                                    {t("Add product")}
                                </Button>
                            )
                        }
                    />
                ) : (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            {cardList.map((design) => {
                                const out = design.stock <= 0;
                                const low = !out && design.stock < lowStock;
                                const cover = design.photos[0]?.url;
                                return (
                                    <Card
                                        key={design.id}
                                        className={cn(
                                            "gap-0 overflow-hidden py-0",
                                            !design.active && "bg-muted/30",
                                        )}
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setEditing(design)}
                                            className="relative aspect-[4/3] overflow-hidden bg-muted"
                                            title={t("Edit")}
                                        >
                                            {cover && (
                                                <img
                                                    src={cover}
                                                    alt={design.name}
                                                    className={cn(
                                                        "size-full object-cover",
                                                        !design.active &&
                                                            "opacity-60",
                                                    )}
                                                />
                                            )}
                                            <span className="absolute start-2 top-2 flex gap-1">
                                                <Badge
                                                    variant="secondary"
                                                    className="bg-background/90"
                                                >
                                                    {types[design.type] ??
                                                        design.type}
                                                </Badge>
                                                {!design.active && (
                                                    <Badge
                                                        variant="secondary"
                                                        className="bg-background/90"
                                                    >
                                                        {t("Hidden")}
                                                    </Badge>
                                                )}
                                            </span>
                                            {design.photos.length > 1 && (
                                                <span className="absolute end-2 bottom-2 flex items-center gap-1 rounded-full bg-background/90 px-2 py-0.5 text-[11px] font-medium shadow">
                                                    <Images className="size-3" />
                                                    {design.photos.length}
                                                </span>
                                            )}
                                        </button>
                                        <CardContent className="flex flex-1 flex-col gap-2 p-4">
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="flex min-w-0 flex-col">
                                                    <span className="truncate font-medium">
                                                        {design.name}
                                                    </span>
                                                    <span className="flex items-baseline gap-2">
                                                        <span className="text-lg font-semibold tabular-nums">
                                                            {design.price > 0
                                                                ? formatCurrency(
                                                                      design.price,
                                                                      currency,
                                                                  )
                                                                : t("Free")}
                                                        </span>
                                                        {design.compareAt !==
                                                            null &&
                                                            design.compareAt >
                                                                design.price && (
                                                                <span className="text-sm text-muted-foreground tabular-nums line-through">
                                                                    {formatCurrency(
                                                                        design.compareAt,
                                                                        currency,
                                                                    )}
                                                                </span>
                                                            )}
                                                    </span>
                                                </div>
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
                                                                    design,
                                                                )
                                                            }
                                                        >
                                                            <Pencil />
                                                            {t("Edit")}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onSelect={() =>
                                                                setStockFor(
                                                                    design,
                                                                )
                                                            }
                                                        >
                                                            <Boxes />
                                                            {t("Adjust stock")}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            onSelect={() =>
                                                                setPending({
                                                                    design,
                                                                    action: design.active
                                                                        ? "deactivate"
                                                                        : "activate",
                                                                })
                                                            }
                                                        >
                                                            {design.active ? (
                                                                <PowerOff />
                                                            ) : (
                                                                <Power />
                                                            )}
                                                            {design.active
                                                                ? t(
                                                                      "Hide from customers",
                                                                  )
                                                                : t(
                                                                      "Make available",
                                                                  )}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            onSelect={() =>
                                                                setPending({
                                                                    design,
                                                                    action: "delete",
                                                                })
                                                            }
                                                        >
                                                            <Trash2 />
                                                            {t("Delete")}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                            {(design.short ||
                                                design.description) && (
                                                <p className="line-clamp-2 text-sm text-muted-foreground">
                                                    {design.short ||
                                                        design.description}
                                                </p>
                                            )}
                                            <div className="mt-auto flex flex-wrap gap-1 pt-1">
                                                {design.generatesKeys ? (
                                                    <Badge variant="outline">
                                                        <Nfc />
                                                        {linkTypes[
                                                            design.linkType
                                                        ] ?? design.linkType}
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">
                                                        {t("No NFC link")}
                                                    </Badge>
                                                )}
                                            </div>
                                        </CardContent>
                                        <CardFooter className="grid grid-cols-2 gap-2 border-t p-4 text-sm">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setStockFor(design)
                                                }
                                                className="flex flex-col items-start rounded-md text-start hover:opacity-80"
                                            >
                                                <span className="text-xs text-muted-foreground">
                                                    {t("In stock")}
                                                </span>
                                                <span
                                                    className={cn(
                                                        "font-semibold tabular-nums",
                                                        out &&
                                                            "text-destructive",
                                                        low && "text-warning",
                                                    )}
                                                >
                                                    {design.stock.toLocaleString()}
                                                    {out && (
                                                        <span className="ms-1.5 text-xs font-normal">
                                                            {t("Sold out")}
                                                        </span>
                                                    )}
                                                    {low && (
                                                        <span className="ms-1.5 text-xs font-normal">
                                                            {t("Running low")}
                                                        </span>
                                                    )}
                                                </span>
                                            </button>
                                            <div className="flex flex-col">
                                                <span className="text-xs text-muted-foreground">
                                                    {t("Sold")}
                                                </span>
                                                <span className="font-semibold tabular-nums">
                                                    {design.sold.toLocaleString()}
                                                    <span className="ms-1.5 text-xs font-normal text-muted-foreground">
                                                        {design.orders}{" "}
                                                        {t("orders")}
                                                    </span>
                                                </span>
                                            </div>
                                        </CardFooter>
                                    </Card>
                                );
                            })}
                        </div>

                        {designs.last_page > 1 && (
                            <div className="flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row">
                                <p className="text-sm text-muted-foreground">
                                    {t("Showing :from to :to of :total results")
                                        .replace(
                                            ":from",
                                            String(designs.from ?? 0),
                                        )
                                        .replace(":to", String(designs.to ?? 0))
                                        .replace(
                                            ":total",
                                            String(designs.total),
                                        )}
                                </p>
                                <div className="flex flex-wrap items-center gap-1">
                                    {designs.links.map((link, index) => {
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
                    </>
                )}
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(value) => !value && setEditing(null)}
            >
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                    {editing && (
                        <ProductForm
                            key={editing === "new" ? "new" : editing.id}
                            design={editing === "new" ? null : editing}
                            types={types}
                            linkTypes={linkTypes}
                            maxPhotos={maxPhotos}
                            maxUploadKb={maxUploadKb}
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>
            <StockDialog design={stockFor} onClose={() => setStockFor(null)} />
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={
                    confirmCopy?.title.replace(
                        ":name",
                        pending?.design.name ?? "",
                    ) ?? ""
                }
                description={confirmCopy?.description}
                confirmLabel={confirmCopy?.label}
                destructive={pending?.action !== "activate"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function ProductForm({
    design,
    types,
    linkTypes,
    maxPhotos,
    maxUploadKb,
    onClose,
}: {
    design: Design | null;
    types: Record<string, string>;
    linkTypes: Record<string, string>;
    maxPhotos: number;
    maxUploadKb: number;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const form = useForm({
        nfc_card_id: design?.id ?? "",
        nfc_card_name: design?.name ?? "",
        product_type: design?.type ?? (types.nfc_card ? "nfc_card" : "generic"),
        short_description: design?.short ?? "",
        nfc_card_description: design?.description ?? "",
        nfc_card_price: String(design?.price ?? ""),
        compare_at_price:
            design?.compareAt !== null && design?.compareAt !== undefined
                ? String(design.compareAt)
                : "",
        nfc_card_available_stocks: String(design?.stock ?? ""),
        sort_order: String(design?.sortOrder ?? 0),
        generates_keys: design?.generatesKeys ?? true,
        allow_logo_upload: design?.allowLogo ?? false,
        image_assets: (design?.imageAssets?.length
            ? design.imageAssets
            : [{ key: "", name: "Logo", resolution: "", required: false }]) as ImageAsset[],
        default_link_type: design?.linkType ?? "vcard",
        photos: (design?.photos ?? []).map(
            (photo): PhotoItem => ({
                key: photoKey(),
                path: photo.path,
                url: photo.url,
            }),
        ),
    });
    const { data, setData, errors } = form;
    const error = (key: string) =>
        (errors as Record<string, string | undefined>)[key];
    const photoError =
        error("photos") ??
        Object.entries(errors).find(([key]) => key.startsWith("photos."))?.[1];

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            photos: photosPayload(current.photos),
        }));
        form.post(
            design ? route("admin.update.design") : route("admin.save.design"),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: onClose,
            },
        );
    };

    const moneyInput = (
        id: "nfc_card_price" | "compare_at_price",
        required: boolean,
    ) => (
        <div className="relative">
            <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                {currency.symbol}
            </span>
            <Input
                id={id}
                type="number"
                min={0}
                step="0.01"
                value={data[id]}
                onChange={(event) => setData(id, event.target.value)}
                aria-invalid={!!errors[id]}
                className="ps-8"
                required={required}
            />
        </div>
    );

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {design ? t("Edit product") : t("Add product")}
                </DialogTitle>
                <DialogDescription>
                    {t(
                        "Customers see the photos, name, price and description when they order.",
                    )}
                </DialogDescription>
            </DialogHeader>

            <div className="flex flex-col gap-2">
                <Label>{t("Photos")}</Label>
                <MultiImageInput
                    value={data.photos}
                    onChange={(items) => setData("photos", items)}
                    max={maxPhotos}
                    maxKb={maxUploadKb}
                    invalid={!!photoError}
                />
                <p className="text-xs text-muted-foreground">
                    {t(
                        "For cards, add the front first and the back second; they are used as the card's two sides.",
                    )}
                </p>
                <FieldError message={photoError} />
            </div>

            <div className="grid gap-4 sm:grid-cols-[1fr_12rem]">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="nfc_card_name">{t("Name")}</Label>
                    <Input
                        id="nfc_card_name"
                        value={data.nfc_card_name}
                        onChange={(event) =>
                            setData("nfc_card_name", event.target.value)
                        }
                        placeholder={t("e.g., Google Review NFC Stand")}
                        aria-invalid={!!errors.nfc_card_name}
                        required
                    />
                    <FieldError message={errors.nfc_card_name} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label>{t("Type")}</Label>
                    <Select
                        value={data.product_type}
                        onValueChange={(value) => setData("product_type", value)}
                    >
                        <SelectTrigger className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {Object.entries(types).map(([value, label]) => (
                                <SelectItem key={value} value={value}>
                                    {label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <FieldError message={errors.product_type} />
                </div>
            </div>

            <div className="flex flex-col gap-2">
                <Label htmlFor="short_description">
                    {t("Short description")}
                </Label>
                <Input
                    id="short_description"
                    value={data.short_description}
                    onChange={(event) =>
                        setData("short_description", event.target.value)
                    }
                    maxLength={255}
                    placeholder={t("One line shown in the product list")}
                    aria-invalid={!!errors.short_description}
                />
                <FieldError message={errors.short_description} />
            </div>

            <div className="flex flex-col gap-2">
                <Label htmlFor="nfc_card_description">{t("Description")}</Label>
                <Textarea
                    id="nfc_card_description"
                    value={data.nfc_card_description}
                    onChange={(event) =>
                        setData("nfc_card_description", event.target.value)
                    }
                    aria-invalid={!!errors.nfc_card_description}
                    className="min-h-24"
                />
                <FieldError message={errors.nfc_card_description} />
            </div>

            <Separator />

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="nfc_card_price">{t("Price")}</Label>
                    {moneyInput("nfc_card_price", true)}
                    <FieldError message={errors.nfc_card_price} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="compare_at_price">
                        {t("Compare-at price")}
                    </Label>
                    {moneyInput("compare_at_price", false)}
                    <p className="text-xs text-muted-foreground">
                        {t("Optional. Shown crossed out next to the price.")}
                    </p>
                    <FieldError message={errors.compare_at_price} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="nfc_card_available_stocks">
                        {t("In stock")}
                    </Label>
                    <Input
                        id="nfc_card_available_stocks"
                        type="number"
                        min={0}
                        value={data.nfc_card_available_stocks}
                        onChange={(event) =>
                            setData(
                                "nfc_card_available_stocks",
                                event.target.value,
                            )
                        }
                        aria-invalid={!!errors.nfc_card_available_stocks}
                        required
                    />
                    <FieldError message={errors.nfc_card_available_stocks} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="sort_order">{t("Sort order")}</Label>
                    <Input
                        id="sort_order"
                        type="number"
                        min={0}
                        value={data.sort_order}
                        onChange={(event) =>
                            setData("sort_order", event.target.value)
                        }
                        aria-invalid={!!errors.sort_order}
                    />
                    <p className="text-xs text-muted-foreground">
                        {t("Lower numbers are shown first.")}
                    </p>
                    <FieldError message={errors.sort_order} />
                </div>
            </div>

            <Separator />

            <div className="flex flex-col gap-3">
                <label className="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-3">
                    <span className="flex flex-col gap-0.5">
                        <span className="text-sm font-medium">
                            {t("Has an NFC chip")}
                        </span>
                        <span className="text-xs text-muted-foreground">
                            {t(
                                "Each item gets its own NFC link and activation key once paid. Turn off for accessories without a chip.",
                            )}
                        </span>
                    </span>
                    <Switch
                        checked={data.generates_keys}
                        onCheckedChange={(checked) =>
                            setData("generates_keys", checked)
                        }
                    />
                </label>
                {data.generates_keys && (
                    <div className="flex flex-col gap-2 rounded-lg border p-3">
                        <Label>{t("Opens by default")}</Label>
                        <Select
                            value={data.default_link_type}
                            onValueChange={(value) =>
                                setData("default_link_type", value)
                            }
                        >
                            <SelectTrigger className="w-full sm:w-64">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(linkTypes).map(
                                    ([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ),
                                )}
                            </SelectContent>
                        </Select>
                        <p className="text-xs text-muted-foreground">
                            {t(
                                "What a tap opens until the customer chooses. They can change it any time from their dashboard.",
                            )}
                        </p>
                        <FieldError message={errors.default_link_type} />
                    </div>
                )}
                <div className="flex flex-col gap-3 rounded-lg border p-3">
                    <label className="flex cursor-pointer items-center justify-between gap-4">
                        <span className="flex flex-col gap-0.5">
                            <span className="text-sm font-medium">
                                {t("Image assets")}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {t(
                                    "Files the customer uploads for printing (logo, artwork…). Images or PDF, after the order is paid.",
                                )}
                            </span>
                        </span>
                        <Switch
                            checked={data.allow_logo_upload}
                            onCheckedChange={(checked) =>
                                setData("allow_logo_upload", checked)
                            }
                        />
                    </label>
                    {data.allow_logo_upload && (
                        <ImageAssetsEditor
                            value={data.image_assets}
                            onChange={(rows) => setData("image_assets", rows)}
                            errors={errors as Record<string, string | undefined>}
                        />
                    )}
                </div>
            </div>

            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button
                    type="submit"
                    disabled={
                        form.processing ||
                        data.photos.length === 0 ||
                        (!!design && !form.isDirty)
                    }
                >
                    {form.processing && <Loader2 className="animate-spin" />}
                    {design ? t("Save changes") : t("Add product")}
                </Button>
            </div>
        </form>
    );
}

const MAX_ASSETS = 10;

/** Rows of image assets: name, suggested resolution, required; reorder and remove. */
function ImageAssetsEditor({
    value,
    onChange,
    errors,
}: {
    value: ImageAsset[];
    onChange: (rows: ImageAsset[]) => void;
    errors: Record<string, string | undefined>;
}) {
    const { t } = useTranslation();
    const update = (index: number, patch: Partial<ImageAsset>) =>
        onChange(value.map((row, i) => (i === index ? { ...row, ...patch } : row)));
    const move = (from: number, to: number) => {
        if (to < 0 || to >= value.length) return;
        const next = [...value];
        const [row] = next.splice(from, 1);
        next.splice(to, 0, row);
        onChange(next);
    };
    const listError =
        errors.image_assets ??
        Object.entries(errors).find(([key]) => key.startsWith("image_assets."))?.[1];

    return (
        <div className="flex flex-col gap-2">
            {value.map((row, index) => (
                <div
                    key={row.key || `new-${index}`}
                    className="grid gap-2 rounded-md border bg-muted/30 p-2 sm:grid-cols-[1fr_1fr_auto]"
                >
                    <Input
                        value={row.name}
                        onChange={(event) => update(index, { name: event.target.value })}
                        placeholder={t("Name, e.g. Front logo")}
                        maxLength={80}
                        aria-label={t("Asset name")}
                        aria-invalid={!!errors[`image_assets.${index}.name`]}
                    />
                    <Input
                        value={row.resolution}
                        onChange={(event) => update(index, { resolution: event.target.value })}
                        placeholder={t("Suggested resolution, e.g. 1000 × 1000 px")}
                        maxLength={120}
                        aria-label={t("Suggested resolution")}
                    />
                    <div className="flex items-center justify-between gap-1 sm:justify-end">
                        <label className="flex items-center gap-1.5 px-1 text-xs text-muted-foreground">
                            <Switch
                                checked={row.required}
                                onCheckedChange={(checked) => update(index, { required: checked })}
                            />
                            {t("Required")}
                        </label>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            aria-label={t("Move up")}
                            disabled={index === 0}
                            onClick={() => move(index, index - 1)}
                        >
                            <ArrowUp />
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            aria-label={t("Move down")}
                            disabled={index === value.length - 1}
                            onClick={() => move(index, index + 1)}
                        >
                            <ArrowDown />
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            aria-label={t("Remove")}
                            onClick={() => onChange(value.filter((_, i) => i !== index))}
                        >
                            <X />
                        </Button>
                    </div>
                </div>
            ))}
            {value.length < MAX_ASSETS && (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="self-start"
                    onClick={() => onChange([...value, { key: "", name: "", resolution: "", required: false }])}
                >
                    <Plus />
                    {t("Add image asset")}
                </Button>
            )}
            <FieldError message={listError} />
        </div>
    );
}

function StockDialog({
    design,
    onClose,
}: {
    design: Design | null;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm({
        id: "",
        mode: "add" as "add" | "remove" | "set",
        amount: "",
    });
    const amount = Number(form.data.amount) || 0;
    const result = !design
        ? 0
        : Math.max(
              0,
              form.data.mode === "add"
                  ? design.stock + amount
                  : form.data.mode === "remove"
                    ? design.stock - amount
                    : amount,
          );

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!design) return;
        form.transform((data) => ({ ...data, id: design.id }));
        form.post(route("admin.update.design.stock"), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    return (
        <Dialog
            open={design !== null}
            onOpenChange={(open) => {
                if (!open) {
                    form.reset();
                    onClose();
                }
            }}
        >
            <DialogContent className="sm:max-w-sm">
                <form onSubmit={submit} className="flex flex-col gap-5">
                    <DialogHeader>
                        <DialogTitle>{t("Adjust stock")}</DialogTitle>
                        <DialogDescription>
                            {design?.name} · {t("Currently")}{" "}
                            {design?.stock.toLocaleString()}
                        </DialogDescription>
                    </DialogHeader>
                    <Tabs
                        value={form.data.mode}
                        onValueChange={(mode) =>
                            form.setData("mode", mode as typeof form.data.mode)
                        }
                    >
                        <TabsList className="w-full">
                            <TabsTrigger value="add">{t("Add")}</TabsTrigger>
                            <TabsTrigger value="remove">
                                {t("Remove")}
                            </TabsTrigger>
                            <TabsTrigger value="set">{t("Set to")}</TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="stock_amount">
                            {form.data.mode === "set"
                                ? t("New stock")
                                : t("Number of items")}
                        </Label>
                        <Input
                            id="stock_amount"
                            type="number"
                            min={0}
                            value={form.data.amount}
                            onChange={(event) =>
                                form.setData("amount", event.target.value)
                            }
                            aria-invalid={!!form.errors.amount}
                            autoFocus
                            required
                        />
                        <FieldError message={form.errors.amount} />
                    </div>
                    <div className="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2 text-sm">
                        <span className="flex items-center gap-2 text-muted-foreground">
                            <Package className="size-4" />
                            {t("Stock after saving")}
                        </span>
                        <span className="font-semibold tabular-nums">
                            {result.toLocaleString()}
                        </span>
                    </div>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            {t("Cancel")}
                        </Button>
                        <Button
                            type="submit"
                            disabled={
                                form.processing || form.data.amount === ""
                            }
                        >
                            {form.processing && (
                                <Loader2 className="animate-spin" />
                            )}
                            {t("Update stock")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
