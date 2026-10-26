import { FileArchive } from "lucide-react";
import { useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { cn } from "@admin/lib/utils";

type Props = {
    file: File | null;
    onChange: (file: File | null) => void;
    maxUploadKb: number;
    invalid?: boolean;
    disabled?: boolean;
};

const isZip = (file: File) => file.name.toLowerCase().endsWith(".zip");

// Click to choose or drag and drop a .zip file (plugin and web template installs)
export function ZipDropzone({
    file,
    onChange,
    maxUploadKb,
    invalid,
    disabled,
}: Props) {
    const { t } = useTranslation();
    const input = useRef<HTMLInputElement>(null);
    const [dragging, setDragging] = useState(false);
    const [wrongType, setWrongType] = useState(false);

    const pick = (picked: File | undefined) => {
        if (!picked) return;
        setWrongType(!isZip(picked));
        onChange(isZip(picked) ? picked : null);
    };

    const onDragOver = (event: React.DragEvent) => {
        event.preventDefault();
        if (disabled) return;
        event.dataTransfer.dropEffect = "copy";
        setDragging(true);
    };

    const onDrop = (event: React.DragEvent) => {
        event.preventDefault();
        setDragging(false);
        if (!disabled) pick(event.dataTransfer.files?.[0]);
    };

    return (
        <>
            <button
                type="button"
                onClick={() => input.current?.click()}
                onDragOver={onDragOver}
                onDragLeave={() => setDragging(false)}
                onDrop={onDrop}
                disabled={disabled}
                className={cn(
                    "flex min-h-48 flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors hover:bg-muted/50",
                    dragging && "border-primary bg-primary/5",
                    (invalid || wrongType) && !dragging && "border-destructive",
                )}
            >
                <FileArchive
                    className={cn(
                        "size-10 text-muted-foreground",
                        dragging && "text-primary",
                    )}
                />
                {file ? (
                    <span className="text-sm font-medium break-all">
                        {file.name}
                    </span>
                ) : (
                    <>
                        <span className="text-sm font-medium">
                            {dragging
                                ? t("Drop the .zip file here")
                                : t("Choose a .zip file")}
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {t("or drag and drop it here")}
                        </span>
                    </>
                )}
                <span className="text-xs text-muted-foreground">
                    {t("Up to :size KB").replace(":size", String(maxUploadKb))}
                </span>
            </button>
            <input
                ref={input}
                type="file"
                accept=".zip,application/zip"
                className="hidden"
                onChange={(event) => {
                    pick(event.target.files?.[0]);
                    event.target.value = "";
                }}
            />
            {wrongType && (
                <p className="text-sm text-destructive">
                    {t("Only .zip files can be uploaded.")}
                </p>
            )}
        </>
    );
}
