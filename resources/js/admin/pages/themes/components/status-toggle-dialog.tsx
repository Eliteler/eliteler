import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";

/**
 * Confirmation before enabling/disabling a theme or intro, warning when
 * existing business cards use it.
 */
export function StatusToggleDialog({
    target,
    kind,
    processing,
    onCancel,
    onConfirm,
}: {
    target: { name: string; active: boolean; usage: number } | null;
    kind: "theme" | "intro";
    processing: boolean;
    onCancel: () => void;
    onConfirm: () => void;
}) {
    const { t } = useTranslation();

    const description = !target
        ? undefined
        : target.active
          ? kind === "theme"
              ? t("Customers will no longer be able to choose this theme.") +
                (target.usage > 0 ? ` ${target.usage} ${t("business cards currently use it.")}` : "")
              : t("Customers will no longer be able to choose this intro.") +
                (target.usage > 0 ? ` ${target.usage} ${t("business cards currently use it.")}` : "")
          : kind === "theme"
            ? t("Customers will be able to choose this theme again.")
            : t("Customers will be able to choose this intro again.");

    return (
        <ConfirmDialog
            open={target !== null}
            onOpenChange={(open) => !open && onCancel()}
            title={target ? `${target.active ? t("Disable") : t("Enable")} “${target.name}”?` : ""}
            description={description}
            confirmLabel={target?.active ? t("Disable") : t("Enable")}
            destructive={target?.active}
            processing={processing}
            onConfirm={onConfirm}
        />
    );
}
