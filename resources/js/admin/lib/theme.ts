import type { Appearance } from "@admin/types";

/*
 | Accent colours available to the admin panel (shadcn themes). Each entry
 | except "neutral" needs [data-accent="..."] blocks in resources/css/admin.css.
 | The default comes from the `ui.accent` shared prop; an admin can pick
 | another in General settings, which is kept in this browser only
 | (localStorage) and applied before paint by the script in admin/app.blade.php.
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

/** Keep in sync with the inline script in resources/views/admin/app.blade.php. */
export const ACCENT_STORAGE_KEY = "gobiz.admin.accent";

const isAccent = (value: unknown): value is Accent => ACCENTS.some((accent) => accent.value === value);

export function storedAccent(): Accent | null {
    try {
        const value = window.localStorage.getItem(ACCENT_STORAGE_KEY);
        return isAccent(value) ? value : null;
    } catch {
        return null;
    }
}

export function applyAccent(accent: string) {
    document.documentElement.dataset.accent = isAccent(accent) ? accent : "blue";
}

/** Apply an accent and remember it in this browser. */
export function saveAccent(accent: Accent) {
    applyAccent(accent);
    try {
        window.localStorage.setItem(ACCENT_STORAGE_KEY, accent);
    } catch {
        // Storage blocked (private mode); the choice lasts until reload
    }
}

export function applyAppearance(appearance: Appearance) {
    document.documentElement.classList.toggle("dark", appearance === "dark");
}
