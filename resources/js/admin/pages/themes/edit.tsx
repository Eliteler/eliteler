import { Head, Link, router, useForm } from "@inertiajs/react";
import { ArrowLeft, Loader2, Save, Users } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import { fromNow } from "@admin/lib/date";

import { StatusToggleDialog } from "./components/status-toggle-dialog";
import { THEME_TYPE_LABELS, type ThemeDetail } from "./types";
import Dot from "../../components/ui/dot";

const MAX_CODE = 25000;

export default function ThemeEdit({
    theme,
    maxUploadKb,
}: {
    theme: ThemeDetail;
    maxUploadKb: number;
}) {
    const { t } = useTranslation();
    const [toggling, setToggling] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);

    const form = useForm({
        theme_id: theme.id,
        theme_name: theme.name,
        theme_thumbnail: null as File | null,
        css: theme.css,
        js: theme.js,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.theme"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // The saved values become the new baseline for isDirty.
                form.setDefaults({ ...form.data, theme_thumbnail: null });
                form.setData("theme_thumbnail", null);
            },
        });
    };

    const toggleStatus = () => {
        router.post(
            route("admin.update.theme.status"),
            { id: theme.id, status: theme.active ? "disable" : "enable" },
            {
                preserveScroll: true,
                onStart: () => setToggling(true),
                onFinish: () => {
                    setToggling(false);
                    setConfirmOpen(false);
                },
            },
        );
    };

    const backHref = route("admin.themes", { status: "all" });

    return (
        <>
            <Head title={`${t("Edit")} ${theme.name}`} />
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col gap-6 p-4 lg:p-6"
            >
                <PageHeader
                    title={theme.name}
                    description={t(
                        "Rename the theme, replace its preview image or add custom CSS and JavaScript.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Themes"), href: backHref },
                        { label: t("Edit") },
                    ]}
                    actions={
                        <>
                            <Button variant="outline" asChild>
                                <Link href={backHref}>
                                    <ArrowLeft className="rtl:rotate-180" />
                                    {t("Back")}
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={form.processing || !form.isDirty}
                            >
                                {form.processing ? (
                                    <Loader2 className="animate-spin" />
                                ) : (
                                    <Save />
                                )}
                                {t("Save changes")}
                            </Button>
                        </>
                    }
                />

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="flex flex-col gap-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Details")}</CardTitle>
                                <CardDescription>
                                    {t(
                                        "Shown to customers when they pick a design.",
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-6">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="theme_name">
                                        {t("Theme name")}
                                    </Label>
                                    <Input
                                        id="theme_name"
                                        value={form.data.theme_name}
                                        onChange={(event) =>
                                            form.setData(
                                                "theme_name",
                                                event.target.value,
                                            )
                                        }
                                        aria-invalid={!!form.errors.theme_name}
                                        maxLength={191}
                                        required
                                    />
                                    <FieldError
                                        message={form.errors.theme_name}
                                    />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label>{t("Preview image")}</Label>
                                    <ImageInput
                                        value={form.data.theme_thumbnail}
                                        currentUrl={theme.thumbnail}
                                        onChange={(file) =>
                                            form.setData(
                                                "theme_thumbnail",
                                                file,
                                            )
                                        }
                                        maxKb={maxUploadKb}
                                        invalid={!!form.errors.theme_thumbnail}
                                    />
                                    <FieldError
                                        message={form.errors.theme_thumbnail}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Custom code")}</CardTitle>
                                <CardDescription>
                                    {t(
                                        "Added to every business card or store that uses this theme.",
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Tabs defaultValue="css">
                                    <TabsList>
                                        <TabsTrigger value="css">
                                            CSS
                                            {form.data.css.trim() && (
                                                <span className="size-1.5 rounded-full bg-primary" />
                                            )}
                                        </TabsTrigger>
                                        <TabsTrigger value="js">
                                            JavaScript
                                            {form.data.js.trim() && (
                                                <span className="size-1.5 rounded-full bg-primary" />
                                            )}
                                        </TabsTrigger>
                                    </TabsList>
                                    {(["css", "js"] as const).map((field) => (
                                        <TabsContent
                                            key={field}
                                            value={field}
                                            className="flex flex-col gap-2"
                                        >
                                            <Textarea
                                                value={form.data[field]}
                                                onChange={(event) =>
                                                    form.setData(
                                                        field,
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder={
                                                    field === "css"
                                                        ? ".card { border-radius: 24px; }"
                                                        : "console.log('Hello');"
                                                }
                                                spellCheck={false}
                                                maxLength={MAX_CODE}
                                                dir="ltr"
                                                aria-invalid={
                                                    !!form.errors[field]
                                                }
                                                className="min-h-72 resize-y bg-muted/30 font-mono text-xs leading-relaxed [field-sizing:fixed]"
                                            />
                                            <div className="flex justify-between gap-2 text-xs text-muted-foreground">
                                                <span>
                                                    {field === "css"
                                                        ? t(
                                                              "Write plain CSS without <style> tags.",
                                                          )
                                                        : t(
                                                              "Write plain JavaScript without <script> tags.",
                                                          )}
                                                </span>
                                                <span className="tabular-nums">
                                                    {form.data[
                                                        field
                                                    ].length.toLocaleString()}{" "}
                                                    /{" "}
                                                    {MAX_CODE.toLocaleString()}
                                                </span>
                                            </div>
                                            <FieldError
                                                message={form.errors[field]}
                                            />
                                        </TabsContent>
                                    ))}
                                </Tabs>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Status")}</CardTitle>
                                <CardDescription>
                                    {t(
                                        "Inactive themes are hidden from customers.",
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center justify-between gap-4 rounded-lg border p-3">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-sm font-medium">
                                            {theme.active ? (
                                                <div className="flex items-center gap-1">
                                                    <Dot color="green-500" />
                                                    {t("Active")}
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-1">
                                                    <Dot color="orange-500" />
                                                    {t("Inactive")}
                                                </div>
                                            )}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {theme.active
                                                ? t("Available to customers")
                                                : t("Hidden from customers")}
                                        </span>
                                    </div>
                                    <Switch
                                        checked={theme.active}
                                        disabled={toggling}
                                        onCheckedChange={() =>
                                            setConfirmOpen(true)
                                        }
                                        aria-label={t("Status")}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Overview")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        {t("Type")}
                                    </span>
                                    <Badge variant="secondary">
                                        {t(THEME_TYPE_LABELS[theme.type])}
                                    </Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        {t("In use")}
                                    </span>
                                    <span className="flex items-center gap-1.5 font-medium tabular-nums">
                                        <Users className="size-3.5 text-muted-foreground" />
                                        {theme.usage}{" "}
                                        {theme.type === "vcard"
                                            ? t(
                                                  theme.usage === 1
                                                      ? "Business card"
                                                      : "Business cards",
                                              )
                                            : t(
                                                  theme.usage === 1
                                                      ? "Store"
                                                      : "Stores",
                                              )}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        {t("Last updated")}
                                    </span>
                                    <span>{fromNow(theme.updatedAt)}</span>
                                </div>
                            </CardContent>
                            {theme.thumbnail && (
                                <CardFooter>
                                    <img
                                        src={theme.thumbnail}
                                        alt={theme.name}
                                        className="w-full rounded-lg border"
                                    />
                                </CardFooter>
                            )}
                        </Card>
                    </div>
                </div>
            </form>

            <StatusToggleDialog
                target={confirmOpen ? theme : null}
                kind="theme"
                processing={toggling}
                onCancel={() => setConfirmOpen(false)}
                onConfirm={toggleStatus}
            />
        </>
    );
}
