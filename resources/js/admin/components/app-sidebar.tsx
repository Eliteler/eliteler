import { Link, usePage } from "@inertiajs/react";
import * as React from "react";

import { NavMain } from "@admin/components/nav-main";
import { NavUser } from "@admin/components/nav-user";
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarRail } from "@admin/components/ui/sidebar";

export function AppSidebar(props: React.ComponentProps<typeof Sidebar>) {
    const { app, navigation, ui } = usePage().props;

    return (
        <Sidebar collapsible="icon" {...props}>
            <SidebarHeader className="h-14 justify-center border-b border-sidebar-border px-4 group-data-[collapsible=icon]:px-2">
                <Link href={route("admin.dashboard")} className="flex items-center">
                    <img
                        src={ui.appearance === "dark" ? app.logo_dark : app.logo}
                        alt={app.name}
                        className="h-8 w-auto max-w-[170px] object-contain group-data-[collapsible=icon]:hidden"
                    />
                    <img
                        src={app.favicon}
                        alt={app.name}
                        className="hidden size-8 rounded-md object-contain group-data-[collapsible=icon]:block"
                    />
                </Link>
            </SidebarHeader>
            <SidebarContent className="py-2">
                <NavMain sections={navigation} />
            </SidebarContent>
            <SidebarFooter className="border-t border-sidebar-border">
                <NavUser />
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
    );
}
