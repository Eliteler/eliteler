import type { Appearance } from "@admin/types";

/*
 | Accent colours available to the admin panel (shadcn themes).
 */
export const ACCENTS = [
    { value: "neutral", label: "Neutral", swatch: "oklch(0.205 0 0)" },
    { value: "blue", label: "Blue", swatch: "oklch(0.488 0.243 264.376)" },
    { value: "green", label: "Green", swatch: "oklch(0.648 0.2 131.684)" },
    { value: "orange", label: "Orange", swatch: "oklch(0.646 0.222 41.116)" },
    { value: "red", label: "Red", swatch: "oklch(0.577 0.245 27.325)" },
    { value: "rose", label: "Rose", swatch: "oklch(0.586 0.253 17.585)" },
    { value: "violet", label: "Violet", swatch: "oklch(0.541 0.281 293.009)" },
    { value: "yellow", label: "Yellow", swatch: "oklch(0.795 0.184 86.047)" },
] as const;

export type Accent = (typeof ACCENTS)[number]["value"];

export const ACCENT_STORAGE_KEY = "gobiz.admin.accent";

const isAccent = (value: unknown): value is Accent =>
    ACCENTS.some((accent) => accent.value === value);

// Read current accent directly from the server-rendered HTML dataset attribute
export function storedAccent(): Accent | null {
    if (typeof document !== "undefined") {
        const domAccent = document.documentElement.dataset.accent;
        if (isAccent(domAccent)) {
            return domAccent;
        }
    }
    return null;
}

// Apply accent directly to the HTML tag
export function applyAccent(accent: string) {
    if (typeof document !== "undefined") {
        document.documentElement.dataset.accent = isAccent(accent)
            ? accent
            : "blue";
    }
}

// Apply accent instantly for live preview while picking colors
export function saveAccent(accent: Accent) {
    applyAccent(accent);

    // Clean up any stale localStorage override so database config takes priority
    try {
        window.localStorage.removeItem(ACCENT_STORAGE_KEY);
    } catch {
        // Ignore storage errors in private browsing
    }
}

export function applyAppearance(appearance: Appearance) {
    if (typeof document !== "undefined") {
        document.documentElement.classList.toggle(
            "dark",
            appearance === "dark",
        );
    }
}
