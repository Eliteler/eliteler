import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { BarChart3, Dices, Loader2, MoreHorizontal, Pencil, Plus, Power, PowerOff, TicketPercent, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { CopyButton } from "@admin/components/copy-button";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ListToolbar } from "@admin/components/list-toolbar";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Progress } from "@admin/components/ui/progress";
import { Switch } from "@admin/components/ui/switch";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import { USED_FOR_LABELS, type Coupon, type ItemOption, type UsedFor } from "./types";

type Props = {
    coupons: Coupon[];
    tab: string;
    search: string;
    counts: Record<string, number>;
    items: Record<UsedFor, ItemOption[]>;
    open: string | null;
};

export function discountLabel(coupon: Pick<Coupon, "type" | "amount">, currency: Parameters<typeof formatCurrency>[1]) {
    return coupon.type === "percentage" ? `${coupon.amount}%` : formatCurrency(coupon.amount, currency);
}

export default function Coupons({ coupons, tab, search, counts, items, open }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [editing, setEditing] = useState<Coupon | "new" | null>(open === "new" ? "new" : (coupons.find((c) => c.id === open) ?? null));
    const [deleting, setDeleting] = useState<Coupon | null>(null);
    const [processing, setProcessing] = useState(false);

    const toggle = (coupon: Coupon) => router.post(route("admin.update.coupon.status"), { id: coupon.id }, { preserveScroll: true });
    const remove = () => {
        if (!deleting) return;
        router.post(route("admin.delete.coupon"), { id: deleting.id }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    return (
        <>
            <Head title={t("Coupons")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Coupons")}
                    description={t("Discount codes customers can enter at checkout.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Coupons") }]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("New coupon")}
                        </Button>
                    }
                />
                <ListToolbar
                    routeName="admin.coupons"
                    tabs={[
                        { value: "active", label: t("Active") },
                        { value: "expired", label: t("Expired") },
                        { value: "disabled", label: t("Disabled") },
                        { value: "all", label: t("All") },
                    ]}
                    tab={tab}
                    counts={counts}
                    search={search}
                    placeholder={t("Search code or description...")}
                />
                {coupons.length === 0 ? (
                    <EmptyState icon={TicketPercent} title={t("No coupons found")} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Code")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Applies to")}</TableHead>
                                    <TableHead>{t("Discount")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Used")}</TableHead>
                                    <TableHead className="hidden sm:table-cell">{t("Expires")}</TableHead>
                                    <TableHead className="w-24 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {coupons.map((coupon) => {
                                    const used = coupon.redeemed ?? 0;
                                    return (
                                        <TableRow key={coupon.id} className={cn(!coupon.active && "opacity-60")}>
                                            <TableCell className="max-w-[240px] ps-4">
                                                <div className="flex items-center gap-1">
                                                    <span className="rounded border border-dashed px-1.5 py-0.5 font-mono text-sm font-semibold">{coupon.code}</span>
                                                    <CopyButton value={coupon.code} label={t("Copy code")} />
                                                </div>
                                                {coupon.description && <span className="block truncate text-xs text-muted-foreground">{coupon.description}</span>}
                                            </TableCell>
                                            <TableCell className="hidden max-w-[220px] md:table-cell">
                                                <span className="text-sm">{t(USED_FOR_LABELS[coupon.usedFor])}</span>
                                                <span className="block truncate text-xs text-muted-foreground">{coupon.items ? coupon.items.join(", ") : t("All")}</span>
                                            </TableCell>
                                            <TableCell className="font-medium tabular-nums">{discountLabel(coupon, currency)}</TableCell>
                                            <TableCell className="hidden w-44 lg:table-cell">
                                                <div className="flex flex-col gap-1">
                                                    <span className="text-xs text-muted-foreground tabular-nums">
                                                        {used} / {coupon.total} · {coupon.customers ?? 0} {t("customers")}
                                                    </span>
                                                    <Progress value={Math.min(100, (used / Math.max(1, coupon.total)) * 100)} className="h-1.5" />
                                                </div>
                                            </TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                {coupon.expired ? (
                                                    <Badge variant="outline" className="border-destructive/30 text-destructive">{t("Expired")}</Badge>
                                                ) : (
                                                    <span className="text-sm">{formatDate(coupon.expires)}</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="pe-4 text-end">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Switch checked={coupon.active} onCheckedChange={() => toggle(coupon)} aria-label={t("Enabled")} />
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                                <MoreHorizontal />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem onSelect={() => setEditing(coupon)}>
                                                                <Pencil />
                                                                {t("Edit")}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={route("admin.statistics.coupon", coupon.id)}>
                                                                    <BarChart3 />
                                                                    {t("See who used it")}
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem onSelect={() => toggle(coupon)}>
                                                                {coupon.active ? <PowerOff /> : <Power />}
                                                                {coupon.active ? t("Disable") : t("Enable")}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(coupon)}>
                                                                <Trash2 />
                                                                {t("Delete")}
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </Card>
                )}
            </div>

            <Dialog open={editing !== null} onOpenChange={(value) => !value && setEditing(null)}>
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-lg">
                    {editing && (
                        <CouponForm key={editing === "new" ? "new" : editing.id} coupon={editing === "new" ? null : editing} items={items} onClose={() => setEditing(null)} />
                    )}
                </DialogContent>
            </Dialog>
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(value) => !value && setDeleting(null)}
                title={t("Delete coupon :code?").replace(":code", deleting?.code ?? "")}
                description={t("Customers can no longer use this code. Past orders keep their discount.")}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}

function CouponForm({ coupon, items, onClose }: { coupon: Coupon | null; items: Record<UsedFor, ItemOption[]>; onClose: () => void }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const tomorrowPlus30 = new Date(Date.now() + 30 * 864e5).toISOString().slice(0, 10);
    const form = useForm({
        used_for: (coupon?.usedFor ?? "plan") as UsedFor,
        plan_id: coupon?.itemIds ?? ["all"],
        code: coupon?.code ?? "",
        description: coupon?.description ?? "",
        type: coupon?.type ?? "percentage",
        discount: String(coupon?.amount ?? ""),
        validity: coupon?.expires?.slice(0, 10) ?? tomorrowPlus30,
        user_limit: String(coupon?.perCustomer ?? 1),
        total_limit: String(coupon?.total ?? 100),
    });

    const all = form.data.plan_id.includes("all");
    const toggleItem = (id: string) => {
        const current = form.data.plan_id.filter((value) => value !== "all");
        const next = current.includes(id) ? current.filter((value) => value !== id) : [...current, id];
        form.setData("plan_id", next.length ? next : ["all"]);
    };
    const randomCode = () => form.setData("code", Array.from({ length: 8 }, () => "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"[Math.floor(Math.random() * 32)]).join(""));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(coupon ? route("admin.update.coupon", coupon.id) : route("admin.store.coupon"), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{coupon ? t("Edit coupon") : t("New coupon")}</DialogTitle>
                <DialogDescription>{t("Customers enter the code at checkout to get the discount.")}</DialogDescription>
            </DialogHeader>

            <div className="flex flex-col gap-2">
                <Label>{t("Use it for")}</Label>
                <Tabs value={form.data.used_for} onValueChange={(value) => form.setData((d) => ({ ...d, used_for: value as UsedFor, plan_id: ["all"] }))}>
                    <TabsList className="w-full">
                        {(Object.keys(USED_FOR_LABELS) as UsedFor[]).map((key) => (
                            <TabsTrigger key={key} value={key}>{t(USED_FOR_LABELS[key])}</TabsTrigger>
                        ))}
                    </TabsList>
                </Tabs>
                <div className="flex flex-wrap gap-1.5">
                    <button type="button" onClick={() => form.setData("plan_id", ["all"])} className={cn("rounded-full border px-3 py-1 text-xs", all && "border-primary bg-primary/10 text-primary")}>
                        {t("All")}
                    </button>
                    {items[form.data.used_for].map((item) => (
                        <button
                            key={item.id}
                            type="button"
                            onClick={() => toggleItem(item.id)}
                            className={cn("rounded-full border px-3 py-1 text-xs", !all && form.data.plan_id.includes(item.id) && "border-primary bg-primary/10 text-primary")}
                        >
                            {item.name} · {item.price > 0 ? formatCurrency(item.price, currency) : t("Free")}
                        </button>
                    ))}
                </div>
                <FieldError message={form.errors.plan_id} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="code">{t("Code")}</Label>
                    <div className="flex gap-1">
                        <Input id="code" value={form.data.code} onChange={(e) => form.setData("code", e.target.value.toUpperCase())} placeholder="SUMMER25" className="font-mono uppercase" maxLength={50} required />
                        <Button type="button" variant="outline" size="icon" onClick={randomCode} aria-label={t("Generate a code")} title={t("Generate a code")}>
                            <Dices />
                        </Button>
                    </div>
                    <FieldError message={form.errors.code} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="validity">{t("Expires on")}</Label>
                    <Input id="validity" type="date" className="relative [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:end-3" value={form.data.validity} onChange={(e) => form.setData("validity", e.target.value)} required />
                    <FieldError message={form.errors.validity} />
                </div>
            </div>
            <div className="flex flex-col gap-2">
                <Label htmlFor="description">{t("Description")}</Label>
                <Input id="description" value={form.data.description} onChange={(e) => form.setData("description", e.target.value)} placeholder={t("Shown only to admins, e.g. Summer sale")} />
                <FieldError message={form.errors.description} />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label>{t("Discount type")}</Label>
                    <Tabs value={form.data.type} onValueChange={(value) => form.setData("type", value as "percentage" | "fixed")}>
                        <TabsList className="w-full">
                            <TabsTrigger value="percentage">{t("Percentage")}</TabsTrigger>
                            <TabsTrigger value="fixed">{t("Fixed amount")}</TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="discount">{t("Discount")}</Label>
                    <div className="relative">
                        <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{form.data.type === "percentage" ? "%" : currency.symbol}</span>
                        <Input id="discount" type="number" min={0} step="0.01" max={form.data.type === "percentage" ? 100 : undefined} value={form.data.discount} onChange={(e) => form.setData("discount", e.target.value)} className="ps-8" required />
                    </div>
                    <FieldError message={form.errors.discount} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="user_limit">{t("Uses per customer")}</Label>
                    <Input id="user_limit" type="number" min={1} value={form.data.user_limit} onChange={(e) => form.setData("user_limit", e.target.value)} required />
                    <FieldError message={form.errors.user_limit} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="total_limit">{t("Total uses")}</Label>
                    <Input id="total_limit" type="number" min={1} value={form.data.total_limit} onChange={(e) => form.setData("total_limit", e.target.value)} required />
                    <FieldError message={form.errors.total_limit} />
                </div>
            </div>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>{t("Cancel")}</Button>
                <Button type="submit" disabled={form.processing || (!!coupon && !form.isDirty)}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {coupon ? t("Save changes") : t("Create coupon")}
                </Button>
            </div>
        </form>
    );
}
