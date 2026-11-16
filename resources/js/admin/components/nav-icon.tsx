import {
    ArrowLeftRight,
    Bell,
    Circle,
    Gift,
    Globe,
    LayoutDashboard,
    type LucideIcon,
    Mail,
    Nfc,
    Package,
    Palette,
    Puzzle,
    Settings,
    Smartphone,
    Sparkles,
    TicketPercent,
    UserRound,
    Users,
    Wallet,
} from "lucide-react";

import { cn } from "@admin/lib/utils";

// Icon names come from App\Support\Admin\AdminNavigation.
const ICONS: Record<string, LucideIcon> = {
    "layout-dashboard": LayoutDashboard,
    palette: Palette,
    users: Users,
    package: Package,
    nfc: Nfc,
    sparkles: Sparkles,
    globe: Globe,
    gift: Gift,
    "ticket-percent": TicketPercent,
    mail: Mail,
    bell: Bell,
    "arrow-left-right": ArrowLeftRight,
    smartphone: Smartphone,
    wallet: Wallet,
    puzzle: Puzzle,
    settings: Settings,
    "user-round": UserRound,
};

export function NavIcon({ name, svg, className }: { name: string | null; svg?: string | null; className?: string }) {
    // Plugin entries bring their own SVG (sanitised on the server)
    if (svg) {
        return <span className={cn("inline-flex size-4 shrink-0 [&>svg]:size-full", className)} aria-hidden dangerouslySetInnerHTML={{ __html: svg }} />;
    }

    const Icon = (name && ICONS[name]) || Circle;
    return <Icon className={className} />;
}
