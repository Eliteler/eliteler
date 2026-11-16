import { router } from "@inertiajs/react";
import { format, formatDistanceToNowStrict, parseISO, isValid } from "date-fns";

const DATE_FORMAT_DIFF_FOR_HUMANS = "diffForHumans";

// Cache to hold the active format and timezone globally in React
let cachedSystemFormat: string | null = null;
let cachedTimezone: string | null = null;

// Listen to Inertia navigation events to update format dynamically
if (typeof window !== "undefined") {
    router.on("navigate", (event) => {
        const props = event.detail.page.props as Record<string, any>;
        if (props?.date_time_format) {
            cachedSystemFormat = props.date_time_format;
        }
        if (props?.timezone) {
            cachedTimezone = props.timezone;
        }
    });
}

/**
 * Strips bracketed regional identifiers like " (BR)", " (Intl)", " (Asia)"
 */
function cleanPhpFormat(phpFormat: string): string {
    return phpFormat.replace(/\s*\([^)]*\)/g, "").trim();
}

/**
 * Converts PHP format to date-fns format matching GoBiz dateTimeFormats
 */
export function phpToDateFns(phpFormat: string, dateOnly = false): string {
    let clean = cleanPhpFormat(phpFormat);

    // If date-only is requested, strip time tokens like H, h, i, s, A
    if (dateOnly) {
        clean = clean
            .replace(/[HhGgisAa:]+/g, "")
            .trim()
            .replace(/\s+/g, " ");
    }

    const formatMap: Record<string, string> = {
        d: "dd", // 01 to 31
        j: "d", // 1 to 31
        D: "EEE", // Mon
        l: "EEEE", // Monday
        m: "MM", // 01 to 12
        n: "M", // 1 to 12
        M: "MMM", // Mar
        F: "MMMM", // March
        Y: "yyyy", // 2025
        y: "yy", // 25
        H: "HH", // 14 (24-hour)
        G: "H", // 14 (24-hour no zero)
        h: "hh", // 02 (12-hour)
        g: "h", // 2 (12-hour no zero)
        i: "mm", // 30
        s: "ss", // 00
        A: "a", // PM
        a: "aaa", // pm
    };

    return clean.replace(
        /[dDjlmnMFYyHGhGgisAa]/g,
        (match) => formatMap[match] || match,
    );
}

/**
 * Resolves date_time_format from Inertia props or initial DOM dataset
 */
function getSystemDateFormat(): string {
    // 1. Return cached format if already resolved
    if (cachedSystemFormat) {
        return cachedSystemFormat;
    }

    // 2. Try resolving from Inertia router page props
    const routerProps = (router as any)?.page?.props as
        | Record<string, any>
        | undefined;
    const formatFromRouter =
        routerProps?.date_time_format ||
        routerProps?.settings?.date_time_format;
    if (formatFromRouter) {
        cachedSystemFormat = formatFromRouter;
        return formatFromRouter;
    }

    // 3. Fallback: Parse directly from Inertia root DOM element (#app)
    if (typeof document !== "undefined") {
        try {
            const appEl = document.getElementById("app");
            if (appEl?.dataset?.page) {
                const initialProps = JSON.parse(appEl.dataset.page)?.props;
                if (initialProps?.date_time_format) {
                    cachedSystemFormat = initialProps.date_time_format;
                    return initialProps.date_time_format;
                }
            }
        } catch {
            // Ignore DOM parsing errors
        }
    }

    return "l, d F Y h:i A";
}

/** The app's timezone (shared "timezone" prop); null = the browser's own. */
function getSystemTimezone(): string | null {
    if (cachedTimezone) return cachedTimezone;

    const fromRouter = ((router as any)?.page?.props as Record<string, any> | undefined)?.timezone;
    if (fromRouter) return (cachedTimezone = fromRouter);

    if (typeof document !== "undefined") {
        try {
            const fromDom = JSON.parse(document.getElementById("app")?.dataset?.page ?? "null")?.props?.timezone;
            if (fromDom) return (cachedTimezone = fromDom);
        } catch {
            // Ignore DOM parsing errors
        }
    }

    return null;
}

/**
 * The same moment as wall-clock time in the app's timezone, so dates read the same as on
 * the server (Blade pages, emails, the mobile app), wherever the admin's browser is.
 */
function inSystemTimezone(date: Date): Date {
    const timeZone = getSystemTimezone();
    if (!timeZone) return date;

    try {
        const parts = Object.fromEntries(
            new Intl.DateTimeFormat("en-US", {
                timeZone,
                hourCycle: "h23",
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            })
                .formatToParts(date)
                .map((part) => [part.type, part.value]),
        );
        return new Date(Number(parts.year), Number(parts.month) - 1, Number(parts.day), Number(parts.hour), Number(parts.minute), Number(parts.second));
    } catch {
        // Unknown timezone name: the browser's own
        return date;
    }
}

/**
 * Safely parses any date string into a Date object
 */
function parseDate(iso: string): Date | null {
    const normalized = iso.includes("T") ? iso : iso.replace(" ", "T");
    const parsed = parseISO(normalized);
    if (isValid(parsed)) return parsed;

    const directDate = new Date(iso);
    return isValid(directDate) ? directDate : null;
}

/** "3 days ago" / "in 2 days" */
export function fromNow(iso: string | null | undefined) {
    if (!iso) return "—";
    try {
        const date = parseDate(iso);
        return date
            ? formatDistanceToNowStrict(date, { addSuffix: true })
            : "—";
    } catch {
        return "—";
    }
}

/**
 * Formats a date using the system configuration format (e.g. "l, d F Y h:i A")
 */
export function formatDate(
    iso: string | null | undefined,
    patternOrPhpFormat?: string,
    dateOnly = false,
) {
    if (!iso) return "—";

    const date = parseDate(iso);
    if (!date) return "—";

    // Use custom pattern if passed, otherwise use system configuration format
    const rawFormat = (patternOrPhpFormat || getSystemDateFormat()).trim();

    // Check for "diffForHumans" relative time option
    if (
        rawFormat === DATE_FORMAT_DIFF_FOR_HUMANS ||
        /relative|human|ago/i.test(rawFormat)
    ) {
        return fromNow(iso);
    }

    // Convert PHP tokens to date-fns format (e.g. "l, d F Y h:i A" -> "EEEE, dd MMMM yyyy hh:mm a")
    const dateFnsPattern = phpToDateFns(rawFormat, dateOnly);

    try {
        return format(inSystemTimezone(date), dateFnsPattern);
    } catch (error) {
        console.error(
            "[date.ts] Format error with pattern:",
            dateFnsPattern,
            error,
        );
        return format(date, "EEEE, dd MMMM yyyy hh:mm a");
    }
}
