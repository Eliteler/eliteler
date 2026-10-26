import type { route as routeFn } from "ziggy-js";

import type { SharedProps } from "@admin/types";

declare global {
    // Provided by the @routes Blade directive (Ziggy).
    var route: typeof routeFn;
}

declare module "@inertiajs/core" {
    interface PageProps extends SharedProps {}
}
