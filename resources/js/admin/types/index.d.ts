export type NavAction = "clear-cache";

export type NavLink = {
    title: string;
    href: string;
    icon: string | null;
    inertia: boolean;
    action: NavAction | null;
    isActive: boolean;
};

export type NavGroup = {
    title: string;
    icon: string;
    isActive: boolean;
    items: NavLink[];
};

export type NavEntry = NavLink | NavGroup;

export type NavSection = {
    title: string;
    items: NavEntry[];
};

export type Language = {
    code: string;
    name: string;
    flag: string | null;
};

export type CurrencySettings = {
    code: string;
    symbol: string;
    symbolFirst: boolean;
    decimals: number;
    format: string;
};

export type AdminUser = {
    id: number;
    name: string;
    email: string;
    avatar: string;
    role: string;
};

export type Appearance = "light" | "dark";

export type SharedProps = {
    app: {
        name: string;
        version: string | null;
        logo: string;
        logo_dark: string;
        favicon: string;
    };
    ui: {
        accent: string;
        appearance: Appearance;
    };
    auth: { user: AdminUser | null };
    permissions: Record<string, boolean>;
    features: {
        nfc: boolean;
        referral: boolean;
        aibuilder: boolean;
        mobile_app_api: boolean;
    };
    navigation: NavSection[];
    currency: CurrencySettings;
    locale: {
        current: string;
        dir: "ltr" | "rtl";
        languages: Language[];
    };
    flash: {
        success: string | null;
        failed: string | null;
        stock_message: string | null;
        stock_url: string | null;
        plugin_notice: PluginNotice | null;
        template_notice: PluginNotice | null;
    };
    errors: Record<string, string>;
    [key: string]: unknown;
};

/** Laravel LengthAwarePaginator serialized to JSON. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
};

/** Why an uploaded plugin zip wasn't installed. */
export type PluginNotice = {
    kind: "legacy" | "older";
    name: string;
    version: string;
    installed?: string;
};
