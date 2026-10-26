import { usePage } from "@inertiajs/react";
import { useState, type ReactNode } from "react";

import { AppSidebar } from "@admin/components/app-sidebar";
import { ClearCacheDialog } from "@admin/components/clear-cache-dialog";
import { FlashToaster } from "@admin/components/flash-toaster";
import { SiteHeader } from "@admin/components/site-header";
import { SidebarInset, SidebarProvider } from "@admin/components/ui/sidebar";

export default function AdminLayout({
    title,
    children,
}: {
    title?: string;
    children: ReactNode;
}) {
    const { app, locale, flash } = usePage().props;
    const [initialFlash] = useState(flash);

    return (
        <SidebarProvider className="min-h-0">
            <AppSidebar side={locale.dir === "rtl" ? "right" : "left"} />
            {/* Use flex column and min-h-screen to ensure full viewport height */}
            <SidebarInset className="flex min-h-screen flex-col overflow-x-clip">
                <SiteHeader title={title} />
                {/* footer to the bottom */}
                <div className="flex min-w-0 flex-1 flex-col overflow-x-clip">
                    {children}
                </div>
                {/* footer */}
                <footer
                    data-print="hide"
                    className="mt-auto border-t bg-background px-4 py-5 text-sm text-muted-foreground lg:px-6"
                >
                    <div className="flex flex-col items-center justify-between gap-2 sm:flex-row">
                        <p>
                            Copyright © {new Date().getFullYear()} {app.name}.
                            All rights reserved.
                        </p>
                        {app.version && (
                            <span className="rounded-md bg-muted px-2 py-0.5 font-medium text-foreground">
                                v{app.version}
                            </span>
                        )}
                    </div>
                </footer>
            </SidebarInset>
            <ClearCacheDialog />
            <FlashToaster initialFlash={initialFlash} dir={locale.dir} />
        </SidebarProvider>
    );
}
