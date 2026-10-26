/*
 | GoBiz Original — template settings: appearance, app banner, announcement bar and popup.
 | Built into ../../dist with `npm run plugin:build -- GoBizOriginal` and shipped in the template zip.
 */
import { Head, Link, useForm } from "@inertiajs/react";
import { Check, FileText, Loader2, Plus, Trash2 } from "lucide-react";
import { useTranslation } from "react-i18next";

import {
    Button,
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
    FieldError,
    ImageInput,
    Input,
    Label,
    PageHeader,
    RichTextEditor,
    Switch,
    Textarea,
    cn,
} from "@gobiz/admin";

type Color = { value: string; label: string; swatch: string };
type Announcement = { text: string; active: boolean };
type Props = {
    appearance: { template_color: string; theme_slider: boolean; custom_css: string; custom_js: string };
    images: { banner: string | null; auth: string | null; popup: string | null };
    colors: Color[];
    appBanner: { app_action: boolean; app_heading: string; app_description: string; google_play_store_link: string; apple_app_store_link: string };
    announcement: {
        announcement_bg: string;
        announcement_text_color: string;
        announcement_marquee: boolean;
        announcement_marquee_speed: string;
        announcements: Announcement[];
    };
    popup: { popup_status: boolean; popup_link: string };
    maxUploadKb: number;
};

export default function TemplateConfig(props: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t("Template settings")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Template settings")}
                    description={t("Colours, images, the announcement bar, the popup and the app banner for GoBiz Original.")}
                    breadcrumbs={[
                        { label: t("Dashboard"), href: route("admin.dashboard") },
                        { label: t("Web templates"), href: route("admin.web-templates") },
                        { label: "GoBiz Original", href: route("admin.web-template.gobiz-original") },
                        { label: t("Settings") },
                    ]}
                    actions={
                        <Button variant="secondary" asChild>
                            <Link href={route("admin.web-template.gobiz-original")}>
                                <FileText />
                                {t("Pages")}
                            </Link>
                        </Button>
                    }
                />
                <div className="grid max-w-3xl gap-6">
                    <Appearance {...props} />
                    <AnnouncementBar values={props.announcement} />
                    <Popup values={props.popup} image={props.images.popup} maxUploadKb={props.maxUploadKb} />
                    <AppBanner values={props.appBanner} />
                </div>
            </div>
        </>
    );
}

function SaveFooter({ processing, dirty }: { processing: boolean; dirty: boolean }) {
    const { t } = useTranslation();
    return (
        <CardFooter className="justify-end border-t pt-6">
            <Button type="submit" disabled={processing || !dirty}>
                {processing && <Loader2 className="animate-spin" />}
                {t("Save changes")}
            </Button>
        </CardFooter>
    );
}

function SwitchRow({ id, label, hint, checked, onChange }: { id: string; label: string; hint?: string; checked: boolean; onChange: (value: boolean) => void }) {
    return (
        <div className="flex items-start justify-between gap-4">
            <div className="flex flex-col gap-1">
                <Label htmlFor={id}>{label}</Label>
                {hint && <p className="text-sm text-muted-foreground">{hint}</p>}
            </div>
            <Switch id={id} checked={checked} onCheckedChange={onChange} />
        </div>
    );
}

