import { Link, usePage } from "@inertiajs/react";
import { Archive, Check, MoreHorizontal, Pencil, Users, X } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Switch } from "@admin/components/ui/switch";
import { formatCurrency } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import {
    UNLIMITED,
    includesCards,
    includesStores,
    periodLabel,
    TYPE_LABELS,
    type Plan,
} from "../types";

/* ---------- row model ---------- */

type Row =
    | { kind: "limit"; label: string; value: number | null; unlimited?: number }
    | { kind: "flag"; label: string; on: boolean }
    | { kind: "storage"; label: string; value: number }
    | { kind: "always"; label: string };

type Section = { title: string; rows: Row[] };

/* ---------- row renderer ---------- */

function FeatureRow({ row }: { row: Row }) {
    const { t } = useTranslation();

    let on = true;
    let strong: string | null = null;

    switch (row.kind) {
        case "limit": {
            on = row.value !== null && row.value !== 0;
            if (on) {
                strong =
                    row.value === (row.unlimited ?? UNLIMITED)
                        ? t("Unlimited")
                        : String(row.value);
            }
            break;
        }
        case "storage": {
            on = row.value !== 0;
            if (on)
                strong =
                    row.value === UNLIMITED ? t("Unlimited") : `${row.value}MB`;
            break;
        }
        case "flag":
            on = row.on;
            break;
        case "always":
            on = true;
            break;
    }

    return (
        <li
            className={cn(
                "flex items-center gap-2 text-sm",
                !on && "opacity-50",
            )}
        >
            <span
                className={cn(
                    "flex size-5 shrink-0 items-center justify-center rounded-full",
                    on
                        ? "bg-success/10 text-success"
                        : "bg-muted text-muted-foreground",
                )}
            >
                {on ? <Check className="size-3" /> : <X className="size-3" />}
            </span>
            <span>
                {strong && (
                    <strong className="mr-1 font-medium text-foreground">
                        {strong}
                    </strong>
                )}
                {row.label}
            </span>
        </li>
    );
}

/* ---------- card ---------- */

