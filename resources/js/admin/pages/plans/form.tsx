import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Contact, Info, Loader2, Save, Store } from "lucide-react";
import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@admin/components/ui/select";
import { Switch } from "@admin/components/ui/switch";
import { Textarea } from "@admin/components/ui/textarea";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import { LimitInput } from "./components/limit-input";
import {
    COMMON_FEATURES,
    FOREVER,
    includesCards,
    includesStores,
    periodLabel,
    STORE_LIMITS,
    VCARD_FEATURES,
    VCARD_LIMITS,
    type Plan,
    type PlanType,
    type Plugins,
} from "./types";

type Term = "monthly" | "yearly" | "custom";

const TYPE_OPTIONS: { value: PlanType; label: string; hint: string }[] = [
    {
        value: "BOTH",
        label: "Cards + stores",
        hint: "Digital business cards and WhatsApp stores",
    },
    {
        value: "VCARD",
        label: "Business cards",
        hint: "Digital business cards only",
    },
    { value: "STORE", label: "Stores", hint: "WhatsApp stores only" },
];

const termFor = (validity: number): Term =>
    validity === 30 ? "monthly" : validity === 365 ? "yearly" : "custom";

const str = (value: number | null | undefined, fallback = "") =>
    value === null || value === undefined ? fallback : String(value);

