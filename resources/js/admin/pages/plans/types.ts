export type PlanType = "BOTH" | "VCARD" | "STORE";
export type PlanStatus = "active" | "disabled" | "archived";

export const VCARD_LIMITS = [
    ["no_of_vcards", "Business cards"],
    ["no_of_services", "Services"],
    ["no_of_vcard_products", "Products"],
    ["no_of_links", "Links"],
    ["no_of_payments", "Payment methods"],
    ["no_testimonials", "Testimonials"],
    ["no_of_galleries", "Gallery images"],
    ["no_of_enquires", "Enquiries"],
] as const;

export const STORE_LIMITS = [
    ["no_of_stores", "Stores"],
    ["no_of_categories", "Categories"],
    ["no_of_store_products", "Store products"],
] as const;

export const VCARD_FEATURES = [
    ["business_hours", "Business hours"],
    ["appointment", "Appointments"],
    ["service_booking", "Service booking"],
    ["contact_form", "Contact form"],
    ["password_protected", "Password protection"],
] as const;

export const COMMON_FEATURES = [
    ["custom_domain", "Custom domain"],
    ["nfc_card", "Order NFC cards"],
    ["pwa", "Installable app (PWA)"],
    ["advanced_settings", "Advanced settings"],
    ["personalized_link", "Personalized link"],
    ["hide_branding", "Hide branding"],
    ["free_setup", "Free setup"],
    ["free_support", "Free support"],
] as const;

export type LimitKey =
    | (typeof VCARD_LIMITS)[number][0]
    | (typeof STORE_LIMITS)[number][0];
export type FeatureKey =
    | (typeof VCARD_FEATURES)[number][0]
    | (typeof COMMON_FEATURES)[number][0];

export type IntegrationKey =
    | "paddle_plan_price_id"
    | "dodo_payments_plan_price_id"
    | "paypal_plan_id"
    | "in_app_purchase_type"
    | "in_app_product_id"
    | "in_app_purchase_id";

export type Plan = {
    id: string;
    type: PlanType;
    name: string;
    description: string | null;
    price: number;
    validity: number;
    trial: number;
    storage: number;
    recommended: boolean;
    private: boolean;
    status: PlanStatus;
    subscribers: number;
    aiCredits: number;
    trialAiCredits: number;
    googleWallet: boolean;
    googleWallets: number;
    limits: Record<LimitKey, number | null>;
    features: Record<FeatureKey, boolean>;
    integrations: Record<IntegrationKey, string | null>;
};

export type Plugins = {
    googleWallet: boolean;
    paddle: boolean;
    dodo: boolean;
    paypal: boolean;
    inApp: boolean;
    aiBuilder: boolean;
    nfc: boolean;
};

export const UNLIMITED = 999;
export const FOREVER = 9999;

export const includesCards = (type: PlanType) => type !== "STORE";
export const includesStores = (type: PlanType) => type !== "VCARD";

export const TYPE_LABELS: Record<PlanType, string> = {
    BOTH: "Cards + stores",
    VCARD: "Business cards",
    STORE: "Stores",
};

/** "per month", "per year", "forever", "for 45 days" */
export function periodLabel(validity: number, t: (key: string) => string) {
    if (validity >= FOREVER) return t("forever");
    if (validity === 30 || validity === 31) return t("per month");
    if (validity === 365 || validity === 366) return t("per year");
    return `${t("for")} ${validity} ${t("days")}`;
}

export function limitLabel(value: number | null, t: (key: string) => string) {
    if (value === null) return "—";
    return value >= UNLIMITED ? t("Unlimited") : value.toLocaleString();
}
