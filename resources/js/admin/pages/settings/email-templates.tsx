import { Head, Link, router, useForm } from "@inertiajs/react";
import {
    Code2,
    Eye,
    ImageIcon,
    Loader2,
    Monitor,
    Search,
    Smartphone,
} from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";
// Free open-source WYSIWYG editor
import ReactQuill from "react-quill-new";
import "react-quill-new/dist/quill.snow.css";

import { FieldError } from "@admin/components/field-error";
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import { cn } from "@admin/lib/utils";

type Template = {
    id: string;
    name: string;
    subject: string;
    content: string;
    enabled: boolean;
};

type Props = {
    templates: { id: string; name: string; enabled: boolean }[];
    template: Template;
    shortcodes: { group: string; codes: { code: string; sample: string }[] }[];
    previewCss: string;
};

export default function EmailTemplates({
    templates,
    template,
    shortcodes,
    previewCss,
}: Props) {
    const { t } = useTranslation();
    const [filter, setFilter] = useState("");

    const list = useMemo(() => {
        const term = filter.trim().toLowerCase();
        return term
            ? templates.filter((item) => item.name.toLowerCase().includes(term))
            : templates;
    }, [templates, filter]);

    return (
        <>
            <Head title={t("Email templates")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Email templates")}
                    description={t(
                        "The emails GoBiz sends for appointments, orders, plans and more. Edit the wording or turn an email off.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Email templates") },
                    ]}
                />

                <div className="grid gap-6 lg:grid-cols-[16rem_1fr]">
                    <Card className="gap-0 self-start py-0">
                        <div className="border-b p-3">
                            <div className="relative">
                                <Search className="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={filter}
                                    onChange={(event) =>
                                        setFilter(event.target.value)
                                    }
                                    placeholder={t("Find a template")}
                                    className="h-8 ps-8"
                                />
                            </div>
                        </div>
                        <nav className="flex max-h-[70svh] flex-col overflow-y-auto p-1.5">
                            {list.map((item) => (
                                <Link
                                    key={item.id}
                                    href={route(
                                        "admin.email.templates.index",
                                        item.id,
                                    )}
                                    preserveScroll
                                    className={cn(
                                        "flex items-center justify-between gap-2 rounded-md px-2.5 py-2 text-sm hover:bg-muted",
                                        item.id === template.id &&
                                            "bg-muted font-medium",
                                    )}
                                >
                                    <span className="truncate">
                                        {item.name}
                                    </span>
                                    {!item.enabled && (
                                        <Badge
                                            variant="outline"
                                            className="shrink-0 text-[10px]"
                                        >
                                            {t("Off")}
                                        </Badge>
                                    )}
                                </Link>
                            ))}
                        </nav>
                    </Card>

                    <Editor
                        key={template.id}
                        template={template}
                        shortcodes={shortcodes}
                        previewCss={previewCss}
                    />
                </div>
            </div>
        </>
    );
}

