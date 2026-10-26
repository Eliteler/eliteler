import { ImageUp, X } from "lucide-react";
import { useEffect, useId, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { cn } from "@admin/lib/utils";

/**
 * Image picker with preview of the current or newly selected file.
 * fit="contain" shows the whole image (logos, icons); "cover" fills the box.
 */
export function ImageInput({
    value,
    currentUrl,
    onChange,
    maxKb,
    invalid,
    className,
    previewClassName,
    fit = "cover",
}: {
    value: File | null;
    currentUrl?: string | null;
    onChange: (file: File | null) => void;
    maxKb?: number;
    invalid?: boolean;
    className?: string;
    previewClassName?: string;
    fit?: "cover" | "contain";
}) {
    const { t } = useTranslation();
    const id = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);

    useEffect(() => {
        if (!value) {
            setPreview(null);
            return;
        }
        const url = URL.createObjectURL(value);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [value]);

    const src = preview ?? currentUrl ?? null;

    return (
        <div className={cn("flex items-start gap-4", className)}>
            <div
                className={cn(
                    "flex aspect-[1/2] w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted",
                    invalid && "border-destructive",
                    previewClassName,
                )}
            >
                {src ? (
                    <img src={src} alt="" className={cn("size-full", fit === "contain" ? "object-contain p-2" : "object-cover object-top")} />
                ) : (
                    <ImageUp className="size-6 text-muted-foreground" />
                )}
            </div>
            <div className="flex flex-col gap-2">
                <input
                    id={id}
                    ref={inputRef}
                    type="file"
                    accept=".jpeg,.jpg,.png,.gif,.svg,.webp"
                    className="hidden"
                    onChange={(event) => onChange(event.target.files?.[0] ?? null)}
                />
                <div className="flex gap-2">
                    <Button type="button" variant="outline" size="sm" onClick={() => inputRef.current?.click()}>
                        <ImageUp />
                        {src ? t("Replace image") : t("Upload image")}
                    </Button>
                    {value && (
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            onClick={() => {
                                onChange(null);
                                if (inputRef.current) inputRef.current.value = "";
                            }}
                        >
                            <X />
                            {t("Undo")}
                        </Button>
                    )}
                </div>
                <p className="text-xs text-muted-foreground">
                    {value ? value.name.length > 10 ? value.name.slice(0, 10) + "..." : value.name : t("JPG, PNG, GIF, SVG or WebP")}
                    {maxKb ? ` · ${t("max")} ${Math.round(maxKb / 1024)} MB` : ""}
                </p>
            </div>
        </div>
    );
}