export function PlanCard({
    plan,
    onToggle,
    onDelete,
    aiBuilderEnabled,
    googleWalletInstalled,
    nfcEnabled,
}: {
    plan: Plan;
    onToggle: () => void;
    onDelete: () => void;
    aiBuilderEnabled: boolean;
    googleWalletInstalled: boolean;
    nfcEnabled: boolean;
}) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const { limits: l, features: f } = plan;

    const sections: Section[] = [];

    /* vCard features */
    if (includesCards(plan.type)) {
        const rows: Row[] = [];

        if (aiBuilderEnabled) {
            if (plan.trial !== 0) {
                rows.push({
                    kind: "limit",
                    label: t("AI Credits (Trial)"),
                    value: plan.trialAiCredits,
                    unlimited: -1,
                });
            }
            rows.push({
                kind: "limit",
                label: t("AI Credit"),
                value: plan.aiCredits,
                unlimited: -1,
            });
        }

        rows.push(
            { kind: "limit", label: t("vCards"), value: l.no_of_vcards },
            { kind: "limit", label: t("Services"), value: l.no_of_services },
            {
                kind: "limit",
                label: t("Products"),
                value: l.no_of_vcard_products,
            },
            { kind: "limit", label: t("Links"), value: l.no_of_links },
            {
                kind: "limit",
                label: t("Payment Listed"),
                value: l.no_of_payments,
            },
            { kind: "limit", label: t("Galleries"), value: l.no_of_galleries },
            {
                kind: "limit",
                label: t("Testimonials"),
                value: l.no_testimonials,
            },
            { kind: "flag", label: t("Business Hours"), on: f.business_hours },
            { kind: "flag", label: t("Appointments"), on: f.appointment },
            {
                kind: "flag",
                label: t("Service Booking"),
                on: f.service_booking,
            },
            { kind: "flag", label: t("Contact Form"), on: f.contact_form },
            { kind: "limit", label: t("Enquiries"), value: l.no_of_enquires },
        );

        if (googleWalletInstalled && plan.googleWallet) {
            rows.push({
                kind: "limit",
                label: t("Google Wallets"),
                value: plan.googleWallets,
            });
        }

        rows.push({
            kind: "flag",
            label: t("Password Protected"),
            on: f.password_protected,
        });

        sections.push({ title: t("vCard Features"), rows });
    }

    /* Store features */
    if (includesStores(plan.type)) {
        sections.push({
            title: t("Store Features"),
            rows: [
                { kind: "limit", label: t("Stores"), value: l.no_of_stores },
                {
                    kind: "limit",
                    label: t("Categories"),
                    value: l.no_of_categories,
                },
                {
                    kind: "limit",
                    label: t("Products"),
                    value: l.no_of_store_products,
                },
            ],
        });
    }

    /* Additional features */
    const additional: Row[] = [
        { kind: "flag", label: t("Custom Domain"), on: f.custom_domain },
    ];
    if (nfcEnabled)
        additional.push({ kind: "always", label: t("Order NFC Card") });
    additional.push(
        { kind: "storage", label: t("storage limit"), value: plan.storage },
        {
            kind: "flag",
            label: t("Advanced Settings"),
            on: f.advanced_settings,
        },
        { kind: "flag", label: t("Progressive Web App (PWA)"), on: f.pwa },
        {
            kind: "flag",
            label: t("Personalized Link"),
            on: f.personalized_link,
        },
        { kind: "flag", label: t("Hide Branding"), on: f.hide_branding },
        { kind: "flag", label: t("Free Setup"), on: f.free_setup },
        { kind: "flag", label: t("Free Support"), on: f.free_support },
    );
    sections.push({ title: t("Additional Features"), rows: additional });

    const active = plan.status === "active";

    return (
        <Card
            className={cn(
                "relative gap-4",
                plan.recommended &&
                    active &&
                    "border-primary ring-1 ring-primary",
                !active && "bg-muted/30",
            )}
        >
            <CardHeader>
                <div className="flex flex-wrap items-center gap-1.5">
                    <Badge variant="secondary">
                        {t(TYPE_LABELS[plan.type])}
                    </Badge>
                </div>
                <CardAction>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                aria-label={t("Actions")}
                            >
                                <MoreHorizontal />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem asChild>
                                <Link href={route("admin.edit.plan", plan.id)}>
                                    <Pencil />
                                    {t("Edit Plan")}
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                variant="destructive"
                                onSelect={onDelete}
                            >
                                <Archive />
                                {t("Delete Plan")}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardAction>

                <CardTitle className="pt-2 text-xl font-bold">
                    {plan.name}
                </CardTitle>
            </CardHeader>

            <CardContent className="flex flex-1 flex-col gap-4">
                <div className="flex items-baseline gap-1.5">
                    <span className="text-3xl font-semibold tracking-tight tabular-nums">
                        {plan.price > 0
                            ? formatCurrency(plan.price, currency)
                            : t("Free")}
                    </span>
                    <span className="text-sm text-muted-foreground">
                        {periodLabel(plan.validity, t)}
                    </span>
                </div>

                {plan.description && (
                    <CardDescription>{plan.description}</CardDescription>
                )}

                <hr />

                <div className="flex flex-col gap-4">
                    {sections.map((section) => (
                        <div
                            key={section.title}
                            className="flex flex-col gap-2"
                        >
                            <h4 className="text-xs font-bold uppercase tracking-wide text-muted-foreground">
                                {section.title}
                            </h4>
                            <ul className="grid gap-2">
                                {section.rows.map((row, i) => (
                                    <FeatureRow
                                        key={`${row.label}-${i}`}
                                        row={row}
                                    />
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            </CardContent>

            <CardFooter className="justify-between gap-2 border-t pt-4 [.border-t]:pt-4">
                <span
                    className="flex items-center gap-1.5 text-sm text-muted-foreground"
                    title={t("Customers currently on this plan")}
                >
                    <Users className="size-4" />
                    <span className="font-medium text-foreground tabular-nums">
                        {plan.subscribers}
                    </span>
                    {t("active")}
                </span>
                <label className="flex items-center gap-2 text-sm">
                    <span className="text-muted-foreground">
                        {plan.status === "active"
                            ? t("Enabled")
                            : t("Disabled")}
                    </span>
                    <Switch
                        checked={plan.status === "active"}
                        onCheckedChange={onToggle}
                        aria-label={t("Enabled")}
                    />
                </label>
            </CardFooter>
        </Card>
    );
}
