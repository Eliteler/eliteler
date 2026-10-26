import { useEffect, useId, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { Skeleton } from "@admin/components/ui/skeleton";
import { cn } from "@admin/lib/utils";

/*
 | TinyMCE (same build the Blade pages used), loaded once from cdnjs on first
 | use so it stays out of the admin bundle. Pasted or picked images are kept
 | inline as base64; the server turns them into files when saving.
 */
const TINYMCE_SRC = "https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js";

type TinyEditor = { getContent: () => string; setContent: (html: string) => void; on: (event: string, cb: () => void) => void; destroy: () => void };
type TinyMce = { init: (options: Record<string, unknown>) => Promise<TinyEditor[]> };

let loader: Promise<TinyMce> | null = null;

function loadTinyMce(): Promise<TinyMce> {
    const existing = (window as unknown as { tinymce?: TinyMce }).tinymce;
    if (existing) return Promise.resolve(existing);
    loader ??= new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = TINYMCE_SRC;
        script.referrerPolicy = "origin";
        script.onload = () => resolve((window as unknown as { tinymce: TinyMce }).tinymce);
        script.onerror = () => {
            loader = null;
            reject(new Error("TinyMCE failed to load"));
        };
        document.head.appendChild(script);
    });
    return loader;
}

export function RichTextEditor({
    value,
    onChange,
    invalid,
    minHeight = 420,
    className,
}: {
    value: string;
    onChange: (html: string) => void;
    invalid?: boolean;
    minHeight?: number;
    className?: string;
}) {
    const { t } = useTranslation();
    const id = useId().replace(/:/g, "");
    const editor = useRef<TinyEditor | null>(null);
    const latest = useRef(onChange);
    const [state, setState] = useState<"loading" | "ready" | "failed">("loading");
    latest.current = onChange;

    useEffect(() => {
        let cancelled = false;
        const dark = document.documentElement.classList.contains("dark");

        loadTinyMce()
            .then((tinymce) =>
                tinymce.init({
                    selector: `#${id}`,
                    min_height: minHeight,
                    menubar: false,
                    branding: false,
                    promotion: false,
                    statusbar: false,
                    plugins: "autoresize link image lists table code media autolink",
                    toolbar:
                        "blocks | bold italic underline | bullist numlist blockquote | alignleft aligncenter alignright | link image media table | removeformat code",
                    skin: dark ? "oxide-dark" : "oxide",
                    content_css: dark ? "dark" : "default",
                    content_style: "body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 15px; line-height: 1.6 }",
                    convert_urls: false,
                    paste_data_images: true,
                    automatic_uploads: false,
                    image_title: true,
                    file_picker_types: "image",
                    file_picker_callback: (callback: (url: string, meta: { title: string }) => void) => {
                        const input = document.createElement("input");
                        input.type = "file";
                        input.accept = "image/*";
                        input.onchange = () => {
                            const file = input.files?.[0];
                            if (!file) return;
                            const reader = new FileReader();
                            reader.onload = () => callback(String(reader.result), { title: file.name });
                            reader.readAsDataURL(file);
                        };
                        input.click();
                    },
                    setup: (instance: TinyEditor) => {
                        instance.on("init", () => {
                            if (cancelled) return;
                            instance.setContent(value);
                            editor.current = instance;
                            setState("ready");
                        });
                        instance.on("change input undo redo", () => latest.current(instance.getContent()));
                    },
                }),
            )
            .catch(() => !cancelled && setState("failed"));

        return () => {
            cancelled = true;
            editor.current?.destroy();
            editor.current = null;
        };
        // Initialise once; later value changes come from the editor itself
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <div className={cn("relative overflow-hidden rounded-md border", invalid && "border-destructive", className)}>
            {state === "loading" && <Skeleton className="absolute inset-0 rounded-none" style={{ minHeight }} />}
            {state === "failed" ? (
                <div className="flex flex-col gap-2 p-3">
                    <p className="text-xs text-muted-foreground">{t("The editor couldn't load, so you can edit the HTML directly.")}</p>
                    <textarea
                        value={value}
                        onChange={(event) => onChange(event.target.value)}
                        className="min-h-80 w-full rounded-md border bg-transparent p-2 font-mono text-xs"
                    />
                </div>
            ) : (
                <textarea id={id} defaultValue={value} className="invisible" style={{ minHeight }} />
            )}
        </div>
    );
}
