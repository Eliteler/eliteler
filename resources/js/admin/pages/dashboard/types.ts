export type Period = "7d" | "30d" | "90d" | "12m";

export type Comparison = { current: number; previous: number };

export type Kpis = {
    revenue?: Comparison; // left out when revenue is hidden
    signups: Comparison;
    subscriptions: Comparison;
    activeSubscribers: { paid: number; free: number; total: number };
};

export type RevenuePoint = { label: string; plans: number; nfc: number; ai: number };

export type GrowthPoint = { label: string; signups: number; subscriptions: number };

export type Gateway = { name: string; revenue: number; payments: number };

export type Usage = Partial<Record<"vcards" | "stores" | "visits" | "enquiries" | "storeOrders" | "appointments", Comparison>>;

export type PlanMixRow = { name: string; price?: number; paid: boolean; customers: number }; // no price when revenue is hidden

export type AttentionKey = "offline_payments" | "nfc_orders" | "low_stock" | "domain_requests" | "withdrawals" | "expiring";

export type AttentionItem = { key: AttentionKey; count: number; href: string | null };

export type RecentPayment = {
    id: string;
    customer: string;
    email: string | null;
    customerId: number | null;
    plan: string;
    gateway: string;
    amount: number;
    date: string;
};

export type CustomerRow = {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    plan: string | null;
    active?: boolean;
    date?: string | null;
    expires?: string;
};

export type DashboardLinks = {
    transactions: string | null;
    customers: string | null;
    customer: string | null; // contains __ID__ placeholder
    plans: string | null;
};

export type SystemNotice = {
    updateAvailable: boolean;
    latestVersion: string;
    supportExpired: boolean;
};

export type DashboardProps = {
    period: Period;
    periods: Period[];
    hideRevenue: boolean;
    // License server status; null without the Software updates permission or when it can't be confirmed
    systemNotice: SystemNotice | null;
    kpis: Kpis;
    // Revenue and payment widgets are not sent when hideRevenue is true
    revenueSeries?: RevenuePoint[];
    growthSeries: GrowthPoint[];
    gateways?: Gateway[];
    usage: Usage;
    attention: AttentionItem[];
    planMix: PlanMixRow[];
    recentPayments?: RecentPayment[];
    recentCustomers: CustomerRow[];
    expiringSoon: CustomerRow[];
    links: DashboardLinks;
};

export const PERIOD_PROPS = ["period", "kpis", "revenueSeries", "growthSeries", "gateways", "usage"] as const;

export function customerUrl(links: DashboardLinks, id: number | null) {
    return links.customer && id ? links.customer.replace("__ID__", String(id)) : null;
}

export function change({ current, previous }: Comparison) {
    if (previous === 0) return current > 0 ? 100 : 0;
    return Math.round(((current - previous) / previous) * 1000) / 10;
}
