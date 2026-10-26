import { usePage } from "@inertiajs/react";

/**
 * True when the signed-in admin has "Hide revenue" on. The server already
 * leaves amounts out (null); this only drops the empty columns and labels.
 */
export function useHideRevenue() {
    return usePage().props.permissions.hide_revenue === true;
}
