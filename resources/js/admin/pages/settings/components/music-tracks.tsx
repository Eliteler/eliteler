import { router, useForm } from "@inertiajs/react";
import { ArrowDown, ArrowUp, FileAudio, Loader2, Music, Pencil, Plus, Trash2, Upload, X } from "lucide-react";
import { useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { FieldError } from "@admin/components/field-error";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";
import { cn } from "@admin/lib/utils";

export type MusicTrack = { id: number; title: string; artist: string | null; src: string; uploaded: boolean; status: boolean };

/** Admin list of the mp3 tracks customers can play behind the analytics globe. */
export function MusicTracks({ tracks, maxUploadMb }: { tracks: MusicTrack[]; maxUploadMb: number }) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<MusicTrack | "new" | null>(null);
    const [deleting, setDeleting] = useState<MusicTrack | null>(null);
    const [processing, setProcessing] = useState(false);

    const post = (name: string, track: MusicTrack, data: Record<string, string> = {}, onFinish?: () => void) =>
        router.post(route(name, { track: track.id }), data, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                onFinish?.();
            },
        });

    return (
        <Card>
            <CardHeader className="flex flex-row items-start justify-between gap-4">
                <div className="flex flex-col gap-1.5">
                    <CardTitle className="flex items-center gap-2">
                        <Music className="size-4" />
                        {t("Background music")}
                    </CardTitle>
                    <CardDescription>
                        {t("Customers can play these tracks while they watch the visitor globe in their dashboard. Only use music you have the rights to share.")}
                    </CardDescription>
                </div>
                <Button type="button" onClick={() => setEditing("new")}>
                    <Plus />
                    {t("Add track")}
                </Button>
            </CardHeader>
            <CardContent>
                {tracks.length === 0 ? (
                    <p className="text-sm text-muted-foreground">{t("No tracks yet. Without tracks, the music button is hidden.")}</p>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {tracks.map((track, index) => (
                            <li key={track.id} className="flex flex-wrap items-center gap-3 p-3">
                                <div className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate font-medium">{track.title}</span>
                                    <span className="truncate text-sm text-muted-foreground">
                                        {track.artist ? `${track.artist} · ` : ""}
                                        {track.uploaded ? t("Uploaded file") : t("Link")}
                                    </span>
                                </div>
                                {/* Preview */}
                                <audio controls preload="none" src={track.src} className="h-8 max-w-full sm:w-56" />
                                {!track.status && <Badge variant="outline">{t("Hidden")}</Badge>}
                                <div className="flex items-center gap-1">
                                    <Switch
                                        checked={track.status}
                                        disabled={processing}
                                        onCheckedChange={() => post("admin.visitor.analytics.tracks.toggle", track)}
                                        aria-label={t("Show to customers")}
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        disabled={processing || index === 0}
                                        onClick={() => post("admin.visitor.analytics.tracks.move", track, { direction: "up" })}
                                        aria-label={t("Move up")}
                                    >
                                        <ArrowUp />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        disabled={processing || index === tracks.length - 1}
                                        onClick={() => post("admin.visitor.analytics.tracks.move", track, { direction: "down" })}
                                        aria-label={t("Move down")}
                                    >
                                        <ArrowDown />
                                    </Button>
                                    <Button type="button" variant="ghost" size="icon" onClick={() => setEditing(track)} aria-label={t("Edit")}>
                                        <Pencil />
                                    </Button>
                                    <Button type="button" variant="ghost" size="icon" onClick={() => setDeleting(track)} aria-label={t("Delete")}>
                                        <Trash2 />
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </CardContent>

            {editing && (
                <TrackDialog key={editing === "new" ? "new" : editing.id} track={editing === "new" ? null : editing} maxUploadMb={maxUploadMb} onClose={() => setEditing(null)} />
            )}

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={t("Delete this track?")}
                description={deleting?.uploaded ? t("The uploaded file is deleted too.") : undefined}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={() => deleting && post("admin.visitor.analytics.tracks.delete", deleting, {}, () => setDeleting(null))}
            />
        </Card>
    );
}

function TrackDialog({ track, maxUploadMb, onClose }: { track: MusicTrack | null; maxUploadMb: number; onClose: () => void }) {
    const { t } = useTranslation();
    const form = useForm<{ title: string; artist: string; url: string; file: File | null }>({
        title: track?.title ?? "",
        artist: track?.artist ?? "",
        url: track && !track.uploaded ? track.src : "",
        file: null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        const name = track ? "admin.visitor.analytics.tracks.update" : "admin.visitor.analytics.tracks.store";
        form.post(route(name, track ? { track: track.id } : {}), { preserveScroll: true, forceFormData: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{track ? t("Edit track") : t("Add track")}</DialogTitle>
                    <DialogDescription>{t("Upload an mp3, or paste an https link to one.")}</DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="flex flex-col gap-4">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="track-title">{t("Title")}</Label>
                        <Input id="track-title" value={form.data.title} onChange={(event) => form.setData("title", event.target.value)} required maxLength={150} />
                        <FieldError message={form.errors.title} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="track-artist">{t("Artist")}</Label>
                        <Input id="track-artist" value={form.data.artist} onChange={(event) => form.setData("artist", event.target.value)} maxLength={150} />
                        <FieldError message={form.errors.artist} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="track-file">{t("mp3 file")}</Label>
                        <FilePicker id="track-file" file={form.data.file} onChange={(file) => form.setData("file", file)} invalid={!!form.errors.file} />
                        <p className="text-xs text-muted-foreground">
                            {t("Up to :size MB.").replace(":size", String(maxUploadMb))}
                            {track?.uploaded && ` ${t("Leave empty to keep the current file.")}`}
                        </p>
                        <FieldError message={form.errors.file} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="track-url">{t("Or a link to an mp3")}</Label>
                        <Input
                            id="track-url"
                            type="url"
                            inputMode="url"
                            placeholder="https://"
                            value={form.data.url}
                            onChange={(event) => form.setData("url", event.target.value)}
                            disabled={form.data.file !== null}
                        />
                        <FieldError message={form.errors.url} />
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={onClose}>
                            {t("Cancel")}
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {track ? t("Save changes") : t("Add track")}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

/** "Choose mp3" button with the picked file's name and size; the native input stays hidden. */
function FilePicker({ id, file, onChange, invalid }: { id: string; file: File | null; onChange: (file: File | null) => void; invalid?: boolean }) {
    const { t } = useTranslation();
    const input = useRef<HTMLInputElement>(null);

    const clear = () => {
        onChange(null);
        if (input.current) input.current.value = "";
    };

    return (
        <div
            className={cn(
                "flex min-h-9 items-center gap-2 rounded-md border border-input px-1.5 py-1 shadow-xs dark:bg-input/30",
                invalid && "border-destructive",
            )}
        >
            <input
                ref={input}
                id={id}
                type="file"
                accept="audio/mpeg,.mp3"
                className="sr-only"
                onChange={(event) => onChange(event.target.files?.[0] ?? null)}
            />
            <Button type="button" variant="secondary" size="sm" className="h-7 shrink-0" onClick={() => input.current?.click()}>
                <Upload />
                {file ? t("Change") : t("Choose mp3")}
            </Button>
            {file ? (
                <>
                    <FileAudio className="size-4 shrink-0 text-muted-foreground" aria-hidden="true" />
                    <span className="min-w-0 flex-1 truncate text-sm">{file.name}</span>
                    <span className="shrink-0 text-xs text-muted-foreground tabular-nums">{(file.size / 1024 / 1024).toFixed(1)} MB</span>
                    <Button type="button" variant="ghost" size="icon" className="size-7 shrink-0" onClick={clear} aria-label={t("Remove file")}>
                        <X />
                    </Button>
                </>
            ) : (
                <span className="truncate text-sm text-muted-foreground">{t("No file chosen")}</span>
            )}
        </div>
    );
}

