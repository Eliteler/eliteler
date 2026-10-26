/*
 | GoBiz Original — edit the text of a website page, group by group, plus its SEO details.
 | Built into ../../dist with `npm run plugin:build -- GoBizOriginal` and shipped in the template zip.
 */
import { Head, useForm } from "@inertiajs/react";
import { ExternalLink, Loader2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, FieldError, Input, Label, PageHeader, Textarea } from "@gobiz/admin";

type Field = { id: number; label: string; content: string };
type Group = { name: string; fields: Field[] };
type Seo = { title: string; description: string; keywords: string };
type Props = {
    page: { key: string; name: string; path: string | null };
    groups: Group[];
    seo: Seo | null;
    siteUrl: string;
};

/** Short values (titles, button text, links) get a one-line input; longer text a textarea */
const isShort = (field: Field) => field.content.length <= 80 && !field.content.includes("\n") && !/description|answer|content|text$/i.test(field.label);

export default function EditPage({ page, groups, seo, siteUrl }: Props) {
    const { t } = useTranslation();
    const form = useForm({
        sections: Object.fromEntries(groups.flatMap((group) => group.fields.map((field) => [String(field.id), field.content]))) as Record<string, string>,
        title: seo?.title ?? "",
        description: seo?.description ?? "",
        keywords: seo?.keywords ?? "",
    });
    const { data, setData, errors } = form;
    const fieldErrors = errors as Record<string, string | undefined>;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.web-template.gobiz-original.update-page", page.key), { preserveScroll: true });
    };

    const setSection = (id: number, value: string) => setData("sections", { ...data.sections, [String(id)]: value });

    return (
        <>
            <Head title={page.name} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={page.name}
                    description={t("Change the text shown on this page. The design stays the same.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Web templates"), href: route("admin.web-templates") },
                        { label: "GoBiz Original", href: route("admin.web-template.gobiz-original") },
                        { label: page.name },
                    ]}
                    actions={
                        page.path && (
                            <Button type="button" variant="secondary" asChild>
                                <a href={siteUrl + page.path} target="_blank" rel="noreferrer">
                                    <ExternalLink />
                                    {t("View page")}
                                </a>
                            </Button>
                        )
                    }
                />

                <div className="grid max-w-4xl gap-6">
                    {groups.map((group) => (
                        <Card key={group.name}>
                            {groups.length > 1 && (
                                <CardHeader>
                                    <CardTitle>{group.name}</CardTitle>
                                </CardHeader>
                            )}
                            <CardContent className="grid gap-5 sm:grid-cols-2">
                                {group.fields.map((field) => {
                                    const id = `section-${field.id}`;
                                    const short = isShort(field);
                                    const error = fieldErrors[`sections.${field.id}`];
                                    return (
                                        <div key={field.id} className={short ? "flex flex-col gap-2" : "flex flex-col gap-2 sm:col-span-2"}>
                                            <Label htmlFor={id}>{field.label}</Label>
                                            {short ? (
                                                <Input id={id} value={data.sections[field.id] ?? ""} onChange={(event) => setSection(field.id, event.target.value)} aria-invalid={!!error} />
                                            ) : (
                                                <Textarea
                                                    id={id}
                                                    rows={4}
                                                    value={data.sections[field.id] ?? ""}
                                                    onChange={(event) => setSection(field.id, event.target.value)}
                                                    aria-invalid={!!error}
                                                />
                                            )}
                                            <FieldError message={error} />
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    ))}

                    {seo && (
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Search engines")}</CardTitle>
                                <CardDescription>{t("How this page appears in Google and when it's shared.")}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="title">{t("Meta title")}</Label>
                                    <Textarea id="title" rows={2} value={data.title} onChange={(event) => setData("title", event.target.value)} aria-invalid={!!errors.title} required />
                                    <FieldError message={errors.title} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="description">{t("Meta description")}</Label>
                                    <Textarea
                                        id="description"
                                        rows={3}
                                        value={data.description}
                                        onChange={(event) => setData("description", event.target.value)}
                                        aria-invalid={!!errors.description}
                                        required
                                    />
                                    <FieldError message={errors.description} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="keywords">{t("Meta keywords")}</Label>
                                    <Textarea
                                        id="keywords"
                                        rows={2}
                                        value={data.keywords}
                                        onChange={(event) => setData("keywords", event.target.value)}
                                        placeholder={t("e.g., digital business card, NFC card")}
                                        aria-invalid={!!errors.keywords}
                                        required
                                    />
                                    <p className="text-xs text-muted-foreground">{t("Separate keywords with commas.")}</p>
                                    <FieldError message={errors.keywords} />
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button type="submit" disabled={form.processing || !form.isDirty}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save changes")}
                    </Button>
                </div>
            </form>
        </>
    );
}
