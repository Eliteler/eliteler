import { Head, router, useForm } from "@inertiajs/react";
import { Bell, CalendarClock, Loader2, MailCheck, Play, Plus, X } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { CopyButton } from "@admin/components/copy-button";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { cn } from "@admin/lib/utils";

type Props = { cronHour: number; reminderDays: number[]; artisan: string };

const hourLabel = (hour: number) => `${String(hour).padStart(2, "0")}:00`;

// Days are stored signed: 7 = 7 days before expiry, 0 = the expiry day, -3 = 3 days after
type When = "before" | "on" | "after";
const LIMITS = { before: 366, after: 30 };

export default function CronJobs({ cronHour, reminderDays, artisan }: Props) {
    const { t } = useTranslation();
    const form = useForm({ cron_hour: String(cronHour), reminder_days: [...reminderDays].sort((a, b) => b - a) });
    const [draft, setDraft] = useState("");
    const [when, setWhen] = useState<When>("before");
    const [busy, setBusy] = useState<"test" | "run" | null>(null);
    const [confirmRun, setConfirmRun] = useState(false);
    const errors = form.errors as Record<string, string | undefined>;
    const dayErrors = Object.entries(errors).filter(([key]) => key.startsWith("reminder_days")).map(([, message]) => message);

    const cronCommand = `0 ${Number(form.data.cron_hour)} * * * php ${artisan} schedule:run >> /dev/null 2>&1`;
    const queueCommand = `* * * * * php ${artisan} queue:work --stop-when-empty >> /dev/null 2>&1`;

    const addDay = () => {
        const count = Number.parseInt(draft, 10);
        if (when !== "on" && (Number.isNaN(count) || count < 1 || count > LIMITS[when])) return;
        const day = when === "on" ? 0 : when === "before" ? count : -count;
        if (!form.data.reminder_days.includes(day)) {
            form.setData("reminder_days", [...form.data.reminder_days, day].sort((a, b) => b - a));
        }
        setDraft("");
    };

    const removeDay = (day: number) => form.setData("reminder_days", form.data.reminder_days.filter((item) => item !== day));

    const describe = (day: number) => {
        const count = Math.abs(day);
        if (day === 0) return t("On the expiry day");
        if (day > 0) return count === 1 ? t("1 day before") : t(":count days before").replace(":count", String(count));
        return count === 1 ? t("1 day after") : t(":count days after").replace(":count", String(count));
    };

    const before = form.data.reminder_days.filter((day) => day > 0);
    const after = form.data.reminder_days.filter((day) => day < 0);
    const onExpiry = form.data.reminder_days.includes(0);
    const draftCount = Number.parseInt(draft, 10);
    const draftValid = when === "on" ? !onExpiry : !Number.isNaN(draftCount) && draftCount >= 1 && draftCount <= LIMITS[when];

    const action = (kind: "test" | "run") =>
        router.post(route(kind === "test" ? "admin.test.reminder" : "admin.set.cronjob"), {}, {
            preserveScroll: true,
            onStart: () => setBusy(kind),
            onFinish: () => {
                setBusy(null);
                setConfirmRun(false);
            },
        });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.update.cron.jobs"), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t("Cron jobs")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Cron jobs")}
                    description={t("Plan expiry reminders and the server commands that run them.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("Cron jobs") }]}
                />

                <div className="grid max-w-4xl gap-6">
                    <Card>
                        <form onSubmit={submit} className="flex flex-col gap-6">
                            <CardHeader>
                                <CardTitle>{t("Plan expiry reminders")}</CardTitle>
                                <CardDescription>
                                    {t("Customers get a reminder email on each of these days, so they can renew before their plan stops working. Reminders after expiry nudge them to come back.")}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-3">
                                    <Label>{t("Reminder schedule")}</Label>
                                    {/* Timeline: reminders before expiry, the expiry day, then reminders after */}
                                    <div className="flex flex-wrap items-center gap-2 rounded-lg border bg-muted/30 p-3">
                                        {before.map((day) => (
                                            <ReminderChip key={day} label={describe(day)} onRemove={() => removeDay(day)} removeLabel={t("Remove")} />
                                        ))}
                                        <span
                                            className={cn(
                                                "inline-flex items-center gap-1.5 rounded-full border py-1 text-sm font-medium",
                                                onExpiry ? "border-warning/50 bg-warning/10 ps-3 pe-1" : "border-dashed px-3 text-muted-foreground",
                                            )}
                                        >
                                            <CalendarClock className="size-4" />
                                            {onExpiry ? t("Expiry day reminder") : t("Plan expires")}
                                            {onExpiry && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeDay(0)}
                                                    className="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                                                    aria-label={t("Remove")}
                                                >
                                                    <X className="size-3.5" />
                                                </button>
                                            )}
                                        </span>
                                        {after.map((day) => (
                                            <ReminderChip key={day} label={describe(day)} onRemove={() => removeDay(day)} removeLabel={t("Remove")} after />
                                        ))}
                                    </div>
                                    {form.data.reminder_days.length === 0 && <p className="text-sm text-muted-foreground">{t("No reminders yet. Add at least one below.")}</p>}

                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="text-sm">{t("Send a reminder")}</span>
                                        {when !== "on" && (
                                            <>
                                                <Input
                                                    id="new-day"
                                                    type="number"
                                                    min={1}
                                                    max={LIMITS[when]}
                                                    value={draft}
                                                    onChange={(event) => setDraft(event.target.value)}
                                                    onKeyDown={(event) => {
                                                        if (event.key === "Enter") {
                                                            event.preventDefault();
                                                            addDay();
                                                        }
                                                    }}
                                                    placeholder="7"
                                                    className="w-20"
                                                    aria-label={t("Number of days")}
                                                />
                                                <span className="text-sm">{t("days")}</span>
                                            </>
                                        )}
                                        <Select value={when} onValueChange={(value) => setWhen(value as When)}>
                                            <SelectTrigger className="w-44" aria-label={t("When")}>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="before">{t("before expiry")}</SelectItem>
                                                <SelectItem value="on">{t("on the expiry day")}</SelectItem>
                                                <SelectItem value="after">{t("after expiry")}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Button type="button" variant="outline" onClick={addDay} disabled={!draftValid}>
                                            <Plus />
                                            {t("Add")}
                                        </Button>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {t("Up to 366 days before expiry or 30 days after. For example, 7 days before expiry and 1 day after expiry.")}
                                    </p>
                                    <FieldError message={dayErrors[0]} />
                                </div>

                                <div className="flex flex-col gap-2 sm:max-w-xs">
                                    <Label htmlFor="cron_hour">{t("Send reminders at")}</Label>
                                    <Select value={form.data.cron_hour} onValueChange={(value) => form.setData("cron_hour", value)}>
                                        <SelectTrigger id="cron_hour" className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Array.from({ length: 24 }, (_, hour) => (
                                                <SelectItem key={hour} value={String(hour)}>
                                                    {hourLabel(hour)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <p className="text-xs text-muted-foreground">{t("Server time. After changing it, update the cron command on your server.")}</p>
                                    <FieldError message={errors.cron_hour} />
                                </div>
                            </CardContent>
                            <CardFooter className="flex flex-wrap justify-between gap-2 border-t pt-6">
                                <div className="flex flex-wrap gap-2">
                                    <Button type="button" variant="outline" onClick={() => action("test")} disabled={busy !== null}>
                                        {busy === "test" ? <Loader2 className="animate-spin" /> : <MailCheck />}
                                        {t("Send me a test")}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={() => setConfirmRun(true)} disabled={busy !== null}>
                                        <Play />
                                        {t("Run now")}
                                    </Button>
                                </div>
                                <Button type="submit" disabled={form.processing || !form.isDirty}>
                                    {form.processing && <Loader2 className="animate-spin" />}
                                    {t("Save changes")}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("Server setup")}</CardTitle>
                            <CardDescription>{t("Add these entries to your server's crontab (in cPanel: Cron Jobs). This is a one-time setup.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <Command
                                label={t("Daily tasks")}
                                hint={t("Runs once a day at :time to send the reminders above.").replace(":time", hourLabel(Number(form.data.cron_hour)))}
                                value={cronCommand}
                            />
                            <Command
                                label={t("Queue worker")}
                                hint={t("Runs every minute to process background jobs, such as translation sync. With SSH or Supervisor, run “php artisan queue:work” as a service instead.")}
                                value={queueCommand}
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmDialog
                open={confirmRun}
                onOpenChange={setConfirmRun}
                title={t("Send today's reminders now?")}
                description={t("Customers whose plans match today's reminder days will get their email now. If the daily job also runs today, they may get it twice.")}
                confirmLabel={t("Run now")}
                processing={busy === "run"}
                onConfirm={() => action("run")}
            />
        </>
    );
}

function ReminderChip({ label, onRemove, removeLabel, after = false }: { label: string; onRemove: () => void; removeLabel: string; after?: boolean }) {
    return (
        <span
            className={cn(
                "inline-flex items-center gap-1.5 rounded-full border bg-background py-1 ps-3 pe-1 text-sm",
                after && "border-destructive/40 bg-destructive/5",
            )}
        >
            <Bell className={cn("size-3.5 text-muted-foreground", after && "text-destructive")} />
            {label}
            <button
                type="button"
                onClick={onRemove}
                className="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                aria-label={removeLabel}
            >
                <X className="size-3.5" />
            </button>
        </span>
    );
}

function Command({ label, hint, value }: { label: string; hint: string; value: string }) {
    return (
        <div className="flex flex-col gap-2">
            <Label>{label}</Label>
            <div className="flex items-center gap-2">
                <code className="min-w-0 flex-1 overflow-x-auto rounded-md border bg-muted px-3 py-2 font-mono text-xs whitespace-nowrap">{value}</code>
                <CopyButton value={value} />
            </div>
            <p className="text-xs text-muted-foreground">{hint}</p>
        </div>
    );
}
