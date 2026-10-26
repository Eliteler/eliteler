import { router, usePage } from "@inertiajs/react";
import { useCallback, useEffect } from "react";

import { applyAppearance } from "@admin/lib/theme";
import type { Appearance } from "@admin/types";

/**
 * Dark/light mode stored in users.choosed_theme via the existing
 * admin.change.theme route.
 */
export function useAppearance() {
    const { ui } = usePage().props;

    // Keep <html> in sync with the server value (e.g. if the change was rejected in demo mode).
    useEffect(() => applyAppearance(ui.appearance), [ui.appearance]);

    const setAppearance = useCallback((appearance: Appearance) => {
        applyAppearance(appearance);
        router.get(route("admin.change.theme", appearance), {}, { preserveScroll: true, preserveState: true });
    }, []);

    return { appearance: ui.appearance, setAppearance };
}
