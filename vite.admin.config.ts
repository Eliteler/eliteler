import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";

/*
 | Admin panel (Inertia + React) build.
 |
 | Kept separate from vite.config.ts on purpose: that config builds the
 | ModernDashboard user panel into plugins/ModernDashboard/Views/js/build
 | (served from public/build), and its source is not part of this repo.
 | The admin panel builds to public/build-admin and uses public/hot-admin.
 */
export default defineConfig({
    // Relative base: chunk/CSS preloads resolve from the served app.js URL.
    // Without it laravel-vite-plugin bakes .env ASSET_URL (e.g. https://gobiz.test)
    // into the bundle, breaking lazy-loaded pages on any other domain.
    base: "./",
    plugins: [
        laravel({
            input: ["resources/js/admin/app.tsx"],
            buildDirectory: "build-admin",
            hotFile: "public/hot-admin",
            refresh: [
                "resources/views/admin/app.blade.php",
                "app/Support/Admin/**",
                "app/Http/Controllers/Admin/**",
            ],
        }),
        react({
            babel: {
                plugins: ["babel-plugin-react-compiler"],
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "@admin": fileURLToPath(
                new URL("./resources/js/admin", import.meta.url),
            ),
            "@lang": fileURLToPath(
                new URL("./resources/lang", import.meta.url),
            ),
        },
    },
    server: {
        // Disable polling if active, or use optimized settings
        watch: {
            usePolling: false, // Set to true only if file changes are not detected
            ignored: ["**/node_modules/**", "**/vendor/**", "**/public/**"], // Ignore heavy folders from watching
        },
        // Speed up cold starts by pre-bundling dependencies
        hmr: {
            overlay: true,
        },
    },
    // Ensure caching is properly enabled
    optimizeDeps: {
        force: false, // Avoid re-bundling dependencies on every run
    },
});
