import i18n from "i18next";
import { initReactI18next } from "react-i18next";

/*
 | Translations reuse Laravel's JSON files (resources/lang/{locale}.json),
 | where the key is the English source text, exactly like __('...') in Blade.
 | Only the active locale is loaded; switching language is a full page load.
 */
const locales = import.meta.glob<Record<string, string>>("/resources/lang/*.json", { import: "default" });

export async function setupI18n(locale: string) {
    const loader = locales[`/resources/lang/${locale}.json`];
    const resources = loader ? await loader() : {};

    await i18n.use(initReactI18next).init({
        lng: locale,
        fallbackLng: false,
        resources: { [locale]: { translation: resources } },
        keySeparator: false,
        nsSeparator: false,
        returnEmptyString: false,
        interpolation: { escapeValue: false },
        react: { useSuspense: false },
    });

    return i18n;
}

export default i18n;
