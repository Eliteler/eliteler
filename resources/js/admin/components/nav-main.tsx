import { ChevronRight } from "lucide-react";

import { NavIcon } from "@admin/components/nav-icon";
import { NavLink } from "@admin/components/nav-link";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@admin/components/ui/collapsible";
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from "@admin/components/ui/sidebar";
import type { NavEntry, NavGroup, NavSection } from "@admin/types";

const isGroup = (entry: NavEntry): entry is NavGroup => "items" in entry;

export function NavMain({ sections }: { sections: NavSection[] }) {
    return (
        <>
            {sections.map((section) => (
                <SidebarGroup key={section.title} className="py-1">
                    <SidebarGroupLabel className="text-[0.68rem] tracking-wider uppercase">{section.title}</SidebarGroupLabel>
                    <SidebarMenu>
                        {section.items.map((entry) =>
                            isGroup(entry) ? (
                                <Collapsible key={entry.title} asChild defaultOpen={entry.isActive} className="group/collapsible">
                                    <SidebarMenuItem>
                                        <CollapsibleTrigger asChild>
                                            <SidebarMenuButton tooltip={entry.title} isActive={entry.isActive}>
                                                <NavIcon name={entry.icon} />
                                                <span>{entry.title}</span>
                                                <ChevronRight className="ms-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90 rtl:rotate-180 rtl:group-data-[state=open]/collapsible:rotate-90" />
                                            </SidebarMenuButton>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <SidebarMenuSub>
                                                {entry.items.map((item) => (
                                                    <SidebarMenuSubItem key={item.href}>
                                                        <SidebarMenuSubButton asChild isActive={item.isActive}>
                                                            <NavLink href={item.href} inertia={item.inertia} action={item.action}>
                                                                <span>{item.title}</span>
                                                            </NavLink>
                                                        </SidebarMenuSubButton>
                                                    </SidebarMenuSubItem>
                                                ))}
                                            </SidebarMenuSub>
                                        </CollapsibleContent>
                                    </SidebarMenuItem>
                                </Collapsible>
                            ) : (
                                <SidebarMenuItem key={entry.href}>
                                    <SidebarMenuButton asChild tooltip={entry.title} isActive={entry.isActive}>
                                        <NavLink href={entry.href} inertia={entry.inertia} action={entry.action}>
                                            <NavIcon name={entry.icon} />
                                            <span>{entry.title}</span>
                                        </NavLink>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ),
                        )}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
