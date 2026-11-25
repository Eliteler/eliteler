import { CheckCircle2, Loader2, Nfc, XCircle } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { CopyButton } from "@admin/components/copy-button";
import { Button } from "@admin/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";

/** Web NFC (Chrome on Android, HTTPS only). Not in TypeScript's DOM types yet. */
type NdefWriter = {
    write: (
        message: { records: { recordType: "url"; data: string }[] },
        options?: { signal?: AbortSignal; overwrite?: boolean },
    ) => Promise<void>;
};
type NdefWindow = Window & { NDEFReader?: new () => NdefWriter };

/** Whether this browser can write NFC tags itself. */
export const canWriteNfc = () =>
    typeof window !== "undefined" &&
    "NDEFReader" in window &&
    window.isSecureContext;

type State = "writing" | "done" | "failed" | "guide";

/**
 * Writes a link onto an NFC tag from the browser when it can (Web NFC);
 * otherwise explains how to write it with an NFC app.
 *
 * Open it from a click (the browser only allows NFC writes that start from
 * a user action): <WriteNfcDialog url={...} open={...} onOpenChange={...} />
 */
export function WriteNfcDialog({
    url,
    open,
    onOpenChange,
}: {
    url: string | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { t } = useTranslation();
    const [state, setState] = useState<State>("guide");
    const [error, setError] = useState<string | null>(null);
    const abort = useRef<AbortController | null>(null);

    const write = async () => {
        if (!url) return;
        const Reader = (window as NdefWindow).NDEFReader;
        if (!Reader || !window.isSecureContext) {
            setState("guide");
            return;
        }

        abort.current?.abort();
        abort.current = new AbortController();
        setError(null);
        setState("writing");

        try {
            await new Reader().write(
                { records: [{ recordType: "url", data: url }] },
                { signal: abort.current.signal, overwrite: true },
            );
            setState("done");
        } catch (e) {
            if ((e as DOMException)?.name === "AbortError") return;
            setError(
                (e as DOMException)?.name === "NotAllowedError"
                    ? t("NFC permission was denied. Allow NFC for this site and try again.")
                    : t("Could not write the tag. Hold it still against the back of the phone and try again."),
            );
            setState("failed");
        }
    };

    // Start as soon as the dialog opens (it opens from a click)
    useEffect(() => {
        if (!open) {
            abort.current?.abort();
            return;
        }
        if (canWriteNfc()) {
            void write();
        } else {
            setState("guide");
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, url]);

    const close = () => onOpenChange(false);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Nfc className="size-5" />
                        {t("Write to NFC tag")}
                    </DialogTitle>
                    <DialogDescription>
                        {state === "guide"
                            ? t("This browser can't write NFC tags. Use your phone and an NFC app:")
                            : t("The tag will open this link when tapped.")}
                    </DialogDescription>
                </DialogHeader>

                {/* The link */}
                <div className="flex items-center gap-2 rounded-lg border bg-muted/40 p-2">
                    <span className="min-w-0 flex-1 truncate font-mono text-sm select-all" dir="ltr">
                        {url}
                    </span>
                    {url && <CopyButton value={url} label={t("Copy NFC link")} />}
                </div>

                {state === "writing" && (
                    <div className="flex flex-col items-center gap-3 py-4 text-center">
                        <span className="relative grid size-16 place-items-center">
                            <span className="absolute inset-0 animate-ping rounded-full bg-primary/20" />
                            <Nfc className="relative size-8 text-primary" />
                        </span>
                        <p className="text-sm font-medium">{t("Hold the NFC tag against the back of your phone…")}</p>
                        <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Loader2 className="size-3.5 animate-spin" />
                            {t("Waiting for a tag")}
                        </p>
                    </div>
                )}

                {state === "done" && (
                    <div className="flex flex-col items-center gap-2 py-4 text-center">
                        <CheckCircle2 className="size-10 text-success" />
                        <p className="text-sm font-medium">{t("Written! Tap the tag with a phone to test it.")}</p>
                    </div>
                )}

                {state === "failed" && (
                    <div className="flex flex-col items-center gap-2 py-4 text-center">
                        <XCircle className="size-10 text-destructive" />
                        <p className="text-sm">{error}</p>
                    </div>
                )}

                {state === "guide" && (
                    <ol className="flex list-decimal flex-col gap-1.5 ps-5 text-sm">
                        <li>{t("Copy the link above and send it to your phone.")}</li>
                        <li>{t("Install a free NFC writing app, such as \"NFC Tools\" (Android and iPhone).")}</li>
                        <li>{t("In the app: Write → Add a record → URL / URI → paste the link.")}</li>
                        <li>{t("Tap Write and hold the tag against the back of the phone until it confirms.")}</li>
                        <li>{t("Tap the tag with any phone to check that it opens the link.")}</li>
                    </ol>
                )}

                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    {state === "writing" && (
                        <Button type="button" variant="outline" onClick={close}>
                            {t("Cancel")}
                        </Button>
                    )}
                    {state === "failed" && (
                        <Button type="button" variant="outline" onClick={() => void write()}>
                            {t("Try again")}
                        </Button>
                    )}
                    {state === "done" && (
                        <Button type="button" variant="outline" onClick={() => void write()}>
                            {t("Write another tag")}
                        </Button>
                    )}
                    {state !== "writing" && (
                        <Button type="button" onClick={close}>
                            {t("Okay")}
                        </Button>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
