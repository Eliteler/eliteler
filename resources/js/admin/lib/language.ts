/**
 * Switch language through the existing SetLocale middleware
 * (?change_language=xx). A full reload is needed for RTL and translations.
 */
export function changeLanguage(code: string) {
    const url = new URL(window.location.href);
    url.searchParams.set("change_language", code);
    window.location.href = url.toString();
}
