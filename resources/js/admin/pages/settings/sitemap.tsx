import { Head, useForm } from "@inertiajs/react";
import { Check, ExternalLink, FileCode2, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { CopyButton } from "@admin/components/copy-button";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { formatDate } from "@admin/lib/date";
import { formatNumber } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

type Category = { id: string; label: string; description: string; count: number };

type Status = { url: string; generatedAt: string; sizeKb: number; urls: number } | null;

type Props = { categories: Category[]; status: Status };

export default function Sitemap({ categories, status }: Props) {
    const { t } = useTranslation();
    const form = useForm<{ categories: string[] }>({ categories: categories.map((category) => category.id) });
    const selected = form.data.categories;
    const allSelected = selected.length === categories.length;
    const total = categories.filter((category) => selected.includes(category.id)).reduce((sum, category) => sum + category.count, 0);
    const errors = form.errors as Record<string, string | undefined>;

    const toggle = (id: string) => form.setData("categories", selected.includes(id) ? selected.filter((item) => item !== id) : [...selected, id]);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.generate.sitemap"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Sitemap")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Sitemap")}
                    description={t("Create a sitemap.xml so search engines can find your pages, blog posts and cards.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Sitemap") }]}
                />

                <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                    <Card>
                        <form onSubmit={submit} className="flex flex-col gap-6">
                            <CardHeader className="flex flex-row items-start justify-between gap-4">
                                <div className="flex flex-col gap-1.5">
                                    <CardTitle>{t("What to include")}</CardTitle>
                                    <CardDescription>{t("Only active, public content is added. Each link appears once.")}</CardDescription>
                                </div>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    onClick={() => form.setData("categories", allSelected ? [] : categories.map((category) => category.id))}
                                >
                                    {allSelected ? t("Clear all") : t("Select all")}
                                </Button>
                            </CardHeader>
                            <CardContent className="grid gap-3 sm:grid-cols-2">
                                {categories.map((category) => {
                                    const on = selected.includes(category.id);
                                    return (
                                        <button
                                            key={category.id}
                                            type="button"
                                            role="checkbox"
                                            aria-checked={on}
                                            onClick={() => toggle(category.id)}
                                            className={cn(
                                                "flex items-start gap-3 rounded-lg border p-3 text-start transition-colors",
                                                on ? "border-primary bg-primary/5" : "hover:bg-muted/50",
                                            )}
                                        >
                                            <span
                                                className={cn(
                                                    "mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-[4px] border",
                                                    on && "border-primary bg-primary text-primary-foreground",
                                                )}
                                            >
                                                {on && <Check className="size-3" />}
                                            </span>
                                            <span className="flex min-w-0 flex-1 flex-col gap-0.5">
                                                <span className="flex items-center justify-between gap-2 text-sm font-medium">
                                                    {category.label}
                                                    <span className="text-xs font-normal text-muted-foreground tabular-nums">
                                                        {t(":count links").replace(":count", formatNumber(category.count))}
                                                    </span>
                                                </span>
                                                <span className="text-xs text-muted-foreground">{category.description}</span>
                                            </span>
                                        </button>
                                    );
                                })}
                                <FieldError message={errors.categories ?? Object.entries(errors).find(([key]) => key.startsWith("categories."))?.[1]} />
                            </CardContent>
                            <CardFooter className="justify-between gap-2 border-t pt-6">
                                <span className="text-sm text-muted-foreground">
                                    {t("About :count links").replace(":count", formatNumber(total))}
                                </span>
                                <Button type="submit" disabled={form.processing || selected.length === 0}>
                                    {form.processing && <Loader2 className="animate-spin" />}
                                    {status ? t("Regenerate sitemap") : t("Generate sitemap")}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>

                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Current sitemap")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4 text-sm">
                                {status ? (
                                    <>
                                        <dl className="grid grid-cols-2 gap-3">
                                            <div>
                                                <dt className="text-xs text-muted-foreground">{t("Links")}</dt>
                                                <dd className="font-medium tabular-nums">{formatNumber(status.urls)}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">{t("File size")}</dt>
                                                <dd className="font-medium tabular-nums">{status.sizeKb} KB</dd>
                                            </div>
                                            <div className="col-span-2">
                                                <dt className="text-xs text-muted-foreground">{t("Last generated")}</dt>
                                                <dd className="font-medium">{formatDate(status.generatedAt, "dd MMM yyyy, HH:mm")}</dd>
                                            </div>
                                        </dl>
                                        <div className="flex items-center gap-2">
                                            <code className="min-w-0 flex-1 truncate rounded-md border bg-muted px-2 py-1.5 font-mono text-xs">{status.url}</code>
                                            <CopyButton value={status.url} />
                                        </div>
                                        <Button variant="outline" size="sm" asChild>
                                            <a href={status.url} target="_blank" rel="noreferrer">
                                                <ExternalLink />
                                                {t("Open sitemap")}
                                            </a>
                                        </Button>
                                    </>
                                ) : (
                                    <div className="flex flex-col items-center gap-2 py-4 text-center text-muted-foreground">
                                        <FileCode2 className="size-8" />
                                        {t("No sitemap yet.")}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Tips")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2 text-sm text-muted-foreground">
                                <p>{t("Regenerate after adding pages, blog posts or cards. The sitemap doesn't update by itself.")}</p>
                                <p>{t("Submit the sitemap link in Google Search Console and Bing Webmaster Tools.")}</p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
