import { router, useForm } from "@inertiajs/react";
import { Link2, Loader2, MoreHorizontal, Pencil, Plus, Trash2 } from "lucide-react";
import { useRef, useState, type FormEvent } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { Pagination } from "@admin/components/pagination";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import type { Paginated } from "@admin/types";

type Exclusion = {
    id: number;
    keyword: string;
    is_default: boolean;
};

type KeywordTab = "default" | "admin";

export type PersonalizedLinkSettingsProps = {
    exclusions: Paginated<Exclusion>;
    search: string;
    tab: KeywordTab;
};

const listProps = ["personalizedLinks"];

export function PersonalizedLinkSettings({ exclusions, search, tab }: PersonalizedLinkSettingsProps) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<Exclusion | "new" | null>(null);
    const [deleting, setDeleting] = useState<Exclusion | null>(null);
    const [processingDelete, setProcessingDelete] = useState(false);
    const searchInput = useRef<HTMLInputElement>(null);

    function filterKeywords(nextTab: KeywordTab, query?: string) {
        router.get(
            route("admin.settings") + "#personalized-link-settings",
            { keyword_tab: nextTab === "admin" ? nextTab : undefined, keyword_search: query || undefined },
            { only: listProps, preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function searchKeywords() {
        filterKeywords(tab, searchInput.current?.value.trim());
    }

    function deleteKeyword() {
        if (!deleting || processingDelete) return;

        router.delete(route("admin.personalized-link-settings.destroy", deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessingDelete(true),
            onSuccess: () => setDeleting(null),
            onFinish: () => setProcessingDelete(false),
        });
    }

    return (
        <>
            <Card id="personalized-link-settings" className="scroll-mt-6 gap-4">
                <CardHeader className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>{t("Personalized Link Settings")}</CardTitle>
                        <CardDescription>{t("Reserve keywords for business card and store links.")}</CardDescription>
                    </div>
                    <Button type="button" onClick={() => setEditing("new")}>
                        <Plus />
                        {t("Add keyword")}
                    </Button>
                </CardHeader>

                <Tabs
                    value={tab}
                    onValueChange={(value) => filterKeywords(value === "admin" ? "admin" : "default")}
                    className="min-w-0 gap-4"
                >
                    <div className="flex flex-col gap-3 px-6 lg:flex-row lg:items-center lg:justify-between">
                        <TabsList aria-label={t("Personalized Link Settings")}>
                            <TabsTrigger value="default">{t("System")}</TabsTrigger>
                            <TabsTrigger value="admin">{t("Custom")}</TabsTrigger>
                        </TabsList>
                        <div role="search" className="flex w-full gap-2 sm:max-w-sm">
                            <Input
                                key={tab + search}
                                ref={searchInput}
                                type="search"
                                defaultValue={search}
                                placeholder={t("Search keywords")}
                                aria-label={t("Search keywords")}
                                className="min-w-0 flex-1"
                                onKeyDown={(event) => {
                                    if (event.key === "Enter" && !event.nativeEvent.isComposing) {
                                        event.preventDefault();
                                        searchKeywords();
                                    }
                                }}
                            />
                            <Button type="button" variant="outline" onClick={searchKeywords}>
                                {t("Search")}
                            </Button>
                        </div>
                    </div>

                    <TabsContent value={tab} className="flex min-w-0 flex-col gap-4">
                        <CardContent className="p-0">
                            {exclusions.data.length ? (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="ps-6">{t("Keyword")}</TableHead>
                                            <TableHead className="pe-6 text-end">
                                                <span className="sr-only">{t("Actions")}</span>
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {exclusions.data.map((exclusion) => (
                                            <TableRow key={exclusion.id}>
                                                <TableCell className="whitespace-normal break-all ps-6 font-medium">
                                                    {exclusion.is_default ? (
                                                        exclusion.keyword
                                                    ) : (
                                                        <button
                                                            type="button"
                                                            className="text-start hover:underline"
                                                            onClick={() => setEditing(exclusion)}
                                                        >
                                                            {exclusion.keyword}
                                                        </button>
                                                    )}
                                                </TableCell>
                                                <TableCell className="pe-6 text-end">
                                                    {exclusion.is_default ? (
                                                        <span className="text-muted-foreground">-</span>
                                                    ) : (
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    size="icon-sm"
                                                                    aria-label={t("Actions")}
                                                                >
                                                                    <MoreHorizontal aria-hidden="true" />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                <DropdownMenuItem
                                                                    onSelect={() => setEditing(exclusion)}
                                                                >
                                                                    <Pencil aria-hidden="true" />
                                                                    {t("Edit")}
                                                                </DropdownMenuItem>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    variant="destructive"
                                                                    onSelect={() => setDeleting(exclusion)}
                                                                >
                                                                    <Trash2 aria-hidden="true" />
                                                                    {t("Delete")}
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            ) : (
                                <EmptyState icon={Link2} title={t("No keywords found")} className="border-0" />
                            )}
                        </CardContent>
                        <Pagination data={exclusions} className="border-t px-6 pt-4" preserveState only={listProps} />
                    </TabsContent>
                </Tabs>
            </Card>

            <Dialog open={editing !== null} onOpenChange={(open) => !open && setEditing(null)}>
                <DialogContent className="sm:max-w-md">
                    {editing !== null && (
                        <KeywordForm
                            key={editing === "new" ? "new" : editing.id}
                            exclusion={editing === "new" ? null : editing}
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && !processingDelete && setDeleting(null)}
                title={t("Delete keyword?")}
                description={t("Remove :keyword from the exclusion list.").replace(":keyword", deleting?.keyword ?? "")}
                confirmLabel={t("Delete")}
                destructive
                processing={processingDelete}
                onConfirm={deleteKeyword}
            />
        </>
    );
}

function KeywordForm({ exclusion, onClose }: { exclusion: Exclusion | null; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm({ keyword: exclusion?.keyword ?? "" });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        // The dialog is portaled, but React events still bubble to General Settings.
        event.stopPropagation();

        const options = { preserveScroll: true, onSuccess: onClose };
        if (exclusion) {
            form.patch(route("admin.personalized-link-settings.update", exclusion.id), options);
        } else {
            form.post(route("admin.personalized-link-settings.store"), options);
        }
    }

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>{exclusion ? t("Edit keyword") : t("Add keyword")}</DialogTitle>
                <DialogDescription>{t("Enter a single keyword to reserve.")}</DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="exclusion-keyword">{t("Keyword")}</Label>
                <Input
                    id="exclusion-keyword"
                    value={form.data.keyword}
                    onChange={(event) => form.setData("keyword", event.target.value)}
                    aria-invalid={!!form.errors.keyword}
                    className="font-mono text-sm"
                    autoCapitalize="none"
                    autoComplete="off"
                    spellCheck={false}
                    maxLength={255}
                    required
                />
                <FieldError message={form.errors.keyword} />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose} disabled={form.processing}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {exclusion ? t("Save") : t("Add keyword")}
                </Button>
            </div>
        </form>
    );
}
