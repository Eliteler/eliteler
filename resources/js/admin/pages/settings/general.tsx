import { Head, useForm, usePage } from "@inertiajs/react";
import { Check, Loader2 } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Combobox, type ComboboxOption } from "@admin/components/combobox";
import { FieldError } from "@admin/components/field-error";
import { ImageInput } from "@admin/components/image-input";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@admin/components/ui/select";
import { Switch } from "@admin/components/ui/switch";
import { Textarea } from "@admin/components/ui/textarea";
import { ACCENTS, type Accent, saveAccent, storedAccent } from "@admin/lib/theme";
import { cn } from "@admin/lib/utils";

type Values = {
    site_name: string;
    show_website: boolean;
    website_redirect_url: string;
    allow_registration: boolean;
    free_plan_on_signup: boolean;
    timezone: string;
    date_time_format: string;
    default_language: string;
    languages: string[];
    currency: string;
    currency_format: string;
    currency_decimals_place: string;
    image_limit: string;
    share_content: string;
};

type Props = {
    values: Values;
    images: { logo: string | null; logoLight: string | null; favicon: string | null };
    options: {
        timezones: string[];
        currencies: ComboboxOption[];
        currencyFormats: string[];
        dateFormats: ComboboxOption[];
        languages: ComboboxOption[];
    };
    maxUploadKb: number;
};

type FormData = Values & { site_logo: File | null; site_logo_light: File | null; favi_icon: File | null };

