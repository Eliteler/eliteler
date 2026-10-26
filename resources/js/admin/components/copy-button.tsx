import { Check, Copy } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { toast } from "@admin/lib/toast";

export function CopyButton({
    value,
    label,
    size = "icon-sm",
    variant = "ghost",
}: {
    value: string;
    label?: string;
    size?: "icon-sm" | "sm";
    variant?: "ghost" | "outline";
}) {
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(value);
            setCopied(true);
            toast.success(t("Copied to clipboard"));
            window.setTimeout(() => setCopied(false), 1500);
        } catch {
            toast.error(t("Could not copy. Select the text and copy it manually."));
        }
    };

    return (
        <Button type="button" variant={variant} size={size} onClick={copy} aria-label={label ?? t("Copy")}>
            {copied ? <Check className="text-success" /> : <Copy />}
            {size === "sm" && (label ?? t("Copy"))}
        </Button>
    );
}
