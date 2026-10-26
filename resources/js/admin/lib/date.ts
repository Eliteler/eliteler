import { format, formatDistanceToNowStrict } from "date-fns";

export function formatDate(iso: string | null | undefined, pattern = "dd MMM yyyy") {
    return iso ? format(new Date(iso), pattern) : "—";
}

/** "3 days ago" / "in 2 days" */
export function fromNow(iso: string | null | undefined) {
    return iso ? formatDistanceToNowStrict(new Date(iso), { addSuffix: true }) : "—";
}