function Editor({
    template,
    shortcodes,
    previewCss,
}: {
    template: Template;
    shortcodes: Props["shortcodes"];
    previewCss: string;
}) {
    const { t } = useTranslation();
    const form = useForm({
        email_template_id: template.id,
        email_template_subject: template.subject,
        email_template_content: template.content,
        is_enabled: template.enabled,
    });
    const { data, setData, errors } = form;

    // Toggle between Visual Rich Text and Raw HTML mode
    const [isSourceMode, setIsSourceMode] = useState(false);

    // States for custom Image URL modal
    const [imageModalOpen, setImageModalOpen] = useState(false);
    const [imageUrl, setImageUrl] = useState("");

    const quillRef = useRef<any>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const subjectInput = useRef<HTMLInputElement>(null);
    const lastFocused = useRef<"subject" | "content">("content");
    const [device, setDevice] = useState<"desktop" | "mobile">("desktop");

    // Ask before leaving with unsaved changes
    useEffect(
        () =>
            router.on("before", (event) => {
                if (
                    form.isDirty &&
                    event.detail.visit.method === "get" &&
                    !window.confirm(
                        t("You have unsaved changes. Leave without saving?"),
                    )
                ) {
                    event.preventDefault();
                }
            }),
        [form.isDirty, t],
    );

    const samples = useMemo(
        () => shortcodes.flatMap((group) => group.codes),
        [shortcodes],
    );
    const fill = (text: string) =>
        [...samples]
            .sort((a, b) => b.code.length - a.code.length)
            .reduce(
                (out, { code, sample }) => out.split(code).join(sample),
                text,
            );

    // Insert shortcode placeholder into active input/editor
    const insert = (code: string) => {
        if (lastFocused.current === "subject") {
            const target = subjectInput.current;
            const value = data.email_template_subject;
            const start = target?.selectionStart ?? value.length;
            const end = target?.selectionEnd ?? value.length;
            setData(
                "email_template_subject",
                value.slice(0, start) + code + value.slice(end),
            );
            requestAnimationFrame(() => {
                target?.focus();
                target?.setSelectionRange(
                    start + code.length,
                    start + code.length,
                );
            });
        } else if (isSourceMode) {
            const target = textareaRef.current;
            const value = data.email_template_content;
            const start = target?.selectionStart ?? value.length;
            const end = target?.selectionEnd ?? value.length;
            setData(
                "email_template_content",
                value.slice(0, start) + code + value.slice(end),
            );
            requestAnimationFrame(() => {
                target?.focus();
                target?.setSelectionRange(
                    start + code.length,
                    start + code.length,
                );
            });
        } else {
            const editor = quillRef.current?.getEditor();
            if (editor) {
                const range = editor.getSelection(true);
                editor.insertText(range.index, code);
                editor.setSelection(range.index + code.length);
                // Sync updated HTML from Quill to form state
                setData("email_template_content", editor.root.innerHTML);
            } else {
                setData(
                    "email_template_content",
                    data.email_template_content + code,
                );
            }
        }
    };

    // Confirm image insertion from modal dialog
    const handleInsertImage = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        if (imageUrl.trim()) {
            const editor = quillRef.current?.getEditor();
            if (editor) {
                const range = editor.getSelection(true);
                editor.insertEmbed(range.index, "image", imageUrl.trim());
                editor.setSelection(range.index + 1);
                // Sync updated HTML from Quill to form state
                setData("email_template_content", editor.root.innerHTML);
            }
            setImageUrl("");
            setImageModalOpen(false);
        }
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            is_enabled: current.is_enabled ? 1 : 0,
        }));
        form.post(route("admin.update.email.template.content"), {
            preserveScroll: true,
            onSuccess: () => form.setDefaults(),
        });
    };

    const previewDoc = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><style>${previewCss} body{margin:0;padding:16px}</style></head><body>${fill(data.email_template_content)}</body></html>`;

    // Quill toolbar configuration
    const quillModules = useMemo(
        () => ({
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ color: [] }, { background: [] }],
                    [{ list: "ordered" }, { list: "bullet" }],
                    [{ align: [] }],
                    ["link", "image", "code-block"],
                    ["clean"],
                ],
                handlers: {
                    image: () => {
                        setImageUrl("");
                        setImageModalOpen(true);
                    },
                },
            },
        }),
        [],
    );

    return (
        <>
            <form onSubmit={submit} className="flex min-w-0 flex-col gap-6">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div className="flex flex-col gap-1.5">
                            <CardTitle>{template.name}</CardTitle>
                            <CardDescription>
                                {t(
                                    "Click a placeholder to insert it where your cursor is. It's replaced with real details when the email is sent.",
                                )}
                            </CardDescription>
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            <Label
                                htmlFor="is_enabled"
                                className="text-sm font-normal text-muted-foreground"
                            >
                                {data.is_enabled
                                    ? t("Sending")
                                    : t("Not sending")}
                            </Label>
                            <Switch
                                id="is_enabled"
                                checked={data.is_enabled}
                                onCheckedChange={(checked) =>
                                    setData("is_enabled", checked)
                                }
                            />
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="email_template_subject">
                                {t("Subject")}
                            </Label>
                            <Input
                                id="email_template_subject"
                                ref={subjectInput}
                                value={data.email_template_subject}
                                onFocus={() =>
                                    (lastFocused.current = "subject")
                                }
                                onChange={(event) =>
                                    setData(
                                        "email_template_subject",
                                        event.target.value,
                                    )
                                }
                                aria-invalid={!!errors.email_template_subject}
                                required
                            />
                            <FieldError
                                message={errors.email_template_subject}
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <span className="text-sm font-medium">
                                {t("Placeholders")}
                            </span>
                            <div className="flex max-h-36 flex-col gap-2 overflow-y-auto rounded-md border p-2">
                                {shortcodes.map((group) => (
                                    <div
                                        key={group.group}
                                        className="flex flex-wrap items-center gap-1"
                                    >
                                        <span className="w-24 shrink-0 text-xs text-muted-foreground">
                                            {group.group}
                                        </span>
                                        {group.codes.map(({ code, sample }) => (
                                            <button
                                                key={code}
                                                type="button"
                                                onClick={() => insert(code)}
                                                title={t(
                                                    "Example: :value",
                                                ).replace(":value", sample)}
                                                className="rounded border bg-muted/40 px-1.5 py-0.5 font-mono text-[11px] hover:border-primary hover:text-primary"
                                            >
                                                {code}
                                            </button>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-4 xl:grid-cols-2">
                            {/* Editor Section */}
                            <div className="flex min-w-0 flex-col gap-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="email_template_content">
                                        {t("Message (HTML)")}
                                    </Label>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        className="h-7 gap-1.5 text-xs font-normal"
                                        onClick={() =>
                                            setIsSourceMode((prev) => !prev)
                                        }
                                    >
                                        {isSourceMode ? (
                                            <>
                                                <Eye className="size-3.5" />
                                                {t("Visual Editor")}
                                            </>
                                        ) : (
                                            <>
                                                <Code2 className="size-3.5" />
                                                {t("HTML Code / Source")}
                                            </>
                                        )}
                                    </Button>
                                </div>

                                {isSourceMode ? (
                                    <Textarea
                                        id="email_template_content"
                                        ref={textareaRef}
                                        value={data.email_template_content}
                                        onFocus={() =>
                                            (lastFocused.current = "content")
                                        }
                                        onChange={(event) =>
                                            setData(
                                                "email_template_content",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t(
                                            "Paste raw HTML code here...",
                                        )}
                                        className="min-h-[360px] flex-1 font-mono text-xs leading-relaxed"
                                        spellCheck={false}
                                        aria-invalid={
                                            !!errors.email_template_content
                                        }
                                    />
                                ) : (
                                    <div className="rounded-md border bg-background overflow-hidden [&_.ql-toolbar]:border-none [&_.ql-toolbar]:border-b [&_.ql-container]:border-none [&_.ql-editor]:min-h-[320px]">
                                        <ReactQuill
                                            ref={quillRef}
                                            theme="snow"
                                            value={data.email_template_content}
                                            // Only update state if the action is triggered directly by the user typing
                                            onChange={(
                                                content,
                                                _delta,
                                                source,
                                            ) => {
                                                if (source === "user") {
                                                    setData(
                                                        "email_template_content",
                                                        content,
                                                    );
                                                }
                                            }}
                                            onFocus={() =>
                                                (lastFocused.current =
                                                    "content")
                                            }
                                            modules={quillModules}
                                            placeholder={t(
                                                "Write email content here...",
                                            )}
                                        />
                                    </div>
                                )}

                                <FieldError
                                    message={errors.email_template_content}
                                />
                            </div>

                            {/* Live Preview Section */}
                            <div className="flex min-w-0 flex-col gap-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium">
                                        {t("Preview")}
                                    </span>
                                    <Tabs
                                        value={device}
                                        onValueChange={(value) =>
                                            setDevice(value as typeof device)
                                        }
                                    >
                                        <TabsList className="h-8">
                                            <TabsTrigger
                                                value="desktop"
                                                className="px-2"
                                                aria-label={t("Desktop")}
                                            >
                                                <Monitor className="size-4" />
                                            </TabsTrigger>
                                            <TabsTrigger
                                                value="mobile"
                                                className="px-2"
                                                aria-label={t("Mobile")}
                                            >
                                                <Smartphone className="size-4" />
                                            </TabsTrigger>
                                        </TabsList>
                                    </Tabs>
                                </div>
                                <div className="flex flex-1 flex-col overflow-hidden rounded-md border bg-muted/30">
                                    <div className="border-b bg-background px-3 py-2 text-sm">
                                        <span className="text-muted-foreground">
                                            {t("Subject")}:{" "}
                                        </span>
                                        <span className="font-medium">
                                            {fill(data.email_template_subject)}
                                        </span>
                                    </div>
                                    <div className="flex flex-1 justify-center p-3">
                                        <iframe
                                            title={t("Preview")}
                                            sandbox=""
                                            srcDoc={previewDoc}
                                            className={cn(
                                                "min-h-96 rounded border bg-white transition-all",
                                                device === "mobile"
                                                    ? "w-full max-w-[375px]"
                                                    : "w-full",
                                            )}
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter className="justify-end border-t pt-6">
                        <Button
                            type="submit"
                            disabled={form.processing || !form.isDirty}
                        >
                            {form.processing && (
                                <Loader2 className="animate-spin" />
                            )}
                            {t("Save changes")}
                        </Button>
                    </CardFooter>
                </Card>
            </form>

            {/* Custom Modal for Image URL Insertion */}
            <Dialog open={imageModalOpen} onOpenChange={setImageModalOpen}>
                <DialogContent className="sm:max-w-md">
                    <form onSubmit={handleInsertImage}>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <ImageIcon className="size-5 text-primary" />
                                {t("Insert image")}
                            </DialogTitle>
                            <DialogDescription>
                                {t(
                                    "Enter a publicly accessible URL for the image to display in email clients.",
                                )}
                            </DialogDescription>
                        </DialogHeader>

                        <div className="my-4 flex flex-col gap-2">
                            <Label htmlFor="image_modal_url">
                                {t("Image URL")}
                            </Label>
                            <Input
                                id="image_modal_url"
                                type="url"
                                autoFocus
                                value={imageUrl}
                                onChange={(e) => setImageUrl(e.target.value)}
                                placeholder="https://example.com/assets/logo.png"
                                required
                            />
                        </div>

                        {/* Replaced DialogFooter with standard div container */}
                        <div className="flex flex-col-reverse justify-end gap-2 pt-2 sm:flex-row">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setImageModalOpen(false)}
                            >
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={!imageUrl.trim()}>
                                {t("Insert")}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
