/*
 | Shared runtime for plugin admin pages.
 |
 | Plugin pages are prebuilt by our team (npm run plugin:build -- <Name>) and
 | shipped inside the plugin zip. They don't bundle React, Inertia or the admin
 | components; their imports resolve to window.GoBizAdmin (vite.plugin.config.ts).
 | One copy of everything, same look, small plugin bundles.
 |
 | PLUGIN_API is the version of this contract (plugin-sdk/admin.ts + the
 | modules below). Bump it only for breaking changes and keep it equal to
 | AdminSharedProps::PLUGIN_API. Plugins declare "requires_admin" in plugin.json.
 */
import * as Inertia from "@inertiajs/react";
import * as React from "react";
import * as ReactDOM from "react-dom";
import * as JsxRuntime from "react/jsx-runtime";
import * as ReactI18next from "react-i18next";

import * as admin from "@admin/plugin-sdk/admin";

export const PLUGIN_API = "1";

const runtime = {
    version: PLUGIN_API,
    modules: {
        react: React,
        "react-dom": ReactDOM,
        "react/jsx-runtime": JsxRuntime,
        "@inertiajs/react": Inertia,
        "react-i18next": ReactI18next,
        "@gobiz/admin": admin,
    },
};

export type GoBizAdminRuntime = typeof runtime;

declare global {
    interface Window {
        GoBizAdmin: GoBizAdminRuntime;
    }
}

window.GoBizAdmin = runtime;
