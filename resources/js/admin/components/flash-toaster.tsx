import { router } from "@inertiajs/react";
import { useEffect } from "react";
import { Toaster } from "react-hot-toast";

import { toast } from "@admin/lib/toast";
import type { SharedProps } from "@admin/types";

function showFlash(flash: SharedProps["flash"] | undefined) {
    if (flash?.success) toast.success(flash.success);
    if (flash?.failed) toast.error(flash.failed);
}

/**
 * Shows the session flashes used across admin controllers
 * (->with('success', ...) / ->with('failed', ...)) as toasts.
 */
export function FlashToaster({ initialFlash, dir }: { initialFlash: SharedProps["flash"]; dir: "ltr" | "rtl" }) {
    useEffect(() => {
        showFlash(initialFlash);

        return router.on("success", (event) => {
            showFlash((event.detail.page.props as unknown as SharedProps).flash);
        });
        // Initial flash only once; later flashes arrive through router events.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <Toaster
            position={dir === "rtl" ? "top-left" : "top-right"}
            gutter={8}
            toastOptions={{
                duration: 4000,
                className:
                    "!bg-popover !text-popover-foreground !border !border-border !shadow-lg !rounded-lg !text-sm !px-3 !py-2",
                success: { iconTheme: { primary: "var(--success)", secondary: "var(--popover)" } },
                error: { iconTheme: { primary: "var(--destructive)", secondary: "var(--popover)" } },
            }}
        />
    );
}
