import { ChevronLeft, ChevronRight, ImagePlus, X } from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { cn } from "@admin/lib/utils";

/** A gallery photo: already saved (path + url) or a new upload (file). */
export type PhotoItem = {
    key: string;
    path?: string;
    url?: string;
    file?: File;
};

let counter = 0;
export const photoKey = () => `photo-${Date.now()}-${counter++}`;

/** Server payload: existing photos by path, new ones as files, in order. */
export const photosPayload = (items: PhotoItem[]) =>
    items.map((item) => (item.file ? { file: item.file } : { path: item.path }));

/**
 * Ordered photo gallery: add several images, remove, and reorder with the
 * arrows or by dragging. The first photo is the cover.
 */
export function MultiImageInput({
    value,
    onChange,
    max = 8,
    maxKb,
    invalid,
    className,
}: {
    value: PhotoItem[];
    onChange: (items: PhotoItem[]) => void;
    max?: number;
    maxKb?: number;
    invalid?: boolean;
    className?: string;
}) {
    const { t } = useTranslation();
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragging, setDragging] = useState<number | null>(null);
    const [tooBig, setTooBig] = useState<string[]>([]);

    // Previews for new files
    const previews = useMemo(
        () =>
            Object.fromEntries(
                value
                    .filter((item) => item.file)
                    .map((item) => [item.key, URL.createObjectURL(item.file as File)]),
            ) as Record<string, string>,
        [value],
    );
    useEffect(
        () => () => Object.values(previews).forEach((url) => URL.revokeObjectURL(url)),
        [previews],
    );

    const add = (files: FileList | null) => {
        if (!files) return;
        const room = max - value.length;
        const big: string[] = [];
        const added = Array.from(files)
            .filter((file) => {
                if (maxKb && file.size / 1024 > maxKb) {
                    big.push(file.name);
                    return false;
                }
                return true;
            })
            .slice(0, Math.max(0, room))
            .map((file) => ({ key: photoKey(), file }));
        setTooBig(big);
        if (added.length) onChange([...value, ...added]);
        if (inputRef.current) inputRef.current.value = "";
    };

    const move = (from: number, to: number) => {
        if (to < 0 || to >= value.length || from === to) return;
        const next = [...value];
        const [item] = next.splice(from, 1);
        next.splice(to, 0, item);
        onChange(next);
    };

    return (
        <div className={cn("flex flex-col gap-2", className)}>
            <div
                className={cn(
                    "grid grid-cols-3 gap-2 sm:grid-cols-4",
                    invalid && "rounded-lg ring-1 ring-destructive",
                )}
            >
                {value.map((item, index) => (
                    <div
                        key={item.key}
                        draggable
                        onDragStart={() => setDragging(index)}
                        onDragEnd={() => setDragging(null)}
                        onDragOver={(event) => event.preventDefault()}
                        onDrop={(event) => {
                            event.preventDefault();
                            if (dragging !== null) move(dragging, index);
                            setDragging(null);
                        }}
                        className={cn(
                            "group relative aspect-square cursor-grab overflow-hidden rounded-lg border bg-muted",
                            dragging === index && "opacity-50",
                        )}
                    >
                        <img
                            src={item.file ? previews[item.key] : item.url}
                            alt=""
                            className="size-full object-cover"
                        />
                        {index === 0 && (
                            <Badge className="absolute start-1.5 top-1.5">
                                {t("Cover")}
                            </Badge>
                        )}
                        <Button
                            type="button"
                            variant="secondary"
                            size="icon-sm"
                            className="absolute end-1.5 top-1.5 size-6 opacity-90"
                            aria-label={t("Remove photo")}
                            onClick={() =>
                                onChange(value.filter((_, i) => i !== index))
                            }
                        >
                            <X className="size-3.5" />
                        </Button>
                        <div className="absolute inset-x-1.5 bottom-1.5 flex justify-between opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100">
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon-sm"
                                className="size-6"
                                aria-label={t("Move left")}
                                disabled={index === 0}
                                onClick={() => move(index, index - 1)}
                            >
                                <ChevronLeft className="size-3.5 rtl:rotate-180" />
                            </Button>
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon-sm"
                                className="size-6"
                                aria-label={t("Move right")}
                                disabled={index === value.length - 1}
                                onClick={() => move(index, index + 1)}
                            >
                                <ChevronRight className="size-3.5 rtl:rotate-180" />
                            </Button>
                        </div>
                    </div>
                ))}
                {value.length < max && (
                    <button
                        type="button"
                        onClick={() => inputRef.current?.click()}
                        className="flex aspect-square flex-col items-center justify-center gap-1 rounded-lg border border-dashed text-xs text-muted-foreground transition-colors hover:bg-muted/50 hover:text-foreground"
                    >
                        <ImagePlus className="size-5" />
                        {t("Add photos")}
                    </button>
                )}
            </div>
            <input
                ref={inputRef}
                type="file"
                multiple
                accept=".jpeg,.jpg,.png,.webp"
                className="hidden"
                onChange={(event) => add(event.target.files)}
            />
            <p className="text-xs text-muted-foreground">
                {t("JPG, PNG or WebP")}
                {maxKb ? ` · ${t("max")} ${Math.round(maxKb / 1024)} MB ${t("each")}` : ""}
                {` · ${value.length}/${max}`}
                {" · "}
                {t("Drag or use the arrows to reorder. The first photo is the cover.")}
            </p>
            {tooBig.length > 0 && (
                <p className="text-xs text-destructive">
                    {t("Too large, not added:")} {tooBig.join(", ")}
                </p>
            )}
        </div>
    );
}
