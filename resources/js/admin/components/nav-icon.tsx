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

export function NavIcon({ name, className }: { name: string | null; className?: string }) {
    const Icon = (name && ICONS[name]) || Circle;
    return <Icon className={className} />;
}
