import { Head, router, useForm } from "@inertiajs/react";
import { Loader2, MoreHorizontal, Pencil, Plus, Search, Trash2, Users } from "lucide-react";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardAction, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { formatDate } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";

type Group = { id: string; name: string; description: string; emails: string[]; created: string | null };
type Customer = { name: string; email: string; hasPlan: boolean };

export default function Groups({ groups, customers, open }: { groups: Group[]; customers: Customer[]; open: string | null }) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<Group | "new" | null>(open === "new" ? "new" : (groups.find((g) => g.id === open) ?? null));
    const [deleting, setDeleting] = useState<Group | null>(null);
    const [processing, setProcessing] = useState(false);

    const remove = () =>
        deleting &&
        router.post(route("admin.marketing.groups.delete"), { id: deleting.id }, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });

    return (
        <>
            <Head title={t("Contact groups")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Contact groups")}
                    description={t("Lists of customers you can send email campaigns to.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Contact groups") }]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("New group")}
                        </Button>
                    }
                />
                {groups.length === 0 ? (
                    <EmptyState icon={Users} title={t("No contact groups yet")} />
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {groups.map((group) => (
                            <Card key={group.id} className="gap-2">
                                <CardHeader>
                                    <CardTitle>{group.name}</CardTitle>
                                    <CardDescription className="line-clamp-2">{group.description}</CardDescription>
                                    <CardAction>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}><MoreHorizontal /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem onSelect={() => setEditing(group)}><Pencil />{t("Edit")}</DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(group)}><Trash2 />{t("Delete")}</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </CardAction>
                                </CardHeader>
                                <div className="flex items-center justify-between px-6 text-sm">
                                    <span className="flex items-center gap-1.5 font-medium tabular-nums"><Users className="size-4 text-muted-foreground" />{group.emails.length} {t("contacts")}</span>
                                    <span className="text-xs text-muted-foreground">{formatDate(group.created)}</span>
                                </div>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
            <Dialog open={editing !== null} onOpenChange={(v) => !v && setEditing(null)}>
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-lg">
                    {editing && <GroupForm key={editing === "new" ? "new" : editing.id} group={editing === "new" ? null : editing} customers={customers} onClose={() => setEditing(null)} />}
                </DialogContent>
            </Dialog>
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(v) => !v && setDeleting(null)}
                title={t("Delete “:name”?").replace(":name", deleting?.name ?? "")}
                description={t("Campaigns already sent are not affected.")}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}

function GroupForm({ group, customers, onClose }: { group: Group | null; customers: Customer[]; onClose: () => void }) {
    const { t } = useTranslation();
    const [search, setSearch] = useState("");
    const [filter, setFilter] = useState<"all" | "plan" | "noplan" | "selected">("all");
    const form = useForm({ group_name: group?.name ?? "", group_desc: group?.description ?? "", emails: group?.emails ?? ([] as string[]) });
    const selected = useMemo(() => new Set(form.data.emails), [form.data.emails]);

    const visible = customers.filter((c) => {
        const q = search.toLowerCase();
        if (q && !c.name.toLowerCase().includes(q) && !c.email.toLowerCase().includes(q)) return false;
        if (filter === "plan") return c.hasPlan;
        if (filter === "noplan") return !c.hasPlan;
        if (filter === "selected") return selected.has(c.email);
        return true;
    });
    const allVisibleSelected = visible.length > 0 && visible.every((c) => selected.has(c.email));

    const toggle = (email: string) => form.setData("emails", selected.has(email) ? form.data.emails.filter((e) => e !== email) : [...form.data.emails, email]);
    const toggleVisible = () => {
        const emails = visible.map((c) => c.email);
        form.setData("emails", allVisibleSelected ? form.data.emails.filter((e) => !emails.includes(e)) : Array.from(new Set([...form.data.emails, ...emails])));
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(group ? route("admin.marketing.groups.update", group.id) : route("admin.marketing.groups.save"), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-4">
            <DialogHeader>
                <DialogTitle>{group ? t("Edit group") : t("New group")}</DialogTitle>
                <DialogDescription>{t("Pick the customers who should get campaigns sent to this group.")}</DialogDescription>
            </DialogHeader>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="group_name">{t("Name")}</Label>
                    <Input id="group_name" value={form.data.group_name} onChange={(e) => form.setData("group_name", e.target.value)} required />
                    <FieldError message={form.errors.group_name} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="group_desc">{t("Description")}</Label>
                    <Input id="group_desc" value={form.data.group_desc} onChange={(e) => form.setData("group_desc", e.target.value)} required />
                    <FieldError message={form.errors.group_desc} />
                </div>
            </div>
            <div className="flex flex-col gap-2">
                <div className="flex items-center justify-between">
                    <Label>{t("Contacts")}</Label>
                    <span className="text-xs text-muted-foreground tabular-nums">{form.data.emails.length} {t("selected")}</span>
                </div>
                <Tabs value={filter} onValueChange={(v) => setFilter(v as typeof filter)}>
                    <TabsList className="w-full">
                        <TabsTrigger value="all">{t("All")}</TabsTrigger>
                        <TabsTrigger value="plan">{t("With a plan")}</TabsTrigger>
                        <TabsTrigger value="noplan">{t("No plan")}</TabsTrigger>
                        <TabsTrigger value="selected">{t("Selected")}</TabsTrigger>
                    </TabsList>
                </Tabs>
                <div className="relative">
                    <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder={t("Search customers...")} className="ps-8" />
                </div>
                <div className="max-h-64 overflow-y-auto rounded-lg border">
                    <button type="button" onClick={toggleVisible} className="flex w-full items-center gap-3 border-b bg-muted/40 px-3 py-2 text-start text-sm font-medium">
                        <input type="checkbox" readOnly checked={allVisibleSelected} className="size-4 accent-[var(--primary)]" />
                        {allVisibleSelected ? t("Unselect all shown") : t("Select all shown")} ({visible.length})
                    </button>
                    {visible.map((c) => (
                        <button key={c.email} type="button" onClick={() => toggle(c.email)} className={cn("flex w-full items-center gap-3 px-3 py-2 text-start hover:bg-muted/50", selected.has(c.email) && "bg-primary/5")}>
                            <input type="checkbox" readOnly checked={selected.has(c.email)} className="size-4 accent-[var(--primary)]" />
                            <span className="flex min-w-0 flex-col">
                                <span className="truncate text-sm">{c.name}</span>
                                <span className="truncate text-xs text-muted-foreground">{c.email}</span>
                            </span>
                        </button>
                    ))}
                </div>
                <FieldError message={form.errors.emails} />
            </div>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>{t("Cancel")}</Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {group ? t("Save changes") : t("Create group")}
                </Button>
            </div>
        </form>
    );
}
