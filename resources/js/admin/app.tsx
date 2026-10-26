import "../../css/admin.css";
// Must run before any plugin page loads: plugins read React & components from it
import "@admin/plugin-runtime";

import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";
import { I18nextProvider } from "react-i18next";

import AdminLayout from "@admin/layouts/admin-layout";
import { setupI18n } from "@admin/lib/i18n";
import { applyAccent, applyAppearance, storedAccent } from "@admin/lib/theme";
import type { SharedProps } from "@admin/types";

type PageModule = {
    default: React.ComponentType & { layout?: (page: React.ReactNode) => React.ReactNode };
};

// Pages: pages/<module>/<page>.tsx, resolved as Inertia::render('<module>/<page>').
const pages = import.meta.glob<PageModule>(["./pages/**/*.tsx", "!./pages/**/components/**", "!./pages/**/types.ts"]);

function withLayout(page: PageModule) {
    page.default.layout ??= (children: React.ReactNode) => <AdminLayout>{children}</AdminLayout>;
    return page;
}

/**
 * Plugin and web template pages are prebuilt into <plugins|templates>/<Name>/dist and named
 * "plugin:<Name>@<version>/<page>" or "template:<Name>@<version>/<page>" by App\Support\Admin\PluginPage.
 */
async function loadPluginPage(name: string): Promise<PageModule> {
    const [, kind, plugin, version, page] = name.match(/^(plugin|template):([\w-]+)@([\w.-]+)\/([\w-]+)$/) ?? [];
    const assetRoute = kind === "template" ? "admin.template.assets" : "admin.plugin.assets";
    const asset = (file: string) => `${route(assetRoute, { plugin, path: file })}?v=${version}`;

    try {
        const href = asset("style.css");
        const key = `${kind}:${plugin}`;
        if (!document.querySelector(`link[data-plugin="${key}"][href="${href}"]`)) {
            // Drop this page's stylesheet from an older build, if any
            document.querySelectorAll(`link[data-plugin="${key}"]`).forEach((old) => old.remove());
            const link = Object.assign(document.createElement("link"), { rel: "stylesheet", href });
            link.dataset.plugin = key;
            document.head.appendChild(link);
        }
        return (await import(/* @vite-ignore */ asset(`${page}.js`))) as PageModule;
    } catch (error) {
        console.error(`Plugin page failed to load: ${name}`, error);
        return pages["./pages/plugins/unavailable.tsx"]() as Promise<PageModule>;
    }
}

// Server-rendered <title> holds the app name before Inertia takes over.
const appName = document.title;

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        if (name.startsWith("plugin:") || name.startsWith("template:")) {
            return withLayout(await loadPluginPage(name));
        }

        const importPage = pages[`./pages/${name}.tsx`];
        if (!importPage) {
            throw new Error(`Admin page not found: ${name}`);
        }

        return withLayout(await importPage());
    },
    async setup({ el, App, props }) {
        const shared = props.initialPage.props as unknown as SharedProps;

        applyAccent(storedAccent() ?? shared.ui.accent);
        applyAppearance(shared.ui.appearance);

        const i18n = await setupI18n(shared.locale.current);

        createRoot(el).render(
            <I18nextProvider i18n={i18n}>
                <App {...props} />
            </I18nextProvider>,
        );
    },
    progress: {
        color: "var(--primary)",
    },
});
