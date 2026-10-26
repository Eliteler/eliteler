import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    base: "/build/",
    plugins: [
        laravel({
            input: [
                "plugins/ModernDashboard/Views/css/app.css",
                "plugins/ModernDashboard/Views/js/app.tsx",
            ],
            refresh: true,
        }),
        react({
            babel: {
                plugins: ["babel-plugin-react-compiler"],
            },
        }),
        tailwindcss(),
    ],
    esbuild: {
        jsx: "automatic",
    },
    resolve: {
        alias: {
            "@": "/plugins/ModernDashboard/Views/js",
        },
    },
    build: {
        outDir: "plugins/ModernDashboard/Views/js/build",
    },
});
