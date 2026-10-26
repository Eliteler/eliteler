import { router } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";

import { CustomerPicker, type PickedCustomer } from "./customer-picker";

/**
 * Duplicate a business card or store (with its content) into another
 * customer's account, within that customer's plan limits.
 */
export function CopyCardDialog({
    card,
    ownerId,
    onClose,
}: {
    card: { id: string; title: string; type: "vcard" | "store" } | null;
    ownerId: string;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const [target, setTarget] = useState<PickedCustomer | null>(null);
    const [processing, setProcessing] = useState(false);

    const close = () => {
        setTarget(null);
        onClose();
    };

    const submit = () => {
        if (!card || !target) return;
        router.post(
            route("admin.assign.card"),
            { card_id: card.id, customer_id: target.id },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: close,
            },
        );
    };

    return (
        <Dialog open={card !== null} onOpenChange={(open) => !open && close()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{card?.type === "store" ? t("Copy store") : t("Copy business card")}</DialogTitle>
                    <DialogDescription>
                        {t("Creates a copy of “:title” with its content in another customer's account.").replace(
                            ":title",
                            card?.title ?? "",
                        )}
                    </DialogDescription>
                </DialogHeader>
                <CustomerPicker value={target} onChange={setTarget} exclude={ownerId} />
                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span className="truncate text-sm text-muted-foreground">
                        {target ? `${t("Copy to")} ${target.name}` : t("Choose a customer")}
                    </span>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={close} disabled={processing}>
                            {t("Cancel")}
                        </Button>
                        <Button onClick={submit} disabled={!target || processing}>
                            {processing && <Loader2 className="animate-spin" />}
                            {t("Copy")}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
