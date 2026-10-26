import { Head, router, useForm } from "@inertiajs/react";
import { Languages, Loader2, Plus, Search, Sparkles } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import type { Paginated } from "@admin/types";
import { cn } from "@admin/lib/utils";

import type { SyncState } from "./index";

type Row = { key: string; source: string; value: string };

type Props = {
    language: { code: string; name: string; isSource: boolean };
    source: { code: string; name: string };
    files: { value: string; label: string }[];
    file: string;
    filter: "all" | "untranslated";
    search: string;
    rows: Paginated<Row>;
    untranslated: number;
    total: number;
    sync: SyncState;
};

export default function TranslationEditor({ language, source, files, file, filter, search: initialSearch, rows, untranslated, total, sync }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(initialSearch);
    const [confirmSync, setConfirmSync] = useState(false);
    const [adding, setAdding] = useState(false);
    const [syncStarting, setSyncStarting] = useState(false);
    const form = useForm<{ file: string; translations: Record<string, string> }>({ file, translations: {} });
    const changed = Object.keys(form.data.translations).length;
    const busy = sync && ["queued", "running"].includes(sync.state);

    const visit = (query: Record<string, string | number | undefined>) => {
        if (changed && !window.confirm(t("You have unsaved changes. Leave without saving?"))) return;
        router.get(route("translation-manager.edit", language.code), { file, filter, search: search || undefined, ...query }, { preserveScroll: true, preserveState: false });
    };

    // Debounced search
    const first = useRef(true);
    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        const timer = window.setTimeout(() => visit({ search: search || undefined, page: undefined }), 400);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    // Refresh while a background translation runs
    useEffect(() => {
        if (!busy) return;
        const timer = window.setInterval(() => !changed && router.reload({ only: ["rows", "untranslated", "sync"] }), 5000);
        return () => window.clearInterval(timer);
    }, [busy, changed]);

    const setValue = (row: Row, value: string) =>
        form.setData("translations", (() => {
            const next = { ...form.data.translations };
            if (value === row.value) delete next[row.key];
            else next[row.key] = value;
            return next;
        })());

    const save = () =>
        form.post(route("translation-manager.update", language.code), {
            preserveScroll: true,
            onSuccess: () => form.setData("translations", {}),
        });

    const startSync = () =>
        router.post(route("translation-manager.sync-missing", language.code), {}, {
            preserveScroll: true,
            onStart: () => setSyncStarting(true),
            onFinish: () => {
                setSyncStarting(false);
                setConfirmSync(false);
            },
        });

    return (
        <>
            <Head title={t("Translate: :name").replace(":name", language.name)} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={language.isSource ? t(":name (source)").replace(":name", language.name) : language.name}
                    description={
                        language.isSource
                            ? t("The original text. Changes here show up wherever a language has no translation.")
                            : t(":count of :total texts still need translating.").replace(":count", untranslated.toLocaleString()).replace(":total", total.toLocaleString())
                    }
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Translations"), href: route("translation-manager.index") },
                        { label: language.name },
                    ]}
                    actions={
                        <div className="flex gap-2">
                            <Button variant="outline" onClick={() => setAdding(true)}>
                                <Plus />
                                {t("Add text")}
                            </Button>
                            {!language.isSource && (
                                <Button variant="outline" onClick={() => setConfirmSync(true)} disabled={!!busy || untranslated === 0}>
                                    {busy ? <Loader2 className="animate-spin" /> : <Sparkles />}
                                    {busy ? t("Translating…") : t("Auto-translate missing")}
                                </Button>
                            )}
                        </div>
                    }
                />

                {busy && (
                    <Alert>
                        <Loader2 className="animate-spin" />
                        <AlertDescription>{sync?.message || t("Translating missing text in the background…")}</AlertDescription>
                    </Alert>
                )}
                {sync?.state === "failed" && (
                    <Alert variant="destructive">
                        <AlertDescription>{sync.message || t("Auto-translate stopped with an error.")}</AlertDescription>
                    </Alert>
                )}

                <Card className="gap-0 py-0">
                    <div className="flex flex-col gap-3 border-b p-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex flex-wrap items-center gap-2">
                            {files.length > 1 && (
                                <Select value={file} onValueChange={(value) => visit({ file: value, page: undefined })}>
                                    <SelectTrigger className="w-56">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {files.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                            {!language.isSource && (
                                <Tabs value={filter} onValueChange={(value) => visit({ filter: value, page: undefined })}>
                                    <TabsList>
                                        <TabsTrigger value="all">{t("All")}</TabsTrigger>
                                        <TabsTrigger value="untranslated">
                                            {t("Untranslated")}
                                            <span className="text-muted-foreground tabular-nums">{untranslated}</span>
                                        </TabsTrigger>
                                    </TabsList>
                                </Tabs>
                            )}
                        </div>
                        <div className="relative lg:w-80">
                            <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder={t("Search text or translation")} className="ps-8" />
                        </div>
                    </div>

                    <CardContent className="p-0">
                        {rows.data.length === 0 ? (
                            <EmptyState
                                icon={Languages}
                                title={filter === "untranslated" && !search ? t("Everything is translated") : t("Nothing matches your search")}
                                className="border-0"
                            />
                        ) : (
                            <div className="divide-y">
                                <div className="hidden grid-cols-2 gap-4 bg-muted/40 px-4 py-2 text-xs font-medium text-muted-foreground md:grid">
                                    <span>{source.name}</span>
                                    <span>{language.isSource ? t("Text") : language.name}</span>
                                </div>
                                {rows.data.map((row) => {
                                    const value = form.data.translations[row.key] ?? row.value;
                                    const edited = row.key in form.data.translations;
                                    return (
                                        <div key={row.key} className={cn("grid gap-2 px-4 py-3 md:grid-cols-2 md:gap-4", edited && "bg-primary/5")}>
                                            <div className="flex min-w-0 flex-col gap-1">
                                                <p className="text-sm break-words whitespace-pre-wrap">{row.source}</p>
                                                {row.key !== row.source && <code className="truncate text-[11px] text-muted-foreground">{row.key}</code>}
                                            </div>
                                            <Textarea
                                                value={value}
                                                onChange={(event) => setValue(row, event.target.value)}
                                                rows={Math.min(6, Math.max(1, Math.ceil(row.source.length / 60)))}
                                                className={cn("min-h-9 resize-y text-sm", !value.trim() && "border-dashed")}
                                                placeholder={t("Not translated yet")}
                                                aria-label={row.source}
                                            />
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Pagination data={rows} />

                <div className="sticky bottom-0 -mx-4 mt-auto flex items-center justify-end gap-3 border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    {changed > 0 && <span className="text-sm text-muted-foreground">{t(":count unsaved").replace(":count", String(changed))}</span>}
                    {changed > 0 && (
                        <Button variant="secondary" onClick={() => form.setData("translations", {})}>
                            {t("Discard")}
                        </Button>
                    )}
                    <Button onClick={save} disabled={!changed || form.processing}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save changes")}
                    </Button>
                </div>
            </div>

            <ConfirmDialog
                open={confirmSync}
                onOpenChange={setConfirmSync}
                title={t("Auto-translate :count texts?").replace(":count", untranslated.toLocaleString())}
                description={t("Missing texts are machine-translated from :source in the background. It can take a while; review the results afterwards.").replace(":source", source.name)}
                confirmLabel={t("Start")}
                processing={syncStarting}
                onConfirm={startSync}
            />

            <Dialog open={adding} onOpenChange={setAdding}>
                <DialogContent className="sm:max-w-md">{adding && <AddText language={language} sourceName={source.name} onClose={() => setAdding(false)} />}</DialogContent>
            </Dialog>
        </>
    );
}

function AddText({ language, sourceName, onClose }: { language: Props["language"]; sourceName: string; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm({ locale: language.code, source_value: "", target_value: "" });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("translation-manager.add-key"), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{t("Add text")}</DialogTitle>
                <DialogDescription>{t("For text that appears on the site but isn't in the list yet. It's added to :source as well.").replace(":source", sourceName)}</DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="source_value">{t("Text in :source").replace(":source", sourceName)}</Label>
                <Textarea id="source_value" value={form.data.source_value} onChange={(event) => form.setData("source_value", event.target.value)} className="min-h-16" required />
                <FieldError message={form.errors.source_value} />
            </div>
            {!language.isSource && (
                <div className="flex flex-col gap-2">
                    <Label htmlFor="target_value">{t("Translation in :name").replace(":name", language.name)}</Label>
                    <Textarea
                        id="target_value"
                        value={form.data.target_value}
                        onChange={(event) => form.setData("target_value", event.target.value)}
                        placeholder={t("Leave blank to translate it automatically")}
                        className="min-h-16"
                    />
                    <FieldError message={form.errors.target_value} />
                </div>
            )}
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Add text")}
                </Button>
            </div>
        </form>
    );
}