export default function GeneralSettings({ values, images, options, maxUploadKb }: Props) {
    const { t } = useTranslation();
    const form = useForm<FormData>({ ...values, site_logo: null, site_logo_light: null, favi_icon: null });
    const { data, setData, errors } = form;
    const timezones = options.timezones.map((zone) => ({ value: zone, label: zone.replaceAll("_", " ") }));
    const placeholders = [
        { code: "{ business_name }", label: t("Business name") },
        { code: "{ business_url }", label: t("Business card link") },
        { code: "{ appName }", label: t("Website name") },
    ];
    const languageName = (code: string) => options.languages.find((language) => language.value === code)?.label ?? code;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            show_website: current.show_website ? 1 : 0,
            allow_registration: current.allow_registration ? 1 : 0,
            free_plan_on_signup: current.free_plan_on_signup ? 1 : 0,
        }));
        form.post(route("admin.change.general.settings"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setData((current) => ({ ...current, site_logo: null, site_logo_light: null, favi_icon: null })),
        });
    };

    const toggleLanguage = (code: string) => {
        const next = data.languages.includes(code) ? data.languages.filter((item) => item !== code) : [...data.languages, code];
        setData((current) => ({
            ...current,
            languages: next,
            default_language: next.includes(current.default_language) ? current.default_language : (next[0] ?? ""),
        }));
    };

    const insertPlaceholder = (code: string) => setData("share_content", `${data.share_content}${data.share_content.endsWith(" ") || !data.share_content ? "" : " "}${code}`);

    return (
        <>
            <Head title={t("General settings")} />
            <form onSubmit={submit} className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("General settings")}
                    description={t("Your website's name, branding, sign-ups, language and currency.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("General settings") }]}
                />

                <div className="grid max-w-5xl gap-6">
                    <AccentPicker />

                    <Section title={t("Branding")} description={t("Shown in the browser tab, emails and across the website.")}>
                        <Field id="site_name" label={t("Website name")} error={errors.site_name}>
                            <Input id="site_name" value={data.site_name} onChange={(e) => setData("site_name", e.target.value)} aria-invalid={!!errors.site_name} required />
                        </Field>
                        <div className="grid gap-5 sm:grid-cols-3">
                            {(
                                [
                                    ["site_logo", t("Logo"), t("For light backgrounds."), images.logo],
                                    ["site_logo_light", t("Logo for dark backgrounds"), t("Usually a white or light version."), images.logoLight],
                                    ["favi_icon", t("Favicon"), t("Square image, at least 32×32 px."), images.favicon],
                                ] as const
                            ).map(([name, label, hint, current]) => (
                                <div key={name} className="flex flex-col gap-2">
                                    <Label>{label}</Label>
                                    <ImageInput
                                        value={data[name]}
                                        currentUrl={current}
                                        onChange={(file) => setData(name, file)}
                                        maxKb={maxUploadKb}
                                        invalid={!!errors[name]}
                                        className="flex-col"
                                        fit="contain"
                                        previewClassName={cn("aspect-[5/2] h-auto w-full", name === "site_logo_light" ? "bg-neutral-900" : "bg-white")}
                                    />
                                    <p className="text-xs text-muted-foreground">{hint}</p>
                                    <FieldError message={errors[name]} />
                                </div>
                            ))}
                        </div>
                    </Section>

                    <Section title={t("Website and sign-ups")} description={t("Control what visitors see and who can create an account.")}>
                        <Toggle
                            id="show_website"
                            label={t("Show the landing page")}
                            description={t("When off, visitors to your home page are sent to the address below.")}
                            checked={data.show_website}
                            onChange={(checked) => setData("show_website", checked)}
                        />
                        {!data.show_website && (
                            <Field id="website_redirect_url" label={t("Send visitors to")} error={errors.website_redirect_url}>
                                <Input
                                    id="website_redirect_url"
                                    type="url"
                                    value={data.website_redirect_url}
                                    onChange={(e) => setData("website_redirect_url", e.target.value)}
                                    placeholder="https://example.com"
                                    aria-invalid={!!errors.website_redirect_url}
                                />
                            </Field>
                        )}
                        <Toggle
                            id="allow_registration"
                            label={t("Allow new sign-ups")}
                            description={t("When off, only existing customers can sign in.")}
                            checked={data.allow_registration}
                            onChange={(checked) => setData("allow_registration", checked)}
                        />
                        <Toggle
                            id="free_plan_on_signup"
                            label={t("Give new customers the free plan")}
                            description={t("Activates your free plan automatically after sign-up. You need an active free plan for this.")}
                            checked={data.free_plan_on_signup}
                            onChange={(checked) => setData("free_plan_on_signup", checked)}
                        />
                    </Section>

                    <Section title={t("Language and region")} description={t("Languages customers can choose from, and how dates and times appear.")}>
                        <div className="grid gap-5 sm:grid-cols-2">
                            <Field id="timezone" label={t("Timezone")} error={errors.timezone}>
                                <Combobox id="timezone" value={data.timezone} options={timezones} onChange={(value) => setData("timezone", value)} invalid={!!errors.timezone} />
                            </Field>
                            <Field id="date_time_format" label={t("Date and time format")} error={errors.date_time_format}>
                                <Combobox
                                    id="date_time_format"
                                    value={data.date_time_format}
                                    options={options.dateFormats}
                                    onChange={(value) => setData("date_time_format", value)}
                                    invalid={!!errors.date_time_format}
                                />
                            </Field>
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label>{t("Enabled languages")}</Label>
                            <div className="flex flex-wrap gap-1.5">
                                {options.languages.map((language) => {
                                    const on = data.languages.includes(language.value);
                                    return (
                                        <button
                                            key={language.value}
                                            type="button"
                                            aria-pressed={on}
                                            onClick={() => toggleLanguage(language.value)}
                                            className={cn(
                                                "rounded-full border px-3 py-1 text-sm transition-colors",
                                                on ? "border-primary bg-primary text-primary-foreground" : "text-muted-foreground hover:bg-muted",
                                            )}
                                        >
                                            {language.label}
                                        </button>
                                    );
                                })}
                            </div>
                            <FieldError message={errors.languages} />
                        </div>
                        <Field id="default_language" label={t("Default language")} error={errors.default_language} className="sm:max-w-xs">
                            <Select value={data.default_language} onValueChange={(value) => setData("default_language", value)}>
                                <SelectTrigger id="default_language" className="w-full" aria-invalid={!!errors.default_language}>
                                    <SelectValue placeholder={t("Select…")} />
                                </SelectTrigger>
                                <SelectContent>
                                    {data.languages.map((code) => (
                                        <SelectItem key={code} value={code}>
                                            {languageName(code)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                    </Section>

                    <Section title={t("Currency")} description={t("Used for plan prices, invoices and payments.")}>
                        <div className="grid gap-5 sm:grid-cols-3">
                            <Field id="currency" label={t("Currency")} error={errors.currency}>
                                <Combobox id="currency" value={data.currency} options={options.currencies} onChange={(value) => setData("currency", value)} invalid={!!errors.currency} />
                            </Field>
                            <Field id="currency_format" label={t("Number format")} error={errors.currency_format}>
                                <Select value={data.currency_format} onValueChange={(value) => setData("currency_format", value)}>
                                    <SelectTrigger id="currency_format" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.currencyFormats.map((format) => (
                                            <SelectItem key={format} value={format}>
                                                <span className="font-mono">{format}</span>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                            <Field id="currency_decimals_place" label={t("Decimal places")} error={errors.currency_decimals_place}>
                                <Select value={data.currency_decimals_place} onValueChange={(value) => setData("currency_decimals_place", value)}>
                                    <SelectTrigger id="currency_decimals_place" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {["0", "1", "2", "3", "4"].map((places) => (
                                            <SelectItem key={places} value={places}>
                                                {places === "0" ? t("None") : places}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                        </div>
                    </Section>

                    <Section title={t("Uploads")} description={t("The largest file anyone can upload, including you.")}>
                        <Field id="image_limit" label={t("Maximum upload size")} error={errors.image_limit} className="sm:max-w-xs">
                            <div className="flex items-center gap-2">
                                <Input
                                    id="image_limit"
                                    type="number"
                                    min={1024}
                                    step={1}
                                    value={data.image_limit}
                                    onChange={(e) => setData("image_limit", e.target.value)}
                                    aria-invalid={!!errors.image_limit}
                                    required
                                />
                                <span className="text-sm text-muted-foreground">KB</span>
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {t("About :size MB. Your server's PHP upload limit still applies.").replace(
                                    ":size",
                                    (Number(data.image_limit || 0) / 1024).toFixed(1).replace(/\.0$/, ""),
                                )}
                            </p>
                        </Field>
                    </Section>

                    <Section title={t("Share message")} description={t("The text customers share along with their business card link.")}>
                        <Field id="share_content" label={t("Message")} error={errors.share_content}>
                            <Textarea
                                id="share_content"
                                value={data.share_content}
                                onChange={(e) => setData("share_content", e.target.value)}
                                className="min-h-24"
                                aria-invalid={!!errors.share_content}
                                required
                            />
                        </Field>
                        <div className="flex flex-wrap items-center gap-2 text-sm">
                            <span className="text-muted-foreground">{t("Insert:")}</span>
                            {placeholders.map((placeholder) => (
                                <Button key={placeholder.code} type="button" variant="outline" size="sm" onClick={() => insertPlaceholder(placeholder.code)}>
                                    {placeholder.label}
                                </Button>
                            ))}
                        </div>
                    </Section>
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button type="submit" disabled={form.processing || !form.isDirty}>
                        {form.processing && <Loader2 className="animate-spin" />}
                        {t("Save changes")}
                    </Button>
                </div>
            </form>
        </>
    );
}

/** Accent colour for this browser only (localStorage), applied immediately. */
function AccentPicker() {
    const { t } = useTranslation();
    const { ui } = usePage().props;
    const [current, setCurrent] = useState<Accent>(() => storedAccent() ?? (ui.accent as Accent));

    const choose = (accent: Accent) => {
        saveAccent(accent);
        setCurrent(accent);
    };

    return (
        <Section title={t("Accent colour")} description={t("Colour for buttons, links and charts in the admin panel. Saved in this browser only.")}>
            <div className="flex flex-wrap gap-3" role="radiogroup" aria-label={t("Accent colour")}>
                {ACCENTS.map((accent) => {
                    const active = current === accent.value;
                    return (
                        <button
                            key={accent.value}
                            type="button"
                            role="radio"
                            aria-checked={active}
                            aria-label={t(accent.label)}
                            title={t(accent.label)}
                            onClick={() => choose(accent.value)}
                            className={cn(
                                "flex size-9 cursor-pointer items-center justify-center rounded-full ring-offset-2 ring-offset-background",
                                active && "ring-2 ring-foreground/40",
                            )}
                            style={{ backgroundColor: accent.swatch }}
                        >
                            {active && <Check className="size-4 text-white" />}
                        </button>
                    );
                })}
            </div>
        </Section>
    );
}

function Section({ title, description, children }: { title: string; description: string; children: React.ReactNode }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>{description}</CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col gap-5">{children}</CardContent>
        </Card>
    );
}

function Field({ id, label, error, className, children }: { id: string; label: string; error?: string; className?: string; children: React.ReactNode }) {
    return (
        <div className={cn("flex flex-col gap-2", className)}>
            <Label htmlFor={id}>{label}</Label>
            {children}
            <FieldError message={error} />
        </div>
    );
}

function Toggle({ id, label, description, checked, onChange }: { id: string; label: string; description: string; checked: boolean; onChange: (checked: boolean) => void }) {
    return (
        <div className="flex items-start justify-between gap-4">
            <div className="flex flex-col gap-1">
                <Label htmlFor={id}>{label}</Label>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
            <Switch id={id} checked={checked} onCheckedChange={onChange} />
        </div>
    );
}
