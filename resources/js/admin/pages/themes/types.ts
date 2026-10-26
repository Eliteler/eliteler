export type ThemeType = "vcard" | "store";

export type ThemeItem = {
    id: string;
    name: string;
    type: ThemeType;
    thumbnail: string | null;
    active: boolean;
    usage: number;
    customCode: boolean;
};

export type ThemeDetail = ThemeItem & {
    css: string;
    js: string;
    updatedAt: string | null;
};

export type IntroItem = {
    id: string;
    name: string;
    thumbnail: string | null;
    active: boolean;
    usage: number;
};

export const THEME_TYPE_LABELS: Record<ThemeType, string> = {
    vcard: "Business card",
    store: "Store",
};
