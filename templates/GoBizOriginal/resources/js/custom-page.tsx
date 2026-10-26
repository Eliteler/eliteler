/*
 | GoBiz Original — add or edit a custom page (shown at /p/<address>).
 | Built into ../../dist with `npm run plugin:build -- GoBizOriginal` and shipped in the template zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { ExternalLink, Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, RichTextEditor, Textarea } from "@gobiz/admin";

type Page = { id: number; page_name: string; slug: string; body: string; title: string; description: string; keywords: string };
type Props = { page: Page | null; siteUrl: string };

const slugify = (value: string) =>
    value
        .toLowerCase()
        .normalize("NFKD")
        .replace(/[̀-ͯ]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

export default function CustomPage({ page, siteUrl }: Props) {
    const { t } = useTranslation();
    const editing = page !== null;
    const form = useForm({
        page_id: page?.id ?? 0,
        page_name: page?.page_name ?? "",
        slug: page?.slug ?? "",
        body: page?.body ?? "",
        title: page?.title ?? "",
        description: page?.description ?? "",
        keywords: page?.keywords ?? "",
    });
    const { data, setData, errors } = form;
    // New pages take their address from the name until it's edited by hand
    const [slugTouched, setSlugTouched] = useState(editing);
    const heading = editing ? page.page_name : t("Add page");

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route(editing ? "admin.web-template.gobiz-original.update-custom-page" : "admin.web-template.gobiz-original.save-custom-page"), { preserveScroll: true });
    };

    const text = (key: "title" | "description" | "keywords", label: string, rows: number, hint?: string) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Textarea id={key} rows={rows} value={data[key]} onChange={(event) => setData(key, event.target.value)} aria-invalid={!!errors[key]} required />
            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <FieldError message={errors[key]} />
        </div>
    );

    return (
        <>
            <Head title={heading} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={heading}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Web templates"), href: route("admin.web-templates") },
                        { label: "GoBiz Original", href: route("admin.web-template.gobiz-original.custom-pages") },
                        { label: heading },
                    ]}
                    actions={
                        editing && (
                            <Button type="button" variant="secondary" asChild>
                                <a href={`${siteUrl}/p/${page.slug}`} target="_blank" rel="noreferrer">
                                    <ExternalLink />
                                    {t("View page")}
                                </a>
                            </Button>
                        )
                    }
                />

                <div className="grid max-w-4xl gap-6">
                    <Card>
                        <CardContent className="flex flex-col gap-5">
                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="page_name">{t("Page name")}</Label>
                                    <Input
                                        id="page_name"
                                        value={data.page_name}
                                        onChange={(event) => {
                                            const name = event.target.value;
                                            form.setData((current) => ({ ...current, page_name: name, slug: slugTouched ? current.slug : slugify(name) }));
                                        }}
                                        aria-invalid={!!errors.page_name}
                                        required
                                    />
                                    <FieldError message={errors.page_name} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="slug">{t("Address")}</Label>
                                    <div className="flex min-w-0 items-center rounded-md border shadow-xs focus-within:ring-[3px] focus-within:ring-ring/50">
                                        <span className="shrink-0 pl-3 text-sm text-muted-foreground">/p/</span>
                                        <Input
                                            id="slug"
                                            value={data.slug}
                                            onChange={(event) => {
                                                setSlugTouched(true);
                                                setData("slug", event.target.value);
                                            }}
                                            onBlur={() => setData("slug", slugify(data.slug))}
                                            className="min-w-0 border-0 pl-0.5 shadow-none focus-visible:ring-0"
                                            aria-invalid={!!errors.slug}
                                            required
                                        />
                                    </div>
                                    <FieldError message={errors.slug} />
                                </div>
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label>{t("Content")}</Label>
                                <RichTextEditor value={data.body} onChange={(html) => setData("body", html)} invalid={!!errors.body} />
                                <FieldError message={errors.body} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Search engines")}</CardTitle>
                            <CardDescription>{t("How this page appears in Google and when it's shared.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            {text("title", t("Meta title"), 2)}
                            {text("description", t("Meta description"), 3)}
                            {text("keywords", t("Meta keywords"), 2, t("Separate keywords with commas."))}
                        </CardContent>
                    </Card>
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button type="submit" disabled={form.processing || !form.isDirty}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {editing ? t("Save changes") : t("Create page")}
                    </Button>
                </div>
            </form>
        </>
    );
}