function Appearance({ appearance, images, colors, maxUploadKb }: Props) {
    const { t } = useTranslation();
    const form = useForm({ ...appearance, banner_image: null as File | null, auth_image: null as File | null });
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ ...current, theme_slider: current.theme_slider ? 1 : 0 }));
        form.post(route("admin.web-template.gobiz-original.update-config"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.setData((current) => ({ ...current, banner_image: null, auth_image: null })),
        });
    };

    const image = (key: "banner_image" | "auth_image", label: string, current: string | null, hint: string) => (
        <div className="flex flex-col gap-2">
            <Label>{label}</Label>
            <ImageInput value={data[key]} currentUrl={current} onChange={(file) => setData(key, file)} maxKb={maxUploadKb} invalid={!!errors[key]} />
            <p className="text-xs text-muted-foreground">{hint}</p>
            <FieldError message={errors[key]} />
        </div>
    );

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Appearance")}</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-6">
                    <div className="flex flex-col gap-3">
                        <Label>{t("Colour")}</Label>
                        <div role="radiogroup" aria-label={t("Colour")} className="flex flex-wrap gap-2">
                            {colors.map((color) => {
                                const selected = data.template_color === color.value;
                                return (
                                    <button
                                        key={color.value}
                                        type="button"
                                        role="radio"
                                        aria-checked={selected}
                                        aria-label={color.label}
                                        title={color.label}
                                        onClick={() => setData("template_color", color.value)}
                                        className={cn(
                                            "flex size-8 items-center justify-center rounded-full text-white ring-offset-2 ring-offset-background transition",
                                            selected ? "ring-2 ring-ring" : "hover:scale-110",
                                        )}
                                        style={{ backgroundColor: color.swatch }}
                                    >
                                        {selected && <Check className="size-4" />}
                                    </button>
                                );
                            })}
                        </div>
                        <FieldError message={errors.template_color} />
                    </div>

                    <SwitchRow
                        id="theme_slider"
                        label={t("Show the themes slider on the home page")}
                        checked={data.theme_slider}
                        onChange={(checked) => setData("theme_slider", checked)}
                    />

                    <div className="grid gap-5 sm:grid-cols-2">
                        {image("banner_image", t("Home banner image"), images.banner, t("Recommended size: 1000 × 667 px."))}
                        {image("auth_image", t("Sign-in and sign-up image"), images.auth, t("Recommended size: 486 × 605 px."))}
                    </div>

                    <div className="flex flex-col gap-2">
                        <Label htmlFor="custom_css">{t("Custom CSS")}</Label>
                        <Textarea
                            id="custom_css"
                            rows={6}
                            value={data.custom_css}
                            onChange={(event) => setData("custom_css", event.target.value)}
                            placeholder=".my-class { color: red; }"
                            className="font-mono text-xs"
                            spellCheck={false}
                            aria-invalid={!!errors.custom_css}
                        />
                        <FieldError message={errors.custom_css} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="custom_js">{t("Custom JavaScript")}</Label>
                        <Textarea
                            id="custom_js"
                            rows={6}
                            value={data.custom_js}
                            onChange={(event) => setData("custom_js", event.target.value)}
                            placeholder="console.log('Hello');"
                            className="font-mono text-xs"
                            spellCheck={false}
                            aria-invalid={!!errors.custom_js}
                        />
                        <p className="text-xs text-muted-foreground">{t("Runs on every page of your website. Only add code from sources you trust.")}</p>
                        <FieldError message={errors.custom_js} />
                    </div>
                </CardContent>
                <SaveFooter processing={form.processing} dirty={form.isDirty} />
            </form>
        </Card>
    );
}

function AnnouncementBar({ values }: { values: Props["announcement"] }) {
    const { t } = useTranslation();
    const form = useForm(values);
    const { data, setData, errors } = form;
    const fieldErrors = errors as Record<string, string | undefined>;

    const update = (index: number, patch: Partial<Announcement>) =>
        setData("announcements", data.announcements.map((item, i) => (i === index ? { ...item, ...patch } : item)));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.web-template.gobiz-original.update-announcements"), { preserveScroll: true });
    };

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Announcement bar")}</CardTitle>
                    <CardDescription>{t("A strip at the top of every page for news, offers or notices.")}</CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-5">
                    <div className="grid gap-5 sm:grid-cols-2">
                        {(["announcement_bg", "announcement_text_color"] as const).map((key) => (
                            <div key={key} className="flex flex-col gap-2">
                                <Label htmlFor={key}>{key === "announcement_bg" ? t("Background colour") : t("Text colour")}</Label>
                                <div className="flex items-center gap-2">
                                    <input
                                        id={key}
                                        type="color"
                                        value={data[key]}
                                        onChange={(event) => setData(key, event.target.value)}
                                        className="h-9 w-12 shrink-0 cursor-pointer rounded-md border bg-transparent p-1"
                                    />
                                    <Input value={data[key]} onChange={(event) => setData(key, event.target.value)} className="font-mono text-sm" aria-label={key} />
                                </div>
                                <FieldError message={errors[key]} />
                            </div>
                        ))}
                    </div>

                    <SwitchRow
                        id="announcement_marquee"
                        label={t("Scroll the text")}
                        hint={t("Moves announcements across the bar instead of showing them still.")}
                        checked={data.announcement_marquee}
                        onChange={(checked) => setData("announcement_marquee", checked)}
                    />
                    {data.announcement_marquee && (
                        <div className="flex flex-col gap-2 sm:max-w-48">
                            <Label htmlFor="announcement_marquee_speed">{t("Scroll speed")}</Label>
                            <Input
                                id="announcement_marquee_speed"
                                type="number"
                                min={10}
                                max={500}
                                value={data.announcement_marquee_speed}
                                onChange={(event) => setData("announcement_marquee_speed", event.target.value)}
                                aria-invalid={!!errors.announcement_marquee_speed}
                            />
                            <p className="text-xs text-muted-foreground">{t("Higher is faster.")}</p>
                            <FieldError message={errors.announcement_marquee_speed} />
                        </div>
                    )}

                    <div className="flex flex-col gap-3">
                        <Label>{t("Announcements")}</Label>
                        {data.announcements.map((item, index) => (
                            <div key={index} className="flex flex-col gap-3 rounded-md border p-3">
                                <div className="flex items-center justify-between gap-3">
                                    <label className="flex items-center gap-2 text-sm">
                                        <Switch checked={item.active} onCheckedChange={(checked) => update(index, { active: checked })} />
                                        {t("Show")}
                                    </label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        aria-label={t("Remove")}
                                        onClick={() => setData("announcements", data.announcements.filter((_, i) => i !== index))}
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                                <RichTextEditor value={item.text} onChange={(html) => update(index, { text: html })} minHeight={160} invalid={!!fieldErrors[`announcements.${index}.text`]} />
                                <FieldError message={fieldErrors[`announcements.${index}.text`]} />
                            </div>
                        ))}
                        <div>
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                disabled={data.announcements.length >= 20}
                                onClick={() => setData("announcements", [...data.announcements, { text: "", active: true }])}
                            >
                                <Plus />
                                {t("Add announcement")}
                            </Button>
                        </div>
                    </div>

                    {data.announcements.some((item) => item.active && item.text) && (
                        <div className="flex flex-col gap-2">
                            <Label>{t("Preview")}</Label>
                            <div
                                className="truncate rounded-md px-3 py-2 text-center text-sm [&_*]:inline [&_p]:m-0"
                                style={{ backgroundColor: data.announcement_bg, color: data.announcement_text_color }}
                                dangerouslySetInnerHTML={{ __html: data.announcements.find((item) => item.active && item.text)?.text ?? "" }}
                            />
                        </div>
                    )}
                </CardContent>
                <SaveFooter processing={form.processing} dirty={form.isDirty} />
            </form>
        </Card>
    );
}

