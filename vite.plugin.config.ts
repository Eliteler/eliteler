import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import { existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { defineConfig, type Plugin } from "vite";

/*
 | Builds one plugin's React pages: npm run plugin:build -- <Name>
 | (scripts/plugin-build.mjs runs one pass per target, admin first: it empties dist/)
 |
 | PLUGIN_TARGET=admin (default)
 |   plugins/<Name>/resources/js/<page>.tsx          →  dist/<page>.js, style.css, manifest.json
 |   React, Inertia, react-i18next and "@gobiz/admin" are NOT bundled: they are read from
 |   window.GoBizAdmin, which the core admin provides (resources/js/admin/plugin-runtime.ts).
 |
 | PLUGIN_TARGET=user: pages of the React customer panel (App\Support\Plugins\UserPluginPage)
 |   plugins/<Name>/resources/js/user/<page>.tsx     →  dist/user/<page>.js, style.css, manifest.json
 |   Same idea with window.GoBizUser ("@gobiz/user", ziggy-js), provided by ModernDashboard.
 |
 | PLUGIN_TARGET=widgets: self-contained scripts for both customer panels (plugin.json "widgets")
 |   plugins/<Name>/resources/js/widgets/<name>.ts   →  dist/widgets/<name>.js (+ <name>.css)
 |   Nothing is shared: a widget bundles what it uses and must work in the Blade panel too.
 |
 | dist/ ships inside the plugin zip; customers' servers never build anything.
 */
const root = dirname(fileURLToPath(import.meta.url));
const plugin = process.env.PLUGIN ?? "";
// plugins/<Name> (plugin.json) or templates/<Name> (template.json), set by scripts/plugin-build.mjs
const base = process.env.PLUGIN_BASE === "templates" ? "templates" : "plugins";
const target = process.env.PLUGIN_TARGET === "user" || process.env.PLUGIN_TARGET === "widgets" ? process.env.PLUGIN_TARGET : "admin";
const pluginDir = resolve(root, base, plugin);
const sourceDir = resolve(pluginDir, "resources/js", target === "admin" ? "" : target);
const outDir = resolve(pluginDir, "dist", target === "admin" ? "" : target);

if (!/^[A-Za-z0-9_-]+$/.test(plugin) || !existsSync(sourceDir)) {
    throw new Error(`Usage: npm run plugin:build -- <PluginName> (expects plugins/<PluginName>/resources/js)`);
}

const SHARED =
    target === "user"
        ? ["react", "react-dom", "react/jsx-runtime", "@inertiajs/react", "react-i18next", "ziggy-js", "@gobiz/user"]
        : ["react", "react-dom", "react/jsx-runtime", "@inertiajs/react", "react-i18next", "@gobiz/admin"];
const GLOBAL = target === "user" ? "GoBizUser" : "GoBizAdmin";
const PREFIX = "\0gobiz-shared:";

/** `import { useState } from "react"` → property lookup on window.GoBizAdmin.modules.react (GoBizUser for customer pages) */
function sharedModules(): Plugin {
    return {
        name: "gobiz-shared-modules",
        enforce: "pre",
        resolveId: (id) => (SHARED.includes(id) ? PREFIX + id : null),
        load(id) {
            if (!id.startsWith(PREFIX)) return null;
            const name = JSON.stringify(id.slice(PREFIX.length));
            return {
                code: `const m = window.${GLOBAL}.modules[${name}];\nexport default (m.default ?? m);\nexport const __shared = m;`,
                syntheticNamedExports: "__shared",
            };
        },
    };
}

const pages = readdirSync(sourceDir).filter((file) => (target === "widgets" ? /\.tsx?$/.test(file) && !file.endsWith(".d.ts") : file.endsWith(".tsx")));
const manifest = JSON.parse(readFileSync(resolve(pluginDir, base === "templates" ? "template.json" : "plugin.json"), "utf8"));

// Tailwind entry for this plugin: utilities only (core already ships preflight
// and base styles), shared theme tokens, scanning only this plugin's sources.
const cssDir = resolve(root, "node_modules/.gobiz-plugins");
mkdirSync(cssDir, { recursive: true });
const cssEntry = resolve(cssDir, `${base}-${plugin}-${target}.css`);
writeFileSync(
    cssEntry,
    [
        // Same order as admin.css; plugin classes go in "plugin-utilities", below core utilities
        "@layer theme, base, components, plugin-utilities, utilities;",
        '@import "tailwindcss-v4/theme.css" layer(theme);',
        '@import "tailwindcss-v4/utilities.css" layer(plugin-utilities) source(none);',
        `@import "${resolve(root, "resources/css/admin-theme.css")}";`,
        // Colours only the customer panel defines (plugins/ModernDashboard/Views/css/app.css)
        ...(target === "user"
            ? ["@theme inline { --color-destructive-foreground: var(--destructive-foreground); --color-info: var(--info); }"]
            : []),
        `@source "${sourceDir}";`,
        // Customer pages live in resources/js/user: keep admin classes from scanning them
        ...(target === "admin" ? [`@source not "${resolve(sourceDir, "user")}";`, `@source not "${resolve(sourceDir, "widgets")}";`] : []),
        "",
    ].join("\n"),
);

const entry = (file: string) => file.replace(/\.tsx?$/, "");

export default defineConfig({
    root,
    // Relative base so any lazy chunk/asset URLs resolve from where dist/ is served
    base: "./",
    publicDir: false,
    plugins: [
        ...(target === "widgets" ? [] : [sharedModules(), react(), tailwindcss()]),
        {
            name: "gobiz-plugin-manifest",
            generateBundle() {
                if (target === "widgets") return;
                this.emitFile({
                    type: "asset",
                    fileName: "manifest.json",
                    source: JSON.stringify(
                        {
                            plugin,
                            version: manifest.version ?? "0",
                            api: target === "user" ? (manifest.requires_user ?? "1") : (manifest.requires_admin ?? "1"),
                            pages: Object.fromEntries(pages.map((file) => [entry(file), `${entry(file)}.js`])),
                            css: "style.css",
                        },
                        null,
                        2,
                    ),
                });
            },
        },
    ],
    build: {
        outDir,
        emptyOutDir: true,
        sourcemap: false,
        rollupOptions: {
            input: {
                ...Object.fromEntries(pages.map((file) => [entry(file), resolve(sourceDir, file)])),
                ...(target === "widgets" ? {} : { style: cssEntry }),
            },
            preserveEntrySignatures: "exports-only",
            output: {
                format: "es",
                entryFileNames: "[name].js",
                chunkFileNames: "chunks/[name]-[hash].js",
                assetFileNames: "[name][extname]",
            },
        },
    },
});