export default function PlanForm({
    plan,
    plugins,
}: {
    plan: Plan | null;
    plugins: Plugins;
}) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const editing = plan !== null;

    const form = useForm({
        plan_id: plan?.id ?? "",
        plan_type: (plan?.type ?? "BOTH") as PlanType,
        plan_name: plan?.name ?? "",
        plan_description: plan?.description ?? "",
        plan_price: str(plan?.price, "0"),
        term: termFor(plan?.validity ?? 30) as Term,
        validity: str(plan?.validity, "30"),
        trial_period: str(plan?.trial, "0"),
        storage: str(plan?.storage, "100"),
        recommended: plan?.recommended ?? false,
        is_private: plan?.private ?? false,
        ai_credits: str(plan?.aiCredits, "0"),
        trial_ai_credits: str(plan?.trialAiCredits, "0"),
        google_wallet: plan?.googleWallet ?? false,
        no_of_google_wallets: str(plan?.googleWallets, "0"),
        ...(Object.fromEntries(
            [...VCARD_LIMITS, ...STORE_LIMITS].map(([key]) => [
                key,
                str(plan?.limits[key], "1"),
            ]),
        ) as Record<
            (typeof VCARD_LIMITS)[number][0] | (typeof STORE_LIMITS)[number][0],
            string
        >),
        ...(Object.fromEntries(
            [...VCARD_FEATURES, ...COMMON_FEATURES].map(([key]) => [
                key,
                plan?.features[key] ?? key === "pwa",
            ]),
        ) as Record<
            | (typeof VCARD_FEATURES)[number][0]
            | (typeof COMMON_FEATURES)[number][0],
            boolean
        >),
        paddle_plan_price_id: plan?.integrations.paddle_plan_price_id ?? "",
        dodo_payments_plan_price_id:
            plan?.integrations.dodo_payments_plan_price_id ?? "",
        paypal_plan_id: plan?.integrations.paypal_plan_id ?? "",
        in_app_purchase_type:
            plan?.integrations.in_app_purchase_type ?? "one_time",
        in_app_product_id: plan?.integrations.in_app_product_id ?? "",
        in_app_purchase_id: plan?.integrations.in_app_purchase_id ?? "",
    });

    const { data, setData, errors } = form;
    const free = Number(data.plan_price) === 0;
    const validity = free ? FOREVER : Number(data.validity) || 0;
    const cards = includesCards(data.plan_type);
    const stores = includesStores(data.plan_type);

    const setTerm = (term: Term) => {
        setData((current) => ({
            ...current,
            term,
            validity:
                term === "monthly"
                    ? "30"
                    : term === "yearly"
                      ? "365"
                      : current.validity === String(FOREVER)
                        ? ""
                        : current.validity,
        }));
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((values) => ({
            ...values,
            validity: free ? FOREVER : values.validity,
        }));
        form.post(
            editing ? route("admin.update.plan") : route("admin.save.plan"),
            { preserveScroll: true },
        );
    };

    const integrations =
        plugins.paddle || plugins.dodo || plugins.paypal || plugins.inApp;

    return (
        <>
            <Head
                title={editing ? `${t("Edit")} ${plan.name}` : t("New plan")}
            />
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col gap-6 p-4 lg:p-6"
            >
                <PageHeader
                    title={editing ? plan.name : t("New plan")}
                    description={t(
                        "Set the price, what's included and the limits for this plan.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Plans"), href: route("admin.plans") },
                        { label: editing ? t("Edit") : t("New") },
                    ]}
                    actions={
                        <>
                            <Button variant="outline" asChild>
                                <Link href={route("admin.plans")}>
                                    {t("Cancel")}
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={
                                    form.processing ||
                                    (editing && !form.isDirty)
                                }
                            >
                                {form.processing ? (
                                    <Loader2 className="animate-spin" />
                                ) : (
                                    <Save />
                                )}
                                {editing ? t("Save changes") : t("Create plan")}
                            </Button>
                        </>
                    }
                />

                {editing && plan.subscribers > 0 && (
                    <Alert variant="info" className="max-w-6xl">
                        <Info />
                        <AlertDescription className="text-foreground">
                            {t(
                                ":count customers are on this plan. They keep the price and limits they bought until they renew; changes apply to new purchases and renewals.",
                            ).replace(":count", String(plan.subscribers))}
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid max-w-6xl gap-6 lg:grid-cols-3">
                    <div className="flex flex-col gap-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Basics")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-5">
                                <div className="flex flex-col gap-2">
                                    <Label>{t("Plan includes")}</Label>
                                    <div className="grid gap-2 sm:grid-cols-3">
                                        {TYPE_OPTIONS.map((option) => (
                                            <button
                                                key={option.value}
                                                type="button"
                                                onClick={() =>
                                                    setData(
                                                        "plan_type",
                                                        option.value,
                                                    )
                                                }
                                                className={cn(
                                                    "flex flex-col gap-1 rounded-lg border p-3 text-start transition-colors hover:bg-muted/50",
                                                    data.plan_type ===
                                                        option.value &&
                                                        "border-primary bg-primary/5 ring-1 ring-primary",
                                                )}
                                            >
                                                <span className="flex items-center gap-1.5 text-sm font-medium">
                                                    {option.value !==
                                                        "STORE" && (
                                                        <Contact className="size-4" />
                                                    )}
                                                    {option.value !==
                                                        "VCARD" && (
                                                        <Store className="size-4" />
                                                    )}
                                                    {t(option.label)}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {t(option.hint)}
                                                </span>
                                            </button>
                                        ))}
                                    </div>
                                    <FieldError message={errors.plan_type} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="plan_name">
                                        {t("Name")}
                                    </Label>
                                    <Input
                                        id="plan_name"
                                        value={data.plan_name}
                                        onChange={(event) =>
                                            setData(
                                                "plan_name",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t("e.g., Professional")}
                                        aria-invalid={!!errors.plan_name}
                                        maxLength={191}
                                        required
                                    />
                                    <FieldError message={errors.plan_name} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="plan_description">
                                        {t("Description")}
                                    </Label>
                                    <Textarea
                                        id="plan_description"
                                        value={data.plan_description}
                                        onChange={(event) =>
                                            setData(
                                                "plan_description",
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t(
                                            "A short line shown on the pricing page",
                                        )}
                                        aria-invalid={!!errors.plan_description}
                                        maxLength={1000}
                                        className="min-h-20"
                                        required
                                    />
                                    <FieldError
                                        message={errors.plan_description}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Pricing")}</CardTitle>
                                <CardDescription>
                                    {t(
                                        "Set the price to 0 for a free plan; free plans never expire.",
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-5 sm:grid-cols-2">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="plan_price">
                                        {t("Price")}
                                    </Label>
                                    <div className="relative">
                                        <span className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                                            {currency.symbol}
                                        </span>
                                        <Input
                                            id="plan_price"
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            value={data.plan_price}
                                            onChange={(event) =>
                                                setData(
                                                    "plan_price",
                                                    event.target.value,
                                                )
                                            }
                                            aria-invalid={!!errors.plan_price}
                                            className="ps-8"
                                            required
                                        />
                                    </div>
                                    <FieldError message={errors.plan_price} />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label>{t("Billing period")}</Label>
                                    <Select
                                        value={free ? "forever" : data.term}
                                        onValueChange={(value) =>
                                            setTerm(value as Term)
                                        }
                                        disabled={free}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {free && (
                                                <SelectItem value="forever">
                                                    {t("Forever (free plan)")}
                                                </SelectItem>
                                            )}
                                            <SelectItem value="monthly">
                                                {t("Monthly (30 days)")}
                                            </SelectItem>
                                            <SelectItem value="yearly">
                                                {t("Yearly (365 days)")}
                                            </SelectItem>
                                            <SelectItem value="custom">
                                                {t("Custom number of days")}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                {!free && data.term === "custom" && (
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="validity">
                                            {t("Validity (days)")}
                                        </Label>
                                        <Input
                                            id="validity"
                                            type="number"
                                            min={1}
                                            max={FOREVER}
                                            value={data.validity}
                                            onChange={(event) =>
                                                setData(
                                                    "validity",
                                                    event.target.value,
                                                )
                                            }
                                            aria-invalid={!!errors.validity}
                                            required
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            {t(
                                                "Use 9999 for a plan that never expires.",
                                            )}
                                        </p>
                                        <FieldError message={errors.validity} />
                                    </div>
                                )}
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="trial_period">
                                        {t("Free trial (days)")}
                                    </Label>
                                    <Input
                                        id="trial_period"
                                        type="number"
                                        min={0}
                                        value={data.trial_period}
                                        onChange={(event) =>
                                            setData(
                                                "trial_period",
                                                event.target.value,
                                            )
                                        }
                                        aria-invalid={!!errors.trial_period}
                                        required
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        {t("0 turns the trial off.")}
                                    </p>
                                    <FieldError message={errors.trial_period} />
                                </div>
                            </CardContent>
                        </Card>

                        {cards && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Contact className="size-4" />
                                        {t("Business card limits")}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-6">
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        {VCARD_LIMITS.map(([key, label]) => (
                                            <LimitInput
                                                key={key}
                                                id={key}
                                                label={t(label)}
                                                value={data[key]}
                                                onChange={(value) =>
                                                    setData(key, value)
                                                }
                                                error={errors[key]}
                                            />
                                        ))}
                                        <LimitInput
                                            id="ai_credits"
                                            label={t("AI credits")}
                                            value={data.ai_credits}
                                            onChange={(value) =>
                                                setData("ai_credits", value)
                                            }
                                            unlimitedValue={-1}
                                            error={errors.ai_credits}
                                            hint={t("0 means no AI credits.")}
                                        />
                                        <LimitInput
                                            id="trial_ai_credits"
                                            label={t("AI credits during trial")}
                                            value={data.trial_ai_credits}
                                            onChange={(value) =>
                                                setData(
                                                    "trial_ai_credits",
                                                    value,
                                                )
                                            }
                                            unlimitedValue={null}
                                            error={errors.trial_ai_credits}
                                        />
                                        {plugins.googleWallet && (
                                            <LimitInput
                                                id="no_of_google_wallets"
                                                label={t(
                                                    "Google Wallet passes",
                                                )}
                                                value={
                                                    data.no_of_google_wallets
                                                }
                                                onChange={(value) =>
                                                    setData(
                                                        "no_of_google_wallets",
                                                        value,
                                                    )
                                                }
                                                error={
                                                    errors.no_of_google_wallets
                                                }
                                            />
                                        )}
                                    </div>
                                    <FeatureGrid
                                        features={[
                                            ...VCARD_FEATURES,
                                            ...(plugins.googleWallet
                                                ? ([
                                                      [
                                                          "google_wallet",
                                                          "Google Wallet",
                                                      ],
                                                  ] as const)
                                                : []),
                                        ]}
                                        data={data}
                                        onChange={(key, value) =>
                                            setData(key, value)
                                        }
                                    />
                                </CardContent>
                            </Card>
                        )}

                        {stores && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Store className="size-4" />
                                        {t("Store limits")}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-5 sm:grid-cols-2">
                                    {STORE_LIMITS.map(([key, label]) => (
                                        <LimitInput
                                            key={key}
                                            id={key}
                                            label={t(label)}
                                            value={data[key]}
                                            onChange={(value) =>
                                                setData(key, value)
                                            }
                                            error={errors[key]}
                                        />
                                    ))}
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    {t("Included for every card and store")}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-6">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <LimitInput
                                        id="storage"
                                        label={t("Media storage")}
                                        value={data.storage}
                                        onChange={(value) =>
                                            setData("storage", value)
                                        }
                                        error={errors.storage}
                                        suffix="MB"
                                        hint={t("Per customer.")}
                                    />
                                </div>
                                <FeatureGrid
                                    features={COMMON_FEATURES}
                                    data={data}
                                    onChange={(key, value) =>
                                        setData(key, value)
                                    }
                                />
                            </CardContent>
                        </Card>

                        {integrations && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {t("Payment integrations")}
                                    </CardTitle>
                                    <CardDescription>
                                        {t(
                                            "IDs of this plan in recurring payment gateways and app stores. Leave empty if not used.",
                                        )}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="grid gap-5 sm:grid-cols-2">
                                    {plugins.paddle && (
                                        <TextField
                                            id="paddle_plan_price_id"
                                            label={t("Paddle price ID")}
                                            value={data.paddle_plan_price_id}
                                            error={errors.paddle_plan_price_id}
                                            onChange={(v) =>
                                                setData(
                                                    "paddle_plan_price_id",
                                                    v,
                                                )
                                            }
                                        />
                                    )}
                                    {plugins.dodo && (
                                        <TextField
                                            id="dodo_payments_plan_price_id"
                                            label={t("Dodo Payments price ID")}
                                            value={
                                                data.dodo_payments_plan_price_id
                                            }
                                            error={
                                                errors.dodo_payments_plan_price_id
                                            }
                                            onChange={(v) =>
                                                setData(
                                                    "dodo_payments_plan_price_id",
                                                    v,
                                                )
                                            }
                                        />
                                    )}
                                    {plugins.paypal && (
                                        <TextField
                                            id="paypal_plan_id"
                                            label={t("PayPal plan ID")}
                                            value={data.paypal_plan_id}
                                            error={errors.paypal_plan_id}
                                            onChange={(v) =>
                                                setData("paypal_plan_id", v)
                                            }
                                        />
                                    )}
                                    {plugins.inApp && (
                                        <>
                                            <div className="flex flex-col gap-2">
                                                <Label>
                                                    {t("In-app purchase type")}
                                                </Label>
                                                <Select
                                                    value={
                                                        data.in_app_purchase_type
                                                    }
                                                    onValueChange={(value) =>
                                                        setData(
                                                            "in_app_purchase_type",
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger className="w-full">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="one_time">
                                                            {t("One time")}
                                                        </SelectItem>
                                                        <SelectItem value="recurring">
                                                            {t("Recurring")}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            {data.in_app_purchase_type ===
                                                "recurring" && (
                                                <TextField
                                                    id="in_app_product_id"
                                                    label={t(
                                                        "In-app product ID",
                                                    )}
                                                    value={
                                                        data.in_app_product_id
                                                    }
                                                    error={
                                                        errors.in_app_product_id
                                                    }
                                                    onChange={(v) =>
                                                        setData(
                                                            "in_app_product_id",
                                                            v,
                                                        )
                                                    }
                                                />
                                            )}
                                            <TextField
                                                id="in_app_purchase_id"
                                                label={t("In-app plan ID")}
                                                value={data.in_app_purchase_id}
                                                error={
                                                    errors.in_app_purchase_id
                                                }
                                                onChange={(v) =>
                                                    setData(
                                                        "in_app_purchase_id",
                                                        v,
                                                    )
                                                }
                                            />
                                        </>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <div className="flex flex-col gap-6 lg:sticky lg:top-20 lg:self-start">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t("Visibility")}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                <ToggleRow
                                    title={t("Recommended")}
                                    hint={t("Highlighted on the pricing page.")}
                                    checked={data.recommended}
                                    onChange={(value) =>
                                        setData("recommended", value)
                                    }
                                />
                                <ToggleRow
                                    title={t("Private")}
                                    hint={t(
                                        "Hidden from the pricing page; only admins can assign it.",
                                    )}
                                    checked={data.is_private}
                                    onChange={(value) =>
                                        setData("is_private", value)
                                    }
                                />
                            </CardContent>
                        </Card>

                        <Card className="gap-3 bg-muted/30">
                            <CardHeader>
                                <CardDescription>
                                    {t("Summary")}
                                </CardDescription>
                                <CardTitle className="text-lg">
                                    {data.plan_name || t("Untitled plan")}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-1 text-sm">
                                <span className="text-2xl font-semibold tabular-nums">
                                    {free
                                        ? t("Free")
                                        : formatCurrency(
                                              Number(data.plan_price) || 0,
                                              currency,
                                          )}
                                    <span className="ms-1.5 text-sm font-normal text-muted-foreground">
                                        {periodLabel(validity, t)}
                                    </span>
                                </span>
                                {Number(data.trial_period) > 0 && !free && (
                                    <span className="text-muted-foreground">
                                        {data.trial_period}{" "}
                                        {t("day free trial")}
                                    </span>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </>
    );
}

function FeatureGrid<K extends string>({
    features,
    data,
    onChange,
}: {
    features: readonly (readonly [K, string])[];
    data: Record<K, boolean | string>;
    onChange: (key: K, value: boolean) => void;
}) {
    const { t } = useTranslation();
    return (
        <div className="grid gap-2 sm:grid-cols-2">
            {features.map(([key, label]) => (
                <label
                    key={key}
                    className="flex cursor-pointer items-center justify-between gap-3 rounded-lg border px-3 py-2.5"
                >
                    <span className="text-sm">{t(label)}</span>
                    <Switch
                        checked={Boolean(data[key])}
                        onCheckedChange={(checked) => onChange(key, checked)}
                    />
                </label>
            ))}
        </div>
    );
}

function ToggleRow({
    title,
    hint,
    checked,
    onChange,
}: {
    title: string;
    hint: string;
    checked: boolean;
    onChange: (value: boolean) => void;
}) {
    return (
        <label className="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-3">
            <span className="flex flex-col gap-0.5">
                <span className="text-sm font-medium">{title}</span>
                <span className="text-xs text-muted-foreground">{hint}</span>
            </span>
            <Switch checked={checked} onCheckedChange={onChange} />
        </label>
    );
}

function TextField({
    id,
    label,
    value,
    error,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="flex flex-col gap-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                aria-invalid={!!error}
                maxLength={255}
                dir="ltr"
            />
            <FieldError message={error} />
        </div>
    );
}