function Popup({ values, image, maxUploadKb }: { values: Props["popup"]; image: string | null; maxUploadKb: number }) {
    const { t } = useTranslation();
    const form = useForm({ ...values, popup_image: null as File | null });
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({ ...current, popup_status: current.popup_status ? 1 : 0 }));
        form.post(route("admin.web-template.gobiz-original.update-popup"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setData("popup_image", null),
        });
    };

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Popup")}</CardTitle>
                    <CardDescription>{t("An image shown over the website when visitors arrive, for example a sale or launch.")}</CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-5">
                    <SwitchRow id="popup_status" label={t("Show the popup")} checked={data.popup_status} onChange={(checked) => setData("popup_status", checked)} />
                    <div className="flex flex-col gap-2">
                        <Label>{t("Image")}</Label>
                        <ImageInput value={data.popup_image} currentUrl={image} onChange={(file) => setData("popup_image", file)} maxKb={maxUploadKb} invalid={!!errors.popup_image} />
                        <FieldError message={errors.popup_image} />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="popup_link">{t("Link (optional)")}</Label>
                        <Input
                            id="popup_link"
                            type="url"
                            value={data.popup_link}
                            onChange={(event) => setData("popup_link", event.target.value)}
                            placeholder="https://"
                            aria-invalid={!!errors.popup_link}
                        />
                        <p className="text-xs text-muted-foreground">{t("Where visitors go when they click the image.")}</p>
                        <FieldError message={errors.popup_link} />
                    </div>
                </CardContent>
                <SaveFooter processing={form.processing} dirty={form.isDirty} />
            </form>
        </Card>
    );
}

function AppBanner({ values }: { values: Props["appBanner"] }) {
    const { t } = useTranslation();
    const form = useForm(values);
    const { data, setData, errors } = form;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route("admin.web-template.gobiz-original.app-action-banner"), { preserveScroll: true });
    };

    const field = (key: "app_heading" | "google_play_store_link" | "apple_app_store_link", label: string, placeholder: string, type = "text") => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Input id={key} type={type} value={data[key]} onChange={(event) => setData(key, event.target.value)} placeholder={placeholder} aria-invalid={!!errors[key]} />
            <FieldError message={errors[key]} />
        </div>
    );

    return (
        <Card>
            <form onSubmit={submit} className="flex flex-col gap-6">
                <CardHeader>
                    <CardTitle>{t("Mobile app banner")}</CardTitle>
                    <CardDescription>{t("Invites visitors to download your mobile app.")}</CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-5">
                    <SwitchRow id="app_action" label={t("Show the app banner")} checked={data.app_action} onChange={(checked) => setData("app_action", checked)} />
                    {field("app_heading", t("Heading"), t("e.g., Your business, in your pocket"))}
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="app_description">{t("Description")}</Label>
                        <Textarea
                            id="app_description"
                            rows={2}
                            value={data.app_description}
                            onChange={(event) => setData("app_description", event.target.value)}
                            aria-invalid={!!errors.app_description}
                        />
                        <FieldError message={errors.app_description} />
                    </div>
                    <div className="grid gap-5 sm:grid-cols-2">
                        {field("google_play_store_link", t("Google Play link"), "https://play.google.com/store/apps/details?id=…", "url")}
                        {field("apple_app_store_link", t("App Store link"), "https://apps.apple.com/app/…", "url")}
                    </div>
                </CardContent>
                <SaveFooter processing={form.processing} dirty={form.isDirty} />
            </form>
        </Card>
    );
}
