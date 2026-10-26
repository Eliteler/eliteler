import { Head, Link, useForm } from "@inertiajs/react";
import { ExternalLink, Loader2, X } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { RichTextEditor } from "@admin/components/rich-text-editor";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@admin/components/ui/select";
import { Switch } from "@admin/components/ui/switch";
import { Textarea } from "@admin/components/ui/textarea";

type Post = {
    id: string;
    blog_name: string;
    blog_slug: string;
    category_id: string;
    tags: string[];
    short_description: string;
    long_description: string;
    seo_title: string;
    seo_description: string;
    seo_keywords: string;
    published: boolean;
    cover: string | null;
    url: string;
};

type Props = {
    post: Post | null;
    categories: { value: string; label: string }[];
    blogUrl: string;
    maxUploadKb: number;
};

const slugify = (text: string) =>
    text
        .toLowerCase()
        .normalize("NFKD")
        .replace(/[̀-ͯ]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

export default function BlogForm({
    post,
    categories,
    blogUrl,
    maxUploadKb,
}: Props) {
    const { t } = useTranslation();

    // Find the "Uncategorized" category value or fallback to the first active category
    const defaultCategoryId =
        categories.find((c) => c.label.toLowerCase().includes("uncategorized"))
            ?.value ??
        categories[0]?.value ??
        "";

    // Check if the current post's category still exists in the active categories list
    const isCategoryActive = categories.some(
        (c) => c.value === post?.category_id,
    );

    // If post has an active category use it, otherwise select "Uncategorized" by default
    const initialCategoryId =
        isCategoryActive && post?.category_id
            ? post.category_id
            : defaultCategoryId;

    const form = useForm({
        blog_name: post?.blog_name ?? "",
        blog_slug: post?.blog_slug ?? "",
        category_id: initialCategoryId,
        tags: post?.tags ?? [],
        short_description: post?.short_description ?? "",
        long_description: post?.long_description ?? "",
        seo_title: post?.seo_title ?? "",
        seo_description: post?.seo_description ?? "",
        seo_keywords: post?.seo_keywords ?? "",
        published: post?.published ?? true,
        blog_cover: null as File | null,
    });
    const { data, setData, errors } = form;
    // A new post's URL follows its title until the URL is edited by hand
    const [slugTouched, setSlugTouched] = useState(!!post);
    const [tagDraft, setTagDraft] = useState("");
    const tagErrors = Object.entries(errors).find(([key]) =>
        key.startsWith("tags"),
    )?.[1];

    const addTag = () => {
        const tags = tagDraft
            .split(",")
            .map((tag) => tag.trim())
            .filter((tag) => tag && !data.tags.includes(tag));
        if (tags.length) setData("tags", [...data.tags, ...tags]);
        setTagDraft("");
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            published: current.published ? 1 : 0,
        }));
        form.post(
            post
                ? route("admin.update.blog", post.id)
                : route("admin.publish.blog"),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => setData("blog_cover", null),
            },
        );
    };

    const seoTitle = data.seo_title || data.blog_name || t("Post title");
    const seoDescription =
        data.seo_description ||
        data.short_description ||
        t("A short summary of the post.");

    return (
        <>
            <Head title={post ? t("Edit post") : t("New post")} />
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col gap-6 p-4 lg:p-6"
            >
                <PageHeader
                    title={post ? t("Edit post") : t("New post")}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Blog posts"), href: route("admin.blogs") },
                        { label: post ? post.blog_name : t("New post") },
                    ]}
                    actions={
                        post?.published && (
                            <Button variant="outline" asChild>
                                <a
                                    href={post.url}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <ExternalLink />
                                    {t("View on website")}
                                </a>
                            </Button>
                        )
                    }
                />

                <div className="grid gap-6 xl:grid-cols-[1fr_20rem]">
                    <div className="flex min-w-0 flex-col gap-6">
                        <Card>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="blog_name">
                                        {t("Title")}
                                    </Label>
                                    <Input
                                        id="blog_name"
                                        value={data.blog_name}
                                        onChange={(event) => {
                                            setData((current) => ({
                                                ...current,
                                                blog_name: event.target.value,
                                                blog_slug: slugTouched
                                                    ? current.blog_slug
                                                    : slugify(
                                                          event.target.value,
                                                      ),
                                            }));
                                        }}
                                        className="text-base"
                                        aria-invalid={!!errors.blog_name}
                                        required
                                    />
                                    <FieldError message={errors.blog_name} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="blog_slug">
                                        {t("URL")}
                                    </Label>
                                    <div className="flex items-center rounded-md border focus-within:ring-[3px] focus-within:ring-ring/50">
                                        <span className="truncate ps-3 text-sm text-muted-foreground">
                                            {blogUrl}/
                                        </span>
                                        <input
                                            id="blog_slug"
                                            value={data.blog_slug}
                                            onChange={(event) => {
                                                setSlugTouched(true);
                                                setData(
                                                    "blog_slug",
                                                    event.target.value,
                                                );
                                            }}
                                            onBlur={() =>
                                                setData(
                                                    "blog_slug",
                                                    slugify(data.blog_slug),
                                                )
                                            }
                                            className="h-9 min-w-0 flex-1 bg-transparent pe-3 text-sm outline-none"
                                            aria-invalid={!!errors.blog_slug}
                                        />
                                    </div>
                                    <FieldError message={errors.blog_slug} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="short_description">
                                        {t("Summary")}
                                    </Label>
                                    <Textarea
                                        id="short_description"
                                        value={data.short_description}
                                        onChange={(event) =>
                                            setData(
                                                "short_description",
                                                event.target.value,
                                            )
                                        }
                                        className="min-h-16"
                                        maxLength={500}
                                        aria-invalid={
                                            !!errors.short_description
                                        }
                                        required
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        {t(
                                            "Shown on the blog list and when the post is shared.",
                                        )}
                                    </p>
                                    <FieldError
                                        message={errors.short_description}
                                    />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label>{t("Content")}</Label>
                                    <RichTextEditor
                                        value={data.long_description}
                                        onChange={(html) =>
                                            setData("long_description", html)
                                        }
                                        invalid={!!errors.long_description}
                                    />
                                    <FieldError
                                        message={errors.long_description}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Search engines")}</CardTitle>
                                <CardDescription>
                                    {t(
                                        "Optional. Leave blank to use the title, summary and tags.",
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="rounded-md border p-3">
                                    <p className="truncate text-xs text-muted-foreground">
                                        {blogUrl}/{data.blog_slug || "…"}
                                    </p>
                                    <p className="truncate text-base text-blue-700 dark:text-blue-400">
                                        {seoTitle}
                                    </p>
                                    <p className="line-clamp-2 text-sm text-muted-foreground">
                                        {seoDescription}
                                    </p>
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="seo_title">
                                        {t("Page title")}
                                    </Label>
                                    <Input
                                        id="seo_title"
                                        value={data.seo_title}
                                        onChange={(event) =>
                                            setData(
                                                "seo_title",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={data.blog_name}
                                    />
                                    <FieldError message={errors.seo_title} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="seo_description">
                                        {t("Description")}
                                    </Label>
                                    <Textarea
                                        id="seo_description"
                                        value={data.seo_description}
                                        onChange={(event) =>
                                            setData(
                                                "seo_description",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={data.short_description}
                                        className="min-h-16"
                                    />
                                    <FieldError
                                        message={errors.seo_description}
                                    />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="seo_keywords">
                                        {t("Keywords")}
                                    </Label>
                                    <Input
                                        id="seo_keywords"
                                        value={data.seo_keywords}
                                        onChange={(event) =>
                                            setData(
                                                "seo_keywords",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={data.tags.join(", ")}
                                    />
                                    <FieldError message={errors.seo_keywords} />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6 xl:sticky xl:top-20 xl:self-start">
                        <Card>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex items-center justify-between gap-3">
                                    <div className="flex flex-col">
                                        <Label htmlFor="published">
                                            {t("Published")}
                                        </Label>
                                        <span className="text-xs text-muted-foreground">
                                            {data.published
                                                ? t("Visible on your blog")
                                                : t("Saved as a draft")}
                                        </span>
                                    </div>
                                    <Switch
                                        id="published"
                                        checked={data.published}
                                        onCheckedChange={(checked) =>
                                            setData("published", checked)
                                        }
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                >
                                    {form.processing && (
                                        <Loader2 className="animate-spin" />
                                    )}
                                    {post
                                        ? t("Save changes")
                                        : data.published
                                          ? t("Publish post")
                                          : t("Save draft")}
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    asChild
                                >
                                    <Link href={route("admin.blogs")}>
                                        {t("Back to posts")}
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2">
                                    <Label>{t("Cover image")}</Label>
                                    <ImageInput
                                        value={data.blog_cover}
                                        currentUrl={post?.cover}
                                        onChange={(file) =>
                                            setData("blog_cover", file)
                                        }
                                        maxKb={maxUploadKb}
                                        invalid={!!errors.blog_cover}
                                        className="flex-col"
                                        previewClassName="aspect-video h-auto w-full"
                                    />
                                    <FieldError message={errors.blog_cover} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="category_id">
                                        {t("Category")}
                                    </Label>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) =>
                                            setData("category_id", value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="category_id"
                                            className="w-full"
                                            aria-invalid={!!errors.category_id}
                                        >
                                            <SelectValue
                                                placeholder={t(
                                                    "Choose a category",
                                                )}
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem
                                                    key={category.value}
                                                    value={category.value}
                                                >
                                                    {category.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Link
                                        href={route("admin.blog.categories")}
                                        className="text-xs text-primary hover:underline"
                                    >
                                        {t("Manage categories")}
                                    </Link>
                                    <FieldError message={errors.category_id} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="tags">{t("Tags")}</Label>
                                    {data.tags.length > 0 && (
                                        <div className="flex flex-wrap gap-1">
                                            {data.tags.map((tag) => (
                                                <span
                                                    key={tag}
                                                    className="inline-flex items-center gap-1 rounded-full border bg-muted/40 py-0.5 ps-2 pe-1 text-xs"
                                                >
                                                    {tag}
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setData(
                                                                "tags",
                                                                data.tags.filter(
                                                                    (item) =>
                                                                        item !==
                                                                        tag,
                                                                ),
                                                            )
                                                        }
                                                        className="rounded-full p-0.5 hover:bg-muted"
                                                        aria-label={t("Remove")}
                                                    >
                                                        <X className="size-3" />
                                                    </button>
                                                </span>
                                            ))}
                                        </div>
                                    )}
                                    <Input
                                        id="tags"
                                        value={tagDraft}
                                        onChange={(event) =>
                                            setTagDraft(event.target.value)
                                        }
                                        onKeyDown={(event) => {
                                            if (
                                                event.key === "Enter" ||
                                                event.key === ","
                                            ) {
                                                event.preventDefault();
                                                addTag();
                                            }
                                        }}
                                        onBlur={addTag}
                                        placeholder={t(
                                            "Type a tag and press Enter",
                                        )}
                                    />
                                    <FieldError message={tagErrors} />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </>
    );
}
