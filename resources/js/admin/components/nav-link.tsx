import { Link } from "@inertiajs/react";
import * as React from "react";

import { openClearCache } from "@admin/components/clear-cache-dialog";
import { useSidebar } from "@admin/components/ui/sidebar";
import { cn } from "@admin/lib/utils";
import type { NavAction } from "@admin/types";

export function NavLink({
    href,
    inertia,
    action,
    onClick,
    ...props
}: React.ComponentProps<"a"> & {
    href: string;
    inertia: boolean;
    action?: NavAction | null;
}) {
    const { isMobile, setOpenMobile } = useSidebar();

    const handleClick = (event: React.MouseEvent<Element>) => {
        onClick?.(event as React.MouseEvent<HTMLAnchorElement>);

        if (isMobile) {
            setOpenMobile(false);
        }
    };

    // Opens a dialog instead of a page (e.g. Clear App Cache)
    if (action) {
        const { className, ...rest } = props;

        return (
            <button
                {...(rest as React.ComponentProps<"button">)}
                type="button"
                onClick={(event) => {
                    handleClick(event);
                    openClearCache();
                }}
                className={cn("w-full cursor-pointer text-start", className)}
            />
        );
    }

    if (inertia) {
        return (
            <Link
                href={href}
                onClick={handleClick}
                {...(props as Omit<React.ComponentProps<typeof Link>, "href">)}
            />
        );
    }

    return <a href={href} onClick={handleClick} {...props} />;
}
