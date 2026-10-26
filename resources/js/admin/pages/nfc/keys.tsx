import { Head, Link, router, useForm } from "@inertiajs/react";
import axios from "axios";
import { KeyRound, Link2, Link2Off, Loader2, Mail, MoreHorizontal, Plus, Trash2 } from "lucide-react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { CopyButton } from "@admin/components/copy-button";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { formatDate } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";
import type { Paginated } from "@admin/types";

import { CustomerPicker, type PickedCustomer } from "../customers/components/customer-picker";
import { ListToolbar } from "@admin/components/list-toolbar";

type KeyRow = {
    id: string;
    key: string;
    url: string;
    source: "order" | "manual";
    orderId: string | null;
    linked: boolean;
    card: { title: string; url: string } | null;
    owner: { id: string; name: string } | null;
    plan: string | null;
    design: string | null;
    voucher: "unused" | "redeemed" | null;
    created: string | null;
};

type Option = { id: string; name: string };

type Props = {
    keys: Paginated<KeyRow>;
    filters: { tab: string; type: string; search: string };
    counts: Record<string, number>;
    plans: Option[];
    designs: Option[];
    open: string | null;
};

export default function NfcKeys({ keys, filters, counts, plans, designs, open }: Props) {
    const { t } = useTranslation();
    const [generateOpen, setGenerateOpen] = useState(open === "generate");
    const [linking, setLinking] = useState<KeyRow | null>(
        open?.startsWith("link:") ? (keys.data.find((row) => row.id === open.slice(5)) ?? null) : null,
    );
    const [pending, setPending] = useState<{ row: KeyRow; action: "unlink" | "delete" } | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        router.post(
            route(pending.action === "unlink" ? "admin.action.key.generation" : "admin.delete.key"),
            { id: pending.row.id },
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

    const setType = (type: string) =>
        router.get(
            route("admin.key.generations"),
            {
                ...(filters.tab !== "all" ? { tab: filters.tab } : {}),
                ...(type !== "all" ? { type } : {}),
                ...(filters.search ? { search: filters.search } : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );

    return (
        <>
            <Head title={t("Activation keys")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Activation keys")}
                    description={t("Each NFC card carries one key. Link a key to a business card to choose what opens when the card is tapped.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Activation keys") }]}
                    actions={
                        <Button onClick={() => setGenerateOpen(true)}>
                            <Plus />
                            {t("Generate keys")}
                        </Button>
                    }
                />

                <ListToolbar
                    routeName="admin.key.generations"
                    tabs={[
                        { value: "all", label: t("All") },
                        { value: "linked", label: t("Linked") },
                        { value: "unlinked", label: t("Not linked") },
                        { value: "vouchers", label: t("With a plan") },
                    ]}
                    tab={filters.tab}
                    counts={counts}
                    search={filters.search}
                    extraQuery={filters.type !== "all" ? { type: filters.type } : {}}
                    placeholder={t("Search key, card or customer...")}
                >
                    <Select value={filters.type} onValueChange={setType}>
                        <SelectTrigger className="w-40">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent align="end">
                            <SelectItem value="all">{t("All sources")}</SelectItem>
                            <SelectItem value="order">{t("From orders")}</SelectItem>
                            <SelectItem value="manual">{t("Generated by admin")}</SelectItem>
                        </SelectContent>
                    </Select>
                </ListToolbar>

                {keys.data.length === 0 ? (
                    <EmptyState icon={KeyRound} title={t("No keys found")} description={filters.search ? t("Try a different search.") : undefined} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Key")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Opens")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Source")}</TableHead>
                                    <TableHead className="hidden xl:table-cell">{t("Created")}</TableHead>
                                    <TableHead className="w-24 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {keys.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="ps-4">
                                            <div className="flex items-center gap-1">
                                                <span className="font-mono text-sm font-medium select-all">{row.key}</span>
                                                <CopyButton value={row.key} label={t("Copy key")} />
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[260px] md:table-cell">
                                            {row.card ? (
                                                <div className="flex flex-col">
                                                    <a href={row.card.url} target="_blank" rel="noreferrer" className="flex items-center gap-1.5 truncate text-sm hover:underline">
                                                        <Link2 className="size-3.5 shrink-0 text-success" />
                                                        {row.card.title}
                                                    </a>
                                                    {row.owner && (
                                                        <Link href={route("admin.view.customer", row.owner.id)} className="truncate text-xs text-muted-foreground hover:underline">
                                                            {row.owner.name}
                                                        </Link>
                                                    )}
                                                </div>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Not linked yet")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden lg:table-cell">
                                            <div className="flex flex-col items-start gap-1">
                                                {row.source === "order" ? (
                                                    row.orderId ? (
                                                        <Link href={route("admin.order.show", row.orderId)} className="font-mono text-xs hover:underline">
                                                            {row.orderId}
                                                        </Link>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">{t("Order")}</span>
                                                    )
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">{t("Generated by admin")}</span>
                                                )}
                                                {row.plan && (
                                                    <Badge
                                                        variant="outline"
                                                        className={cn(row.voucher === "unused" ? "border-primary/30 text-primary" : "text-muted-foreground")}
                                                        title={row.design ?? undefined}
                                                    >
                                                        {row.plan}
                                                        {" · "}
                                                        {row.voucher === "unused" ? t("Not redeemed") : t("Redeemed")}
                                                    </Badge>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden text-sm text-muted-foreground xl:table-cell">{formatDate(row.created)}</TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <div className="flex justify-end gap-1">
                                                <CopyButton value={row.url} label={t("Copy NFC link")} />
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                            <MoreHorizontal />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" className="w-52">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route("admin.key.greeting.letter", row.key)}>
                                                                <Mail />
                                                                {t("Greeting letter")}
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onSelect={() => setLinking(row)}>
                                                            <Link2 />
                                                            {row.linked ? t("Link to another card") : t("Link to a business card")}
                                                        </DropdownMenuItem>
                                                        {row.linked && (
                                                            <DropdownMenuItem onSelect={() => setPending({ row, action: "unlink" })}>
                                                                <Link2Off />
                                                                {t("Unlink")}
                                                            </DropdownMenuItem>
                                                        )}
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            disabled={row.linked}
                                                            onSelect={() => setPending({ row, action: "delete" })}
                                                        >
                                                            <Trash2 />
                                                            {row.linked ? t("Unlink before deleting") : t("Delete key")}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                <Pagination data={keys} />
            </div>

            <GenerateDialog open={generateOpen} onOpenChange={setGenerateOpen} plans={plans} designs={designs} />
            <LinkDialog row={linking} onClose={() => setLinking(null)} />
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={pending?.action === "unlink" ? t("Unlink this key?") : t("Delete this key?")}
                description={
                    pending?.action === "unlink"
                        ? t("Tapping the NFC card will no longer open “:card”.").replace(":card", pending.row.card?.title ?? "")
                        : t("NFC cards carrying this key will stop working. This cannot be undone.")
                }
                confirmLabel={pending?.action === "unlink" ? t("Unlink") : t("Delete key")}
                destructive
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function GenerateDialog({
    open,
    onOpenChange,
    plans,
    designs,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    plans: Option[];
    designs: Option[];
}) {
    const { t } = useTranslation();
    const form = useForm({ nfc_card_key_generations: "10", associated_plan_id: "none", associated_nfc_card_id: "none" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            associated_plan_id: data.associated_plan_id === "none" ? "" : data.associated_plan_id,
            associated_nfc_card_id: data.associated_nfc_card_id === "none" ? "" : data.associated_nfc_card_id,
        }));
        form.post(route("admin.save.key.generation"), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onOpenChange(false);
            },
        });
    };

    const bundle = form.data.associated_plan_id !== "none" || form.data.associated_nfc_card_id !== "none";

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <form onSubmit={submit} className="flex flex-col gap-5">
                    <DialogHeader>
                        <DialogTitle>{t("Generate keys")}</DialogTitle>
                        <DialogDescription>{t("Create keys for NFC cards you sell or give away outside the online store.")}</DialogDescription>
                    </DialogHeader>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="key_count">{t("Number of keys")}</Label>
                        <Input
                            id="key_count"
                            type="number"
                            min={1}
                            max={999}
                            value={form.data.nfc_card_key_generations}
                            onChange={(event) => form.setData("nfc_card_key_generations", event.target.value)}
                            aria-invalid={!!form.errors.nfc_card_key_generations}
                            required
                        />
                        <FieldError message={form.errors.nfc_card_key_generations} />
                    </div>
                    <div className="flex flex-col gap-3 rounded-lg border p-3">
                        <div className="flex flex-col gap-0.5">
                            <span className="text-sm font-medium">{t("Include a plan (optional)")}</span>
                            <span className="text-xs text-muted-foreground">
                                {t("Customers who sign up with one of these keys get the plan for free. Choose both a plan and an NFC card.")}
                            </span>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="flex flex-col gap-2">
                                <Label>{t("Plan")}</Label>
                                <Select value={form.data.associated_plan_id} onValueChange={(value) => form.setData("associated_plan_id", value)}>
                                    <SelectTrigger className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t("No plan")}</SelectItem>
                                        {plans.map((plan) => (
                                            <SelectItem key={plan.id} value={plan.id}>
                                                {plan.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.associated_plan_id} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label>{t("NFC card")}</Label>
                                <Select value={form.data.associated_nfc_card_id} onValueChange={(value) => form.setData("associated_nfc_card_id", value)}>
                                    <SelectTrigger className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t("No NFC card")}</SelectItem>
                                        {designs.map((design) => (
                                            <SelectItem key={design.id} value={design.id}>
                                                {design.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.associated_nfc_card_id} />
                            </div>
                        </div>
                        {bundle && (form.data.associated_plan_id === "none" || form.data.associated_nfc_card_id === "none") && (
                            <p className="text-xs text-warning">{t("Choose both a plan and an NFC card, or neither.")}</p>
                        )}
                    </div>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            {t("Cancel")}
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {t("Generate")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

type CustomerCard = { id: string; title: string; url: string; type: string };

function LinkDialog({ row, onClose }: { row: KeyRow | null; onClose: () => void }) {
    const { t } = useTranslation();
    const [customer, setCustomer] = useState<PickedCustomer | null>(null);
    const [cards, setCards] = useState<CustomerCard[] | null>(null);
    const form = useForm({ key_id: "", card_id: "" });

    useEffect(() => {
        if (!customer) return;
        setCards(null);
        form.setData("card_id", "");
        axios
            .post<CustomerCard[]>(route("admin.get.customer.cards"), { customerId: customer.id })
            .then(({ data }) => setCards(data))
            .catch(() => setCards([]));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [customer]);

    const close = () => {
        setCustomer(null);
        setCards(null);
        form.reset();
        onClose();
    };

    const submit = () => {
        if (!row) return;
        form.transform((data) => ({ ...data, key_id: row.id }));
        form.post(route("admin.update.link.key"), { preserveScroll: true, onSuccess: close });
    };

    return (
        <Dialog open={row !== null} onOpenChange={(value) => !value && close()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{t("Link key to a business card")}</DialogTitle>
                    <DialogDescription>
                        <span className="font-mono">{row?.key}</span> · {t("Tapping the NFC card will open the card you choose.")}
                    </DialogDescription>
                </DialogHeader>
                {!customer ? (
                    <div className="flex flex-col gap-2">
                        <Label>{t("1. Choose the customer")}</Label>
                        <CustomerPicker value={customer} onChange={setCustomer} />
                    </div>
                ) : (
                    <div className="flex flex-col gap-3">
                        <div className="flex items-center justify-between rounded-lg border px-3 py-2 text-sm">
                            <span className="flex flex-col">
                                <span className="font-medium">{customer.name}</span>
                                <span className="text-xs text-muted-foreground">{customer.email}</span>
                            </span>
                            <Button variant="secondary" size="sm" onClick={() => setCustomer(null)}>
                                {t("Change")}
                            </Button>
                        </div>
                        <Label>{t("2. Choose the business card")}</Label>
                        {cards === null ? (
                            <div className="flex justify-center py-6">
                                <Loader2 className="size-4 animate-spin text-muted-foreground" />
                            </div>
                        ) : cards.length === 0 ? (
                            <p className="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground">
                                {t("This customer has no active business cards or stores.")}
                            </p>
                        ) : (
                            <div className="flex max-h-60 flex-col gap-1.5 overflow-y-auto">
                                {cards.map((card) => (
                                    <button
                                        key={card.id}
                                        type="button"
                                        onClick={() => form.setData("card_id", card.id)}
                                        className={cn(
                                            "flex flex-col rounded-lg border px-3 py-2 text-start transition-colors hover:bg-muted/50",
                                            form.data.card_id === card.id && "border-primary bg-primary/5 ring-1 ring-primary",
                                        )}
                                    >
                                        <span className="text-sm font-medium">{card.title}</span>
                                        <span className="truncate text-xs text-muted-foreground" dir="ltr">
                                            {card.url.replace(/^https?:\/\//, "")}
                                        </span>
                                    </button>
                                ))}
                            </div>
                        )}
                        <FieldError message={form.errors.card_id} />
                    </div>
                )}
                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button variant="outline" onClick={close}>
                        {t("Cancel")}
                    </Button>
                    <Button onClick={submit} disabled={!form.data.card_id || form.processing}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Link")}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
