import {
    Check,
    ChevronsUpDown,
    Globe,
    Languages,
    LogOut,
    Moon,
    Search,
    Sun,
    UserRound,
} from "lucide-react";
import { useMemo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { Link, usePage } from "@inertiajs/react";

import { LogoutForm } from "@admin/components/logout-form";
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from "@admin/components/ui/avatar";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from "@admin/components/ui/sidebar";
import { useAppearance } from "@admin/hooks/use-appearance";
import { changeLanguage } from "@admin/lib/language";
import { cn } from "@admin/lib/utils";

export function initials(name: string) {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join("");
}

export function NavUser() {
    const { t } = useTranslation();
    const { isMobile, setOpenMobile } = useSidebar();
    const { auth, locale } = usePage().props;
    const { appearance, setAppearance } = useAppearance();
    const [langModalOpen, setLangModalOpen] = useState(false);
    const [langSearch, setLangSearch] = useState("");
    const logoutRef = useRef<HTMLFormElement>(null);
    const user = auth.user;

    if (!user) return null;

    // Filter languages based on search query
    const filteredLanguages = useMemo(() => {
        const query = langSearch.trim().toLowerCase();
        if (!query) return locale.languages;
        return locale.languages.filter(
            (lang) =>
                lang.name.toLowerCase().includes(query) ||
                lang.code.toLowerCase().includes(query),
        );
    }, [locale.languages, langSearch]);

    // Handle language selection and close both modal and mobile drawer
    const handleSelectLanguage = (code: string) => {
        setLangModalOpen(false);
        if (isMobile) {
            setOpenMobile(false);
        }
        changeLanguage(code);
    };

    return (
        <>
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <SidebarMenuButton
                                size="lg"
                                className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            >
                                <Avatar className="size-8 rounded-lg">
                                    <AvatarImage
                                        src={user.avatar}
                                        alt={user.name}
                                    />
                                    <AvatarFallback className="rounded-lg">
                                        {initials(user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="grid flex-1 text-start text-sm leading-tight">
                                    <span className="truncate font-medium">
                                        {user.name}
                                    </span>
                                    <span className="truncate text-xs text-muted-foreground">
                                        {user.role}
                                    </span>
                                </div>
                                <ChevronsUpDown className="ms-auto size-4" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                            side={isMobile ? "bottom" : "right"}
                            align="end"
                            sideOffset={4}
                        >
                            <DropdownMenuLabel className="p-0 font-normal">
                                <div className="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <Avatar className="size-8 rounded-lg">
                                        <AvatarImage
                                            src={user.avatar}
                                            alt={user.name}
                                        />
                                        <AvatarFallback className="rounded-lg">
                                            {initials(user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div className="grid flex-1 text-start text-sm leading-tight">
                                        <span className="truncate font-medium">
                                            {user.name}
                                        </span>
                                        <span className="truncate text-xs text-muted-foreground">
                                            {user.email}
                                        </span>
                                    </div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuGroup>
                                <DropdownMenuItem asChild>
                                    <Link href={route("admin.account")}>
                                        <UserRound />
                                        {t("Profile & account")}
                                    </Link>
                                </DropdownMenuItem>

                                {/* Trigger Language Modal */}
                                {locale.languages.length > 1 && (
                                    <DropdownMenuItem
                                        onSelect={(e) => {
                                            e.preventDefault();
                                            setLangSearch("");
                                            setLangModalOpen(true);
                                        }}
                                    >
                                        <Languages />
                                        <span>{t("Language")}</span>
                                        <span className="ms-auto rounded bg-muted px-1.5 py-0.5 text-[10px] font-semibold uppercase text-muted-foreground">
                                            {locale.current}
                                        </span>
                                    </DropdownMenuItem>
                                )}

                                <DropdownMenuItem
                                    onSelect={() =>
                                        setAppearance(
                                            appearance === "dark"
                                                ? "light"
                                                : "dark",
                                        )
                                    }
                                >
                                    {appearance === "dark" ? <Sun /> : <Moon />}
                                    {appearance === "dark"
                                        ? t("Light mode")
                                        : t("Dark mode")}
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                variant="destructive"
                                onSelect={() => logoutRef.current?.submit()}
                            >
                                <LogOut />
                                {t("Logout")}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <LogoutForm ref={logoutRef} />
                </SidebarMenuItem>
            </SidebarMenu>

            {/* Language Selection Modal */}
            <Dialog open={langModalOpen} onOpenChange={setLangModalOpen}>
                <DialogContent
                    className="z-50 max-w-md gap-0 p-0 overflow-hidden sm:rounded-xl"
                    onPointerDownOutside={(e) => e.stopPropagation()}
                >
                    <DialogHeader className="p-5 pb-3">
                        <div className="flex items-center gap-2">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <Globe className="size-5" />
                            </div>
                            <div>
                                <DialogTitle className="text-base font-semibold">
                                    {t("Select Language")}
                                </DialogTitle>
                                <DialogDescription className="text-xs">
                                    {t(
                                        "Choose your preferred language for the interface.",
                                    )}
                                </DialogDescription>
                            </div>
                        </div>
                    </DialogHeader>

                    {/* Search Field */}
                    <div className="px-5 pb-3">
                        <div className="relative">
                            <Search className="absolute left-3 top-2.5 size-4 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder={t(
                                    "Search language by name or code...",
                                )}
                                value={langSearch}
                                onChange={(e) => setLangSearch(e.target.value)}
                                className="h-9 pl-9 text-xs"
                                autoFocus
                            />
                        </div>
                    </div>

                    <div className="border-t" />

                    {/* Language Cards Grid */}
                    <div className="max-h-80 overflow-y-auto p-3">
                        {filteredLanguages.length === 0 ? (
                            <div className="py-8 text-center text-xs text-muted-foreground">
                                {t("No languages match your search.")}
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                                {filteredLanguages.map((language) => {
                                    const isCurrent =
                                        language.code === locale.current;
                                    return (
                                        <button
                                            key={language.code}
                                            type="button"
                                            onClick={() =>
                                                handleSelectLanguage(
                                                    language.code,
                                                )
                                            }
                                            className={cn(
                                                "group flex items-center justify-between rounded-lg border p-2.5 text-start transition-all",
                                                isCurrent
                                                    ? "border-primary/50 bg-primary/5 text-primary shadow-xs"
                                                    : "border-border/60 hover:border-border hover:bg-muted/50 text-foreground",
                                            )}
                                        >
                                            <div className="flex items-center gap-2.5 min-w-0">
                                                {language.flag && (
                                                    <span
                                                        className={`fi fi-${language.flag} shrink-0 text-base rounded-xs`}
                                                    />
                                                )}
                                                <div className="flex flex-col min-w-0">
                                                    <span className="truncate text-xs font-medium">
                                                        {language.name}
                                                    </span>
                                                    <span className="text-[10px] text-muted-foreground uppercase font-mono">
                                                        {language.code}
                                                    </span>
                                                </div>
                                            </div>
                                            {isCurrent && (
                                                <Check className="size-4 shrink-0 text-primary" />
                                            )}
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
