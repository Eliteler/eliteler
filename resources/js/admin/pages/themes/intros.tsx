import { Head, router, useForm } from "@inertiajs/react";
import { Clapperboard, Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";

import { PreviewDialog } from "./components/preview-dialog";
import { StatusToggleDialog } from "./components/status-toggle-dialog";
import { ThemeCard } from "./components/theme-card";
import type { IntroItem } from "./types";

type Props = {
    intros: IntroItem[];
    filters: { status: "all" | "active" | "disabled" };
    maxUploadKb: number;
};

export default function Intros({ intros, filters, maxUploadKb }: Props) {
    const { t } = useTranslation();
    const [preview, setPreview] = useState<IntroItem | null>(null);
    const [toggle, setToggle] = useState<IntroItem | null>(null);
    const [editing, setEditing] = useState<IntroItem | null>(null);
    const [processing, setProcessing] = useState(false);

    const filter = (status: string) =>
        router.get(route("admin.business-card-intros"), status === "all" ? {} : { status }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });

    const confirmToggle = () => {
        if (!toggle) return;
        router.post(route("admin.business-card-intro.status", toggle.id), {}, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setToggle(null);
            },
        });
    };

    return (
        <>
            <Head title={t("Business Card Intros")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Business Card Intros")}
                    description={t("Animated intro screens shown before a business card opens.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Themes"), href: route("admin.themes", { status: "all" }) },
                        { label: t("Intros") },
                    ]}
                />

                <Tabs value={filters.status} onValueChange={filter}>
                    <TabsList>
                        <TabsTrigger value="all">{t("All")}</TabsTrigger>
                        <TabsTrigger value="active">{t("Active")}</TabsTrigger>
                        <TabsTrigger value="disabled">{t("Inactive")}</TabsTrigger>
                    </TabsList>
                </Tabs>

                {intros.length === 0 ? (
                    <EmptyState icon={Clapperboard} title={t("No intros found")} />
                ) : (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                        {intros.map((intro) => (
                            <ThemeCard
                                key={intro.id}
                                name={intro.name}
                                thumbnail={intro.thumbnail}
                                active={intro.active}
                                usage={intro.usage}
                                onEdit={() => setEditing(intro)}
                                onPreview={() => setPreview(intro)}
                                onToggle={() => setToggle(intro)}
                            />
                        ))}
                    </div>
                )}
            </div>

            <EditIntroDialog intro={editing} maxUploadKb={maxUploadKb} onClose={() => setEditing(null)} />
            <PreviewDialog
                open={preview !== null}
                onOpenChange={(open) => !open && setPreview(null)}
                title={preview?.name ?? ""}
                src={preview?.thumbnail ?? null}
            />
            <StatusToggleDialog
                target={toggle}
                kind="intro"
                processing={processing}
                onCancel={() => setToggle(null)}
                onConfirm={confirmToggle}
            />
        </>
    );
}

function EditIntroDialog({ intro, maxUploadKb, onClose }: { intro: IntroItem | null; maxUploadKb: number; onClose: () => void }) {
    return (
        <Dialog open={intro !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-md">
                {intro && <EditIntroForm key={intro.id} intro={intro} maxUploadKb={maxUploadKb} onClose={onClose} />}
            </DialogContent>
        </Dialog>
    );
}

function EditIntroForm({ intro, maxUploadKb, onClose }: { intro: IntroItem; maxUploadKb: number; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm({ intro_name: intro.name, intro_thumbnail: null as File | null });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.business-card-intro", intro.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-6">
            <DialogHeader>
                <DialogTitle>{t("Edit intro")}</DialogTitle>
                <DialogDescription>{t("Change the name or preview image.")}</DialogDescription>
            </DialogHeader>
            <div className="flex flex-col gap-2">
                <Label htmlFor="intro_name">{t("Name")}</Label>
                <Input
                    id="intro_name"
                    value={form.data.intro_name}
                    onChange={(event) => form.setData("intro_name", event.target.value)}
                    aria-invalid={!!form.errors.intro_name}
                    maxLength={191}
                    required
                />
                <FieldError message={form.errors.intro_name} />
            </div>
            <div className="flex flex-col gap-2">
                <Label>{t("Preview image")}</Label>
                <ImageInput
                    value={form.data.intro_thumbnail}
                    currentUrl={intro.thumbnail}
                    onChange={(file) => form.setData("intro_thumbnail", file)}
                    maxKb={maxUploadKb}
                    invalid={!!form.errors.intro_thumbnail}
                />
                <FieldError message={form.errors.intro_thumbnail} />
            </div>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing || !form.isDirty}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {t("Save changes")}
                </Button>
            </div>
        </form>
    );
}
