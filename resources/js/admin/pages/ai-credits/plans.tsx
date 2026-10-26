import { Head, router, useForm, usePage } from "@inertiajs/react";
import { Loader2, MoreHorizontal, Pencil, Plus, Power, PowerOff, Sparkles, Trash2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
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
import { Textarea } from "@admin/components/ui/textarea";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

type Pack = {
    id: string;
    name: string;
    description: string | null;
    price: number;
    credits: number;
    inAppId: string | null;
    active: boolean;
    orders: number;
    revenue: number;
};

type Props = { plans: Pack[]; inApp: boolean; open: string | null };

export default function AiCreditPlans({ plans, inApp, open }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const [editing, setEditing] = useState<Pack | "new" | null>(open === "new" ? "new" : (plans.find((p) => p.id === open) ?? null));
    const [pending, setPending] = useState<{ pack: Pack; action: "activate" | "deactivate" | "delete" } | null>(null);
    const [processing, setProcessing] = useState(false);

    const run = () => {
        if (!pending) return;
        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        };
        if (pending.action === "delete") router.post(route("admin.delete.ai.credits.plan"), { id: pending.pack.id }, options);
        else router.post(route("admin.status.ai.credits.plan"), { id: pending.pack.id, action: pending.action }, options);
    };

    const copy = pending && {
        activate: { title: t("Put “:name” on sale?"), description: t("Customers can buy this credit pack again."), label: t("Put on sale") },
        deactivate: { title: t("Hide “:name”?"), description: t("Customers can no longer buy this credit pack. Credits already bought are kept."), label: t("Hide") },
        delete: { title: t("Delete “:name”?"), description: t("The pack is removed and can't be bought. Credits already bought are kept."), label: t("Delete") },
    }[pending.action];

    return (
        <>
            <Head title={t("AI credit packs")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("AI credit packs")}
                    description={t("Extra AI credits customers can buy on top of their plan.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("AI credit packs") }]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("New credit pack")}
                        </Button>
                    }
                />

                {plans.length === 0 ? (
                    <EmptyState icon={Sparkles} title={t("No credit packs yet")} />
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {plans.map((pack) => (
                            <Card key={pack.id} className={cn("gap-4", !pack.active && "bg-muted/30")}>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        {pack.name}
                                        {!pack.active && <Badge variant="outline" className="text-muted-foreground">{t("Hidden")}</Badge>}
                                    </CardTitle>
                                    {pack.description && <CardDescription className="line-clamp-2">{pack.description}</CardDescription>}
                                    <CardAction>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                    <MoreHorizontal />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem onSelect={() => setEditing(pack)}>
                                                    <Pencil />
                                                    {t("Edit")}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem onSelect={() => setPending({ pack, action: pack.active ? "deactivate" : "activate" })}>
                                                    {pack.active ? <PowerOff /> : <Power />}
                                                    {pack.active ? t("Hide from customers") : t("Put on sale")}
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem variant="destructive" onSelect={() => setPending({ pack, action: "delete" })}>
                                                    <Trash2 />
                                                    {t("Delete")}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </CardAction>
                                </CardHeader>
                                <CardContent className="flex items-end justify-between gap-2">
                                    <div className="flex flex-col">
                                        <span className="flex items-center gap-1.5 text-3xl font-semibold tabular-nums">
                                            <Sparkles className="size-5 text-primary" />
                                            {pack.credits.toLocaleString()}
                                        </span>
                                        <span className="text-xs text-muted-foreground">{t("AI credits")}</span>
                                    </div>
                                    <div className="flex flex-col items-end">
                                        <span className="text-xl font-semibold tabular-nums">{pack.price > 0 ? formatCurrency(pack.price, currency) : t("Free")}</span>
                                        {pack.price > 0 && pack.credits > 0 && (
                                            <span className="text-xs text-muted-foreground">
                                                {formatCurrency(pack.price / pack.credits, { ...currency, decimals: 3 })} {t("per credit")}
                                            </span>
                                        )}
                                    </div>
                                </CardContent>
                                <CardFooter className="justify-between border-t pt-4 text-sm [.border-t]:pt-4">
                                    <span className="text-muted-foreground">
                                        <span className="font-medium text-foreground tabular-nums">{pack.orders}</span> {t("sold")}
                                    </span>
                                    <span className="text-muted-foreground">
                                        <span className="font-medium text-foreground tabular-nums">{formatCurrency(pack.revenue, currency)}</span> {t("revenue")}
                                    </span>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            <Dialog open={editing !== null} onOpenChange={(value) => !value && setEditing(null)}>
                <DialogContent className="sm:max-w-md">
                    {editing && <PackForm key={editing === "new" ? "new" : editing.id} pack={editing === "new" ? null : editing} inApp={inApp} onClose={() => setEditing(null)} />}
                </DialogContent>
            </Dialog>
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(value) => !value && setPending(null)}
                title={copy?.title.replace(":name", pending?.pack.name ?? "") ?? ""}
                description={copy?.description}
                confirmLabel={copy?.label}
                destructive={pending?.action !== "activate"}
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function PackForm({ pack, inApp, onClose }: { pack: Pack | null; inApp: boolean; onClose: () => void }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const form = useForm({
        plan_name: pack?.name ?? "",
        plan_description: pack?.description ?? "",
        plan_price: String(pack?.price ?? ""),
        no_of_ai_credits: String(pack?.credits ?? ""),
        in_app_product_id: pack?.inAppId ?? "",
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(pack ? route("admin.update.ai.credits.plan", pack.id) : route("admin.save.ai.credits.plan"), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    const field = (key: keyof typeof form.data, label: string, input: React.ReactNode) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            {input}
            <FieldError message={form.errors[key]} />
        </div>
    );

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{pack ? t("Edit credit pack") : t("New credit pack")}</DialogTitle>
                <DialogDescription>{t("Customers buy the pack once and the credits are added to their balance.")}</DialogDescription>
            </DialogHeader>
            {field("plan_name", t("Name"), (
                <Input id="plan_name" value={form.data.plan_name} onChange={(e) => form.setData("plan_name", e.target.value)} placeholder={t("e.g., 500 credits")} required />
            ))}
            {field("plan_description", t("Description"), (
                <Textarea id="plan_description" value={form.data.plan_description} onChange={(e) => form.setData("plan_description", e.target.value)} className="min-h-20" required />
            ))}
            <div className="grid gap-4 sm:grid-cols-2">
                {field("no_of_ai_credits", t("Credits"), (
                    <Input id="no_of_ai_credits" type="number" min={1} value={form.data.no_of_ai_credits} onChange={(e) => form.setData("no_of_ai_credits", e.target.value)} required />
                ))}
                {field("plan_price", t("Price"), (
                    <div className="relative">
                        <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{currency.symbol}</span>
                        <Input id="plan_price" type="number" min={0} step="0.01" value={form.data.plan_price} onChange={(e) => form.setData("plan_price", e.target.value)} className="ps-8" required />
                    </div>
                ))}
            </div>
            {inApp && field("in_app_product_id", t("In-app product ID"), (
                <Input id="in_app_product_id" value={form.data.in_app_product_id} onChange={(e) => form.setData("in_app_product_id", e.target.value)} dir="ltr" />
            ))}
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>{t("Cancel")}</Button>
                <Button type="submit" disabled={form.processing || (!!pack && !form.isDirty)}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {pack ? t("Save changes") : t("Create pack")}
                </Button>
            </div>
        </form>
    );
}
