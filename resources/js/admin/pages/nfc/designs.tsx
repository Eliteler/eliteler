import { Head, router, useForm, usePage } from "@inertiajs/react";
import { Boxes, CreditCard, Loader2, MoreHorizontal, Package, Pencil, Plus, Power, PowerOff, RotateCw, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardFooter } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

type Design = {
    id: string;
    name: string;
    description: string | null;
    front: string | null;
    back: string | null;
    price: number;
    stock: number;
    active: boolean;
    orders: number;
    sold: number;
};

type Props = { designs: Design[]; lowStock: number; maxUploadKb: number; open: string | null };

type Pending = { design: Design; action: "activate" | "deactivate" | "delete" };

export default function NfcDesigns({ designs, lowStock, maxUploadKb, open }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [editing, setEditing] = useState<Design | "new" | null>(
        open === "new" ? "new" : (designs.find((design) => design.id === open) ?? null),
    );
    const [stockFor, setStockFor] = useState<Design | null>(null);
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);
    const [flipped, setFlipped] = useState<Record<string, boolean>>({});

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

    const confirmCopy = pending && {
        activate: {
            title: t("Make “:name” available?"),
            description: t("Customers can order this NFC card again."),
            label: t("Make available"),
        },
        deactivate: {
            title: t("Hide “:name”?"),
            description: t("Customers can no longer order this NFC card. Existing orders are not affected."),
            label: t("Hide"),
        },
        delete: {
            title: t("Delete “:name”?"),
            description: t("The NFC card is removed from this list and can't be ordered. Existing orders are not affected."),
            label: t("Delete"),
        },
    }[pending.action];

    return (
        <>
            <Head title={t("NFC cards")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("NFC cards")}
                    description={t("Physical NFC cards customers can order for their business cards.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("NFC cards") }]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add NFC card")}
                        </Button>
                    }
                />

                {designs.length === 0 ? (
                    <EmptyState
                        icon={CreditCard}
                        title={t("No NFC cards yet")}
                        description={t("Add a card design so customers can order it.")}
                        action={
                            <Button size="sm" onClick={() => setEditing("new")}>
                                <Plus />
                                {t("Add NFC card")}
                            </Button>
                        }
                    />
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {designs.map((design) => {
                            const showBack = flipped[design.id] && design.back;
                            const out = design.stock <= 0;
                            const low = !out && design.stock < lowStock;
                            return (
                                <Card key={design.id} className={cn("gap-0 overflow-hidden py-0", !design.active && "bg-muted/30")}>
                                    <button
                                        type="button"
                                        onClick={() => setFlipped((state) => ({ ...state, [design.id]: !state[design.id] }))}
                                        className="group relative aspect-[1.586] overflow-hidden bg-muted"
                                        title={t("Show the other side")}
                                    >
                                        {(showBack ? design.back : design.front) && (
                                            <img
                                                src={(showBack ? design.back : design.front) ?? ""}
                                                alt={design.name}
                                                className={cn("size-full object-cover transition-opacity", !design.active && "opacity-60")}
                                            />
                                        )}
                                        <span className="absolute end-2 bottom-2 flex items-center gap-1 rounded-full bg-background/90 px-2 py-1 text-[11px] font-medium opacity-0 shadow transition-opacity group-hover:opacity-100">
                                            <RotateCw className="size-3" />
                                            {showBack ? t("Front") : t("Back")}
                                        </span>
                                        <span className="absolute start-2 top-2 flex gap-1">
                                            {!design.active && (
                                                <Badge variant="secondary" className="bg-background/90">
                                                    {t("Hidden")}
                                                </Badge>
                                            )}
                                        </span>
                                    </button>
                                    <CardContent className="flex flex-1 flex-col gap-3 p-4">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="flex min-w-0 flex-col">
                                                <span className="truncate font-medium">{design.name}</span>
                                                <span className="text-lg font-semibold tabular-nums">
                                                    {design.price > 0 ? formatCurrency(design.price, currency) : t("Free")}
                                                </span>
                                            </div>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem onSelect={() => setEditing(design)}>
                                                        <Pencil />
                                                        {t("Edit")}
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onSelect={() => setStockFor(design)}>
                                                        <Boxes />
                                                        {t("Adjust stock")}
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem onSelect={() => setPending({ design, action: design.active ? "deactivate" : "activate" })}>
                                                        {design.active ? <PowerOff /> : <Power />}
                                                        {design.active ? t("Hide from customers") : t("Make available")}
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem variant="destructive" onSelect={() => setPending({ design, action: "delete" })}>
                                                        <Trash2 />
                                                        {t("Delete")}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                        {design.description && <p className="line-clamp-2 text-sm text-muted-foreground">{design.description}</p>}
                                    </CardContent>
                                    <CardFooter className="grid grid-cols-2 gap-2 border-t p-4 text-sm">
                                        <button
                                            type="button"
                                            onClick={() => setStockFor(design)}
                                            className="flex flex-col items-start rounded-md text-start hover:opacity-80"
                                        >
                                            <span className="text-xs text-muted-foreground">{t("In stock")}</span>
                                            <span
                                                className={cn(
                                                    "font-semibold tabular-nums",
                                                    out && "text-destructive",
                                                    low && "text-warning",
                                                )}
                                            >
                                                {design.stock.toLocaleString()}
                                                {out && <span className="ms-1.5 text-xs font-normal">{t("Sold out")}</span>}
                                                {low && <span className="ms-1.5 text-xs font-normal">{t("Running low")}</span>}
                                            </span>
                                        </button>
                                        <div className="flex flex-col">
                                            <span className="text-xs text-muted-foreground">{t("Sold")}</span>
                                            <span className="font-semibold tabular-nums">
                                                {design.sold.toLocaleString()}
                                                <span className="ms-1.5 text-xs font-normal text-muted-foreground">
                                                    {design.orders} {t("orders")}
                                                </span>
                                            </span>
                                        </div>
                                    </CardFooter>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </div>

            <DesignDialog design={editing} maxUploadKb={maxUploadKb} onClose={() => setEditing(null)} />
            <StockDialog design={stockFor} onClose={() => setStockFor(null)} />
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={confirmCopy?.title.replace(":name", pending?.design.name ?? "") ?? ""}
                description={confirmCopy?.description}
                confirmLabel={confirmCopy?.label}
                destructive={pending?.action !== "activate"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function DesignDialog({ design, maxUploadKb, onClose }: { design: Design | "new" | null; maxUploadKb: number; onClose: () => void }) {
    return (
        <Dialog open={design !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-xl">
                {design && <DesignForm key={design === "new" ? "new" : design.id} design={design === "new" ? null : design} maxUploadKb={maxUploadKb} onClose={onClose} />}
            </DialogContent>
        </Dialog>
    );
}

function DesignForm({ design, maxUploadKb, onClose }: { design: Design | null; maxUploadKb: number; onClose: () => void }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const form = useForm({
        nfc_card_id: design?.id ?? "",
        nfc_card_name: design?.name ?? "",
        nfc_card_description: design?.description ?? "",
        nfc_card_price: String(design?.price ?? ""),
        nfc_card_available_stocks: String(design?.stock ?? ""),
        nfc_card_front_image: null as File | null,
        nfc_card_back_image: null as File | null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(design ? route("admin.update.design") : route("admin.save.design"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{design ? t("Edit NFC card") : t("Add NFC card")}</DialogTitle>
                <DialogDescription>{t("Customers see the name, picture, price and description when they order.")}</DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="nfc_card_name">{t("Name")}</Label>
                <Input
                    id="nfc_card_name"
                    value={form.data.nfc_card_name}
                    onChange={(event) => form.setData("nfc_card_name", event.target.value)}
                    placeholder={t("e.g., Matte black NFC card")}
                    aria-invalid={!!form.errors.nfc_card_name}
                    required
                />
                <FieldError message={form.errors.nfc_card_name} />
            </div>
            <div className="flex flex-col gap-2">
                <Label htmlFor="nfc_card_description">{t("Description")}</Label>
                <Textarea
                    id="nfc_card_description"
                    value={form.data.nfc_card_description}
                    onChange={(event) => form.setData("nfc_card_description", event.target.value)}
                    aria-invalid={!!form.errors.nfc_card_description}
                    className="min-h-20"
                />
                <FieldError message={form.errors.nfc_card_description} />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="nfc_card_price">{t("Price")}</Label>
                    <div className="relative">
                        <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                            {currency.symbol}
                        </span>
                        <Input
                            id="nfc_card_price"
                            type="number"
                            min={0}
                            step="0.01"
                            value={form.data.nfc_card_price}
                            onChange={(event) => form.setData("nfc_card_price", event.target.value)}
                            aria-invalid={!!form.errors.nfc_card_price}
                            className="ps-8"
                            required
                        />
                    </div>
                    <FieldError message={form.errors.nfc_card_price} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="nfc_card_available_stocks">{t("Cards in stock")}</Label>
                    <Input
                        id="nfc_card_available_stocks"
                        type="number"
                        min={0}
                        value={form.data.nfc_card_available_stocks}
                        onChange={(event) => form.setData("nfc_card_available_stocks", event.target.value)}
                        aria-invalid={!!form.errors.nfc_card_available_stocks}
                        required
                    />
                    <FieldError message={form.errors.nfc_card_available_stocks} />
                </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                {(
                    [
                        ["nfc_card_front_image", t("Front side"), design?.front],
                        ["nfc_card_back_image", t("Back side"), design?.back],
                    ] as const
                ).map(([field, label, current]) => (
                    <div key={field} className="flex flex-col gap-2">
                        <Label>{label}</Label>
                        <ImageInput
                            value={form.data[field]}
                            currentUrl={current}
                            onChange={(file) => form.setData(field, file)}
                            maxKb={maxUploadKb}
                            invalid={!!form.errors[field]}
                            className="flex-col"
                            previewClassName="aspect-[1.586] w-full"
                        />
                        <FieldError message={form.errors[field]} />
                    </div>
                ))}
            </div>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing || (!!design && !form.isDirty)}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {design ? t("Save changes") : t("Add NFC card")}
                </Button>
            </div>
        </form>
    );
}

function StockDialog({ design, onClose }: { design: Design | null; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm({ id: "", mode: "add" as "add" | "remove" | "set", amount: "" });
    const amount = Number(form.data.amount) || 0;
    const result = !design
        ? 0
        : Math.max(0, form.data.mode === "add" ? design.stock + amount : form.data.mode === "remove" ? design.stock - amount : amount);

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
                            {design?.name} · {t("Currently")} {design?.stock.toLocaleString()}
                        </DialogDescription>
                    </DialogHeader>
                    <Tabs value={form.data.mode} onValueChange={(mode) => form.setData("mode", mode as typeof form.data.mode)}>
                        <TabsList className="w-full">
                            <TabsTrigger value="add">{t("Add")}</TabsTrigger>
                            <TabsTrigger value="remove">{t("Remove")}</TabsTrigger>
                            <TabsTrigger value="set">{t("Set to")}</TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="stock_amount">{form.data.mode === "set" ? t("New stock") : t("Number of cards")}</Label>
                        <Input
                            id="stock_amount"
                            type="number"
                            min={0}
                            value={form.data.amount}
                            onChange={(event) => form.setData("amount", event.target.value)}
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
                        <span className="font-semibold tabular-nums">{result.toLocaleString()}</span>
                    </div>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" onClick={onClose}>
                            {t("Cancel")}
                        </Button>
                        <Button type="submit" disabled={form.processing || form.data.amount === ""}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {t("Update stock")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
