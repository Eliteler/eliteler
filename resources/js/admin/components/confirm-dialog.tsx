import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";

export function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel,
    destructive = false,
    processing = false,
    onConfirm,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    confirmLabel?: string;
    destructive?: boolean;
    processing?: boolean;
    onConfirm: () => void;
}) {
    const { t } = useTranslation();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>
                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
                        {t("Cancel")}
                    </Button>
                    <Button variant={destructive ? "destructive" : "default"} onClick={onConfirm} disabled={processing}>
                        {confirmLabel ?? t("Confirm")}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
