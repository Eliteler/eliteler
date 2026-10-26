#!/usr/bin/env node
/*
 | npm run plugin:build -- <Name>        build plugins/<Name>/dist (or templates/<Name>/dist)
 | npm run plugin:zip   -- <Name>        build, then zip the plugin or web template for release
 | npm run template:build [-- <Name>]    build templates/<Name>/dist, or every web template with React pages
 | npm run template:zip   [-- <Name>]    build, then zip one web template (or every one) for release
 |
 | The zip leaves out the React sources (resources/js) and goes to
 | storage/app/plugin-releases/<Name>-<version>.zip (Plugins → Install) or
 | storage/app/template-releases/<Name>-<version>.zip (Web templates → Upload).
 */
import { execFileSync } from "node:child_process";
import {
    existsSync,
    mkdirSync,
    readFileSync,
    readdirSync,
    rmSync,
    writeFileSync,
} from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { ZipArchive } from "archiver";
import { createWriteStream } from "node:fs";
import { relative, sep } from "node:path";

const root = resolve(dirname(fileURLToPath(import.meta.url)), "..");
// template:build / template:zip pass --templates: only look in templates/, and no name means all of them
const args = process.argv.slice(2);
const mode = args[0];
const onlyTemplates = args.includes("--templates");
const name = args.slice(1).find((arg) => !arg.startsWith("--"));
const hasReact = (dir, entry) =>
    existsSync(resolve(root, dir, entry, "resources/js"));

// README.md shipped in a plugin's or web template's zip, from its plugin.json / template.json
// (rewritten on every zip so the version stays current)
const readme = (dir, base) => {
    const isTemplate = base === "templates";
    const manifest = JSON.parse(
        readFileSync(
            resolve(dir, isTemplate ? "template.json" : "plugin.json"),
            "utf8",
        ),
    );
    const lines = [
        `# ${manifest.name ?? (isTemplate ? manifest.template_id : manifest.plugin_id)}`,
        "",
    ];

    if (manifest.description) {
        lines.push(manifest.description, "");
    }
    lines.push(
        `- Version: ${manifest.version ?? "-"}`,
        `- Requires: GoBiz SaaS ${manifest.min_version ?? "-"} or later`,
    );
    if (manifest.documentation_url) {
        lines.push(`- Documentation: ${manifest.documentation_url}`);
    }

    return [
        ...lines,
        "",
        "## Installation",
        "",
        "**Do not extract this zip file.** Upload the zip directly into your GoBiz SaaS platform:",
        "",
        "1. Log in to your GoBiz SaaS admin panel.",
        isTemplate
            ? "2. Go to **Website templates** and click **Install template**."
            : "2. Go to **Plugins** and click **Install plugin**.",
        "3. Choose this .zip file as it is, without extracting or renaming its contents, and click **Install**.",
        "",
        "The zip's file name doesn't matter, so you can keep the name it was downloaded with.",
        "",
        "## Updating",
        "",
        "Upload the zip of the new version the same way. It replaces the installed version.",
        "",
    ].join("\n");
};

// Build all plugins or templates when the name is "all"
if (name === "all") {
    const target = onlyTemplates ? "templates" : "plugins";
    const entries = readdirSync(resolve(root, target)).filter((entry) =>
        hasReact(target, entry),
    );

    for (const entry of entries) {
        execFileSync(
            process.execPath,
            [
                fileURLToPath(import.meta.url),
                mode,
                ...(onlyTemplates ? ["--templates"] : []),
                entry,
            ],
            { cwd: root, stdio: "inherit" },
        );
    }

    process.exit(0);
}

if (onlyTemplates && !name) {
    const templates = readdirSync(resolve(root, "templates")).filter((entry) =>
        hasReact("templates", entry),
    );
    for (const template of templates) {
        execFileSync(
            process.execPath,
            [fileURLToPath(import.meta.url), mode, "--templates", template],
            { cwd: root, stdio: "inherit" },
        );
    }
    process.exit(0);
}

const base = (onlyTemplates ? ["templates"] : ["plugins", "templates"]).find(
    (dir) => name && /^[A-Za-z0-9_-]+$/.test(name) && hasReact(dir, name),
);
if (!base) {
    console.error(
        onlyTemplates
            ? "Usage: npm run template:build [-- <Name>]   (templates/<Name>/resources/js must exist)"
            : "Usage: npm run plugin:build -- <Name>   (plugins/<Name>/resources/js or templates/<Name>/resources/js must exist)",
    );
    process.exit(1);
}
execFileSync(
    process.execPath,
    [
        resolve(root, "node_modules/vite/bin/vite.js"),
        "build",
        "--config",
        "vite.plugin.config.ts",
        "--logLevel",
        "warn",
    ],
    {
        cwd: root,
        stdio: "inherit",
        env: {
            ...process.env,
            PLUGIN: name,
            PLUGIN_BASE: base,
        },
    },
);

console.log(`✓ Built ${base}/${name}/dist`);

if (mode === "zip") {
    const { version = "0" } = JSON.parse(readFileSync(resolve(root, base, name, base === "templates" ? "template.json" : "plugin.json"), "utf8"));

    const outDir = resolve(
        root,
        "storage/app",
        base === "templates" ? "template-releases" : "plugin-releases",
    );
    const zip = resolve(outDir, `${name}-${version}.zip`);
    const sourceDir = resolve(root, base, name);

    mkdirSync(outDir, { recursive: true });
    rmSync(zip, { force: true });

    writeFileSync(resolve(sourceDir, "README.md"), readme(sourceDir, base));

    await new Promise((resolvePromise, rejectPromise) => {
        const output = createWriteStream(zip);
        const archive = new ZipArchive({ zlib: { level: 9 } });

        output.on("close", resolvePromise);
        output.on("error", rejectPromise);
        archive.on("error", rejectPromise);

        archive.pipe(output);

        // Exclude React sources and dependency folders from the release.
        archive.glob("**/*", {
            cwd: sourceDir,
            dot: true,
            ignore: [
                "node_modules/**",
                "**/node_modules/**",
                "**/.DS_Store",
            ],
        });

        archive.finalize().catch(rejectPromise);
    });

    console.log(`✓ ${relative(root, zip).split(sep).join("/")}`);
}
