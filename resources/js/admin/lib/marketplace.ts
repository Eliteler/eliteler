/** Plugins and web templates listed on gobizsaas.com (only "published" ones are shown). */
export type MarketItem = {
    id: string;
    name: string;
    logo?: string;
    description?: string;
    url: string;
};

/** Fallback when a list can't be loaded (template pages live under /plugins too). */
export const MARKETPLACE_PAGE = "https://gobizsaas.com/plugins";

export const isWebUrl = (value: unknown): value is string => typeof value === "string" && /^https?:\/\//i.test(value);

/**
 * Published items from a gobizsaas.com list, keyed by `idKey` (plugin_id or
 * template_id). Throws when the list can't be loaded or is empty. Times out
 * quickly, so a fallback tab still counts as a click (browsers block pop-ups
 * opened long after the click).
 */
export async function loadMarketplace(url: string, idKey: "plugin_id" | "template_id"): Promise<MarketItem[]> {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), 4000);

    try {
        const response = await fetch(url, { signal: controller.signal, headers: { Accept: "application/json" } });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data: unknown = await response.json();
        const list = (Array.isArray(data) ? data : [])
            .filter((item) => item?.status === "published" && typeof item[idKey] === "string" && typeof item.name === "string" && isWebUrl(item.url))
            .map((item) => ({
                id: item[idKey] as string,
                name: item.name as string,
                logo: isWebUrl(item.logo) ? item.logo : undefined,
                description: typeof item.description === "string" ? item.description : undefined,
                url: item.url as string,
            }));
        if (list.length === 0) throw new Error("Nothing published");

        return list;
    } finally {
        window.clearTimeout(timer);
    }
}

/** Opens the marketplace website in a new tab (when its list can't be loaded). */
export function openMarketplacePage() {
    window.open(MARKETPLACE_PAGE, "_blank", "noopener,noreferrer");
}
