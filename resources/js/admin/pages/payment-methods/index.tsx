import { Head, router, useForm } from "@inertiajs/react";
import axios from "axios";
import { CheckCircle2, ExternalLink, Loader2, MoreHorizontal, Plug, Settings2, Trash2, Wallet, XCircle } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { CopyButton } from "@admin/components/copy-button";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardFooter } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Switch } from "@admin/components/ui/switch";
import { Textarea } from "@admin/components/ui/textarea";
import { cn } from "@admin/lib/utils";

type Field = {
    name: string;
    label: string;
    type: "text" | "secret" | "select" | "textarea";
    value: string;
    saved: boolean;
    options: { value: string; label: string }[] | null;
};

type Gateway = {
    id: string;
    name: string;
    displayName: string;
    logo: string | null;
    active: boolean;
    plugin: boolean;
    settingsUrl: string | null;
    fields: Field[];
    links: { label: string; url: string }[];
    configured: boolean | null;
    testable: boolean;
};

type Props = { gateways: Gateway[]; maxUploadKb: number; open: string | null };

export default function PaymentMethods({ gateways, maxUploadKb, open }: Props) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<Gateway | null>(gateways.find((gateway) => gateway.id === open) ?? null);
    const [deleting, setDeleting] = useState<Gateway | null>(null);
    const [busy, setBusy] = useState<string | null>(null);

    const setStatus = (gateway: Gateway, status: "activated" | "deactivated" | "deleted") =>
        router.post(
            route("admin.delete.payment.method"),
            { id: gateway.id, status },
            {
                preserveScroll: true,
                onStart: () => setBusy(gateway.id),
                onFinish: () => {
                    setBusy(null);
                    setDeleting(null);
                },
            },
        );

    const activeCount = gateways.filter((gateway) => gateway.active).length;

    return (
        <>
            <Head title={t("Payment gateways")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Payment gateways")}
                    description={t(":count of :total gateways are available at checkout.")
                        .replace(":count", String(activeCount))
                        .replace(":total", String(gateways.length))}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Payment gateways") }]}
                />

                {gateways.length === 0 ? (
                    <EmptyState icon={Wallet} title={t("No payment gateways")} description={t("Payment gateways appear here once installed.")} />
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        {gateways.map((gateway) => (
                            <Card key={gateway.id} className={cn("gap-0 py-0", !gateway.active && "bg-muted/30")}>
                                <CardContent className="flex flex-1 items-start gap-3 p-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white p-1.5">
                                        {gateway.logo ? (
                                            <img src={gateway.logo} alt="" className={cn("max-h-full max-w-full object-contain", !gateway.active && "opacity-60")} />
                                        ) : (
                                            <Wallet className="size-5 text-muted-foreground" />
                                        )}
                                    </div>
                                    <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                                        <span className="truncate font-medium" title={gateway.displayName}>
                                            {gateway.displayName}
                                        </span>
                                        <div className="flex flex-wrap gap-1">
                                            {gateway.plugin && (
                                                <Badge variant="outline">
                                                    <Plug />
                                                    {t("Plugin")}
                                                </Badge>
                                            )}
                                            {gateway.configured === true && (
                                                <Badge variant="secondary">
                                                    <CheckCircle2 className="text-success" />
                                                    {t("Set up")}
                                                </Badge>
                                            )}
                                            {gateway.configured === false && (
                                                <Badge variant="secondary">
                                                    <XCircle className="text-warning" />
                                                    {t("Needs setup")}
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                    <Switch
                                        checked={gateway.active}
                                        disabled={busy === gateway.id}
                                        onCheckedChange={(checked) => setStatus(gateway, checked ? "activated" : "deactivated")}
                                        aria-label={gateway.active ? t("Hide from checkout") : t("Show at checkout")}
                                        title={gateway.active ? t("Shown at checkout") : t("Hidden from checkout")}
                                    />
                                </CardContent>
                                <CardFooter className="flex gap-2 border-t p-3">
                                    <Button variant="outline" size="sm" className="flex-1" onClick={() => setEditing(gateway)}>
                                        <Settings2 />
                                        {t("Configure")}
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                                                <MoreHorizontal />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(gateway)}>
                                                <Trash2 />
                                                {t("Delete")}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            <Dialog open={editing !== null} onOpenChange={(isOpen) => !isOpen && setEditing(null)}>
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-lg">
                    {editing && <GatewayForm key={editing.id} gateway={editing} maxUploadKb={maxUploadKb} onClose={() => setEditing(null)} />}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(isOpen) => !isOpen && setDeleting(null)}
                title={t("Delete “:name”?").replace(":name", deleting?.displayName ?? "")}
                description={t("It's removed from this list and from checkout. Past payments are not affected.")}
                confirmLabel={t("Delete")}
                destructive
                processing={busy === deleting?.id}
                onConfirm={() => deleting && setStatus(deleting, "deleted")}
            />
        </>
    );
}

function GatewayForm({ gateway, maxUploadKb, onClose }: { gateway: Gateway; maxUploadKb: number; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm<Record<string, string | File | null>>({
        display_name: gateway.displayName,
        logo: null,
        ...Object.fromEntries(gateway.fields.map((field) => [field.name, field.value])),
    });
    const [testing, setTesting] = useState(false);
    const [result, setResult] = useState<{ success: boolean; message: string } | null>(null);
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.payment.method", gateway.id), { forceFormData: true, preserveScroll: true, onSuccess: onClose });
    };

    const test = () => {
        setTesting(true);
        setResult(null);
        const { logo: _logo, display_name: _name, ...credentials } = form.data;
        axios
            .post(route("admin.test.payment.connection", gateway.id), credentials)
            .then(({ data }) => setResult({ success: !!data.success, message: data.message ?? "" }))
            .catch(() => setResult({ success: false, message: t("Couldn't run the test. Please try again.") }))
            .finally(() => setTesting(false));
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{gateway.name}</DialogTitle>
                <DialogDescription>
                    {gateway.plugin
                        ? t("Change how this gateway appears at checkout. Its keys are managed in the plugin settings.")
                        : t("Change how this gateway appears at checkout and enter its API keys.")}
                </DialogDescription>
            </DialogHeader>

            <div className="grid gap-4 sm:grid-cols-[1fr_auto]">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="display_name">{t("Name at checkout")}</Label>
                    <Input
                        id="display_name"
                        value={String(form.data.display_name ?? "")}
                        onChange={(event) => form.setData("display_name", event.target.value)}
                        aria-invalid={!!errors.display_name}
                        required
                    />
                    <FieldError message={errors.display_name} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label>{t("Logo")}</Label>
                    <ImageInput
                        value={form.data.logo as File | null}
                        currentUrl={gateway.logo}
                        onChange={(file) => form.setData("logo", file)}
                        maxKb={maxUploadKb}
                        invalid={!!errors.logo}
                        fit="contain"
                        previewClassName="aspect-square size-16 bg-white"
                    />
                    <FieldError message={errors.logo} />
                </div>
            </div>

            {gateway.plugin && gateway.settingsUrl && (
                <Button variant="outline" asChild>
                    <a href={gateway.settingsUrl}>
                        <ExternalLink />
                        {t("Open plugin settings")}
                    </a>
                </Button>
            )}

            {gateway.fields.length > 0 && (
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    {gateway.fields.map((field) => (
                        <div key={field.name} className="flex flex-col gap-2">
                            <Label htmlFor={field.name}>{field.label}</Label>
                            {field.type === "select" ? (
                                <Select value={String(form.data[field.name] ?? "")} onValueChange={(value) => form.setData(field.name, value)}>
                                    <SelectTrigger id={field.name} className="w-full" aria-invalid={!!errors[field.name]}>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {field.options?.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            ) : field.type === "textarea" ? (
                                <Textarea
                                    id={field.name}
                                    value={String(form.data[field.name] ?? "")}
                                    onChange={(event) => form.setData(field.name, event.target.value)}
                                    placeholder={t("e.g., bank name, account name, account number, IFSC/SWIFT")}
                                    className="min-h-28"
                                    aria-invalid={!!errors[field.name]}
                                />
                            ) : (
                                <Input
                                    id={field.name}
                                    type={field.type === "secret" ? "password" : "text"}
                                    autoComplete="off"
                                    value={String(form.data[field.name] ?? "")}
                                    onChange={(event) => form.setData(field.name, event.target.value)}
                                    placeholder={field.type === "secret" && field.saved ? t("Saved. Leave blank to keep it.") : ""}
                                    aria-invalid={!!errors[field.name]}
                                    className="font-mono text-sm"
                                />
                            )}
                            <FieldError message={errors[field.name]} />
                        </div>
                    ))}

                    {gateway.links.map((link) => (
                        <div key={link.url} className="flex flex-col gap-2">
                            <Label>{link.label}</Label>
                            <div className="flex items-center gap-2">
                                <Input value={link.url} readOnly className="font-mono text-xs" />
                                <CopyButton value={link.url} />
                            </div>
                        </div>
                    ))}

                    {result && (
                        <p className={cn("flex items-start gap-2 text-sm", result.success ? "text-success" : "text-destructive")}>
                            {result.success ? <CheckCircle2 className="mt-0.5 size-4 shrink-0" /> : <XCircle className="mt-0.5 size-4 shrink-0" />}
                            {result.message}
                        </p>
                    )}
                </div>
            )}

            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                {gateway.testable ? (
                    <Button type="button" variant="outline" onClick={test} disabled={testing}>
                        {testing && <Loader2 className="animate-spin" />}
                        {t("Test connection")}
                    </Button>
                ) : (
                    <span />
                )}
                <div className="flex gap-2">
                    <Button type="button" variant="secondary" onClick={onClose}>
                        {t("Cancel")}
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save")}
                    </Button>
                </div>
            </div>
        </form>
    );
}
