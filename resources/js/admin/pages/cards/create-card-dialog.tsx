import { Loader2, Plus } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";

import { CustomerPicker, type PickedCustomer } from "../customers/components/customer-picker";

/**
 * Start creating a business card or store from the admin panel: pick the customer
 * who will own it (or the admin's own account) and continue in the user-panel
 * builder. The admin stays signed in as themselves (see AdminCardContext middleware).
 */
export function CreateCardDialog({
    open,
    type,
    onClose,
}: {
    open: boolean;
    type: "vcard" | "store";
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const [target, setTarget] = useState<PickedCustomer | null>(null);
    const [leaving, setLeaving] = useState(false);
    const isStore = type === "store";

    const close = () => {
        setTarget(null);
        onClose();
    };

    // Full page load on purpose: the builder is a user-panel (Blade) page.
    const go = (customer?: string) => {
        setLeaving(true);
        window.location.href = route("admin.cards.new", customer ? { type, customer } : { type });
    };

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && close()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{isStore ? t("Create store") : t("Create business card")}</DialogTitle>
                    <DialogDescription>
                        {t(
                            "Choose the customer who will own it. You continue in the builder on their behalf, without signing in as them.",
                        )}
                    </DialogDescription>
                </DialogHeader>
                <CustomerPicker value={target} onChange={setTarget} />
                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <Button type="button" variant="ghost" size="sm" disabled={leaving} onClick={() => go()}>
                        {t("Create in my own account")}
                    </Button>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={close} disabled={leaving}>
                            {t("Cancel")}
                        </Button>
                        <Button type="button" disabled={!target || leaving} onClick={() => target && go(target.id)}>
                            {leaving ? <Loader2 className="animate-spin" /> : <Plus />}
                            {t("Continue")}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
