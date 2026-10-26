import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import { existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { defineConfig, type Plugin } from "vite";

/*
 | Builds one plugin's React admin pages: npm run plugin:build -- <Name>
 |
 |   plugins/<Name>/resources/js/<page>.tsx  →  plugins/<Name>/dist/<page>.js
 |                                              plugins/<Name>/dist/style.css
 |                                              plugins/<Name>/dist/manifest.json
 |
 | dist/ ships inside the plugin zip; customers' servers never build anything.
 | React, Inertia, react-i18next and "@gobiz/admin" are NOT bundled: they are
 | read from window.GoBizAdmin, which the core admin provides (plugin-runtime.ts).
 */
const root = dirname(fileURLToPath(import.meta.url));
const plugin = process.env.PLUGIN ?? "";
// plugins/<Name> (plugin.json) or templates/<Name> (template.json), set by scripts/plugin-build.mjs
const base = process.env.PLUGIN_BASE === "templates" ? "templates" : "plugins";
const pluginDir = resolve(root, base, plugin);
const sourceDir = resolve(pluginDir, "resources/js");

if (!/^[A-Za-z0-9_-]+$/.test(plugin) || !existsSync(sourceDir)) {
    throw new Error(`Usage: npm run plugin:build -- <PluginName> (expects plugins/<PluginName>/resources/js)`);
}

const SHARED = ["react", "react-dom", "react/jsx-runtime", "@inertiajs/react", "react-i18next", "@gobiz/admin"];
const PREFIX = "\0gobiz-shared:";

/** `import { useState } from "react"` → property lookup on window.GoBizAdmin.modules.react */
function sharedModules(): Plugin {
    return {
        name: "gobiz-shared-modules",
        enforce: "pre",
        resolveId: (id) => (SHARED.includes(id) ? PREFIX + id : null),
        load(id) {
            if (!id.startsWith(PREFIX)) return null;
            const name = JSON.stringify(id.slice(PREFIX.length));
            return {
                code: `const m = window.GoBizAdmin.modules[${name}];\nexport default (m.default ?? m);\nexport const __shared = m;`,
                syntheticNamedExports: "__shared",
            };
        },
    };
}

const pages = readdirSync(sourceDir).filter((file) => file.endsWith(".tsx"));
const manifest = JSON.parse(readFileSync(resolve(pluginDir, base === "templates" ? "template.json" : "plugin.json"), "utf8"));

// Tailwind entry for this plugin: utilities only (core already ships preflight
// and base styles), shared theme tokens, scanning only this plugin's sources.
const cssDir = resolve(root, "node_modules/.gobiz-plugins");
mkdirSync(cssDir, { recursive: true });
const cssEntry = resolve(cssDir, `${base}-${plugin}.css`);
writeFileSync(
    cssEntry,
    [
        // Same order as admin.css; plugin classes go in "plugin-utilities", below core utilities
        "@layer theme, base, components, plugin-utilities, utilities;",
        '@import "tailwindcss-v4/theme.css" layer(theme);',
        '@import "tailwindcss-v4/utilities.css" layer(plugin-utilities) source(none);',
        `@import "${resolve(root, "resources/css/admin-theme.css")}";`,
        `@source "${sourceDir}";`,
        "",
    ].join("\n"),
);

export default defineConfig({
    root,
    // Relative base so any lazy chunk/asset URLs resolve from where dist/ is served
    base: "./",
    publicDir: false,
    plugins: [
        sharedModules(),
        react(),
        tailwindcss(),
        {
            name: "gobiz-plugin-manifest",
            generateBundle() {
                this.emitFile({
                    type: "asset",
                    fileName: "manifest.json",
                    source: JSON.stringify(
                        {
                            plugin,
                            version: manifest.version ?? "0",
                            api: manifest.requires_admin ?? "1",
                            pages: Object.fromEntries(pages.map((file) => [file.replace(/\.tsx$/, ""), file.replace(/\.tsx$/, ".js")])),
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
        outDir: resolve(pluginDir, "dist"),
        emptyOutDir: true,
        sourcemap: false,
        rollupOptions: {
            input: {
                ...Object.fromEntries(pages.map((file) => [file.replace(/\.tsx$/, ""), resolve(sourceDir, file)])),
                style: cssEntry,
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
