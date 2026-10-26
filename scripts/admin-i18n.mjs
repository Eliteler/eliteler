/**
 * Add the admin panel's translation keys to resources/lang/en.json.
 *
 * Scans the React admin (t("...")) and the PHP that serves it (__('...')),
 * and appends every key that en.json does not have yet as "Text": "Text".
 * Existing keys and other locale files are never changed.
 *
 *   npm run i18n:admin            add missing keys
 *   npm run i18n:admin -- --check list missing keys, exit 1 if any
 */
import { existsSync, readFileSync, readdirSync, statSync, writeFileSync } from "node:fs";
import { join } from "node:path";

const ROOT = new URL("..", import.meta.url).pathname;
const EN = join(ROOT, "resources/lang/en.json");

const SOURCES = [
    { dir: "resources/js/admin", ext: [".tsx", ".ts"], patterns: [/\bt\(\s*"((?:[^"\\]|\\.)+)"/g, /\bt\(\s*'((?:[^'\\]|\\.)+)'/g, /\bt\(\s*`([^`$]+)`/g] },
    // String constants passed through t() later, e.g. label: "Business cards"
    { dir: "resources/js/admin/pages", ext: [".ts", ".tsx"], patterns: [/\b(?:label|hint|title|description|long|short): "((?:[^"\\]|\\.)+)"/g, /\[\s*"[a-z_]+",\s*"((?:[^"\\]|\\.)+)"\s*\]/g] },
    {
        dir: "app",
        ext: [".php"],
        only: ["Services/Admin", "Support/Admin", "Http/Controllers/Admin"],
        patterns: [/__\(\s*'((?:[^'\\]|\\.)+)'/g, /__\(\s*"((?:[^"\\]|\\.)+)"/g],
    },
    // Plugins and web templates with React admin pages: their page sources and admin controllers
    ...["plugins", "templates"].flatMap((base) =>
        readdirSync(join(ROOT, base))
            .filter((name) => existsSync(join(ROOT, base, name, "resources/js")))
            .flatMap((name) => [
                { dir: `${base}/${name}/resources/js`, ext: [".tsx", ".ts"], patterns: [/\bt\(\s*"((?:[^"\\]|\\.)+)"/g, /\bt\(\s*'((?:[^'\\]|\\.)+)'/g] },
                { dir: `${base}/${name}/Controllers`, ext: [".php"], patterns: [/__\(\s*'((?:[^'\\]|\\.)+)'/g, /__\(\s*"((?:[^"\\]|\\.)+)"/g] },
            ]),
    ),
];

function walk(dir, ext) {
    return readdirSync(dir).flatMap((name) => {
        const path = join(dir, name);
        if (statSync(path).isDirectory()) return name === "ui" ? [] : walk(path, ext);
        return ext.some((e) => name.endsWith(e)) ? [path] : [];
    });
}

const unescape = (s) => s.replace(/\\(["'`\\])/g, "$1");

const found = new Set();
for (const source of SOURCES) {
    for (const file of walk(join(ROOT, source.dir), source.ext)) {
        if (source.only && !source.only.some((part) => file.includes(`/${part}/`))) continue;
        const code = readFileSync(file, "utf8");
        for (const pattern of source.patterns) {
            for (const match of code.matchAll(pattern)) {
                const key = unescape(match[1]);
                // Skip identifiers / route names / CSS that the loose patterns can catch
                if (!/\s|[A-Z]/.test(key) || /^[a-z0-9_.-]+$/.test(key)) continue;
                found.add(key);
            }
        }
    }
}

const en = JSON.parse(readFileSync(EN, "utf8"));
const missing = [...found].filter((key) => !(key in en)).sort((a, b) => a.localeCompare(b));

if (process.argv.includes("--check")) {
    missing.forEach((key) => console.log(key));
    console.log(`${missing.length} missing key(s)`);
    process.exit(missing.length ? 1 : 0);
}

if (missing.length) {
    // Append before the closing brace instead of re-serialising, so the rest
    // of the file (including any duplicate keys) stays byte-for-byte the same.
    const raw = readFileSync(EN, "utf8");
    const end = raw.lastIndexOf("}");
    const head = raw.slice(0, end).replace(/\s*$/, "");
    const lines = missing.map((key) => `    ${JSON.stringify(key)}: ${JSON.stringify(key)}`).join(",\n");
    writeFileSync(EN, `${head},\n${lines}\n}\n`);
}
console.log(`Added ${missing.length} key(s) to resources/lang/en.json`);
