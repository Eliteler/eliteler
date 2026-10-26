import { CommandSearch } from "@admin/components/command-search";
import { ThemeToggle } from "@admin/components/theme-toggle";
import { SidebarTrigger } from "@admin/components/ui/sidebar";

export function SiteHeader({ title }: { title?: string }) {
    return (
        <header data-print="hide" className="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-2 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/70">
            <div className="flex w-full min-w-0 items-center gap-2 px-4 lg:px-6">
                <SidebarTrigger className="-ms-1" />
                {title && <h1 className="hidden truncate text-base font-medium sm:block">{title}</h1>}
                <div className="ms-auto flex min-w-0 items-center gap-1">
                    <CommandSearch />
                    {/* On phones the theme toggle lives in the profile menu (nav-user) to keep the bar narrow */}
                    <div className="hidden items-center gap-1 md:flex">
                        <ThemeToggle />
                    </div>
                </div>
            </div>
        </header>
    );
}
