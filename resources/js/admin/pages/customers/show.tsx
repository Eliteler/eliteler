import { Head, Link, router, usePage } from "@inertiajs/react";
import {
    Copy,
    ExternalLink,
    FileText,
    Mail,
    MoreHorizontal,
    Pencil,
    Phone,
    Sparkles,
    UserPlus,
} from "lucide-react";
import { useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { initials } from "@admin/components/nav-user";
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from "@admin/components/ui/avatar";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@admin/components/ui/card";
import { Progress } from "@admin/components/ui/progress";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@admin/components/ui/table";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@admin/components/ui/tabs";
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from "@admin/components/ui/tooltip";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatCurrency, formatNumber } from "@admin/lib/format";
import { cn } from "@admin/lib/utils";

import { CopyCardDialog } from "./components/copy-card-dialog";
import { CustomerActions } from "./components/customer-actions";
import { StateBadge, UnverifiedBadge } from "./components/customer-badges";
import type { CustomerPlan, CustomerState, PlanOption } from "./types";

type CardRow = {
    id: string;
    type: "vcard" | "store";
    title: string;
    url: string;
    theme: string | null;
    active: boolean;
    state: string;
    created: string | null;
};

type Payment = {
    id: string;
    reference: string | null;
    description: string;
    gateway: string;
    amount: number;
    status: string;
    date: string | null;
    invoice: string | null;
};

type NfcOrder = {
    id: string;
    item: string | null;
    quantity: number;
    amount: number | null; // null when revenue is hidden
    status: string;
    date: string | null;
};
type NfcPayment = {
    id: string;
    orderId: string;
    reference: string | null;
    gateway: string;
    amount: number;
    status: string;
    date: string | null;
};
type AiPayment = {
    id: string;
    plan: string | null;
    gateway: string;
    amount: number;
    status: string;
    date: string | null;
};

type Customer = {
    id: string;
    name: string;
    email: string;
    avatar: string | null;
    phone: string | null;
    whatsapp: string | null;
    language: string | null;
    signInWith: string | null;
    state: CustomerState;
    verified: boolean;
    joined: string | null;
    updated: string | null;
    plan: CustomerPlan | null;
    billing: Partial<
        Record<
            "name" | "email" | "phone" | "address" | "taxId" | "type",
            string
        >
    >;
    aiCredits: { credits: number; trial: number } | null;
    referredBy: { id: string; name: string; email: string } | null;
};

type ActivityRow = CardRow | Payment | NfcOrder | NfcPayment | AiPayment;

type PaginatedActivity = {
    data: ActivityRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type ActivityTab =
    | "cards"
    | "stores"
    | "payments"
    | "nfc_orders"
    | "nfc_payments"
    | "ai_payments";

type Props = {
    customer: Customer;
    activeTab: ActivityTab;
    activity: PaginatedActivity;
    tabCounts: Partial<Record<ActivityTab, number>>;
    hideRevenue: boolean;
    nfcEnabled: boolean;
    aiBuilderEnabled: boolean;
    plans: PlanOption[];
    openDialog: "plan" | null;
};

const SUCCESS = ["success", "paid", "delivered", "shipped"];
const FAILED = ["failed", "cancelled"];

function StatusPill({ status }: { status: string }) {
    const { t } = useTranslation();
    const key = status.toLowerCase();
    return (
        <Badge
            variant="outline"
            className={cn(
                "capitalize",
                SUCCESS.includes(key) && "border-success/30 text-success",
                FAILED.includes(key) &&
                    "border-destructive/30 text-destructive",
            )}
        >
            {t(status.replace(/_/g, " "))}
        </Badge>
    );
}

function InfoRow({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex items-start justify-between gap-4 text-sm">
            <span className="shrink-0 text-muted-foreground">{label}</span>
            <span className="min-w-0 text-end break-words">{children}</span>
        </div>
    );
}

function EmptyRow({ colSpan, text }: { colSpan: number; text: string }) {
    return (
        <TableRow className="hover:bg-transparent">
            <TableCell
                colSpan={colSpan}
                className="py-10 text-center text-sm text-muted-foreground"
            >
                {text}
            </TableCell>
        </TableRow>
    );
}

export default function CustomerShow(props: Props) {
    const { t } = useTranslation();
    const pageState = usePage();
    const { currency, locale } = pageState.props;
    const { customer, plans, activeTab, activity, tabCounts } = props;
    const dir = locale.dir;

    const [planOpen, setPlanOpen] = useState(
        props.openDialog === "plan" && customer.state !== "deleted",
    );
    const [copying, setCopying] = useState<CardRow | null>(null);

    const money = (value: number) => formatCurrency(value, currency);
    const deleted = customer.state === "deleted";

    const getQueryParams = () =>
        Object.fromEntries(
            new URLSearchParams(pageState.url.split("?")[1] ?? ""),
        );

    const changeTab = (tab: ActivityTab) => {
        router.get(
            route("admin.view.customer", customer.id),
            { ...getQueryParams(), tab, page: 1 },
            { preserveScroll: true, preserveState: true },
        );
    };

    const changePage = (page: number) => {
        router.get(
            route("admin.view.customer", customer.id),
            { ...getQueryParams(), tab: activeTab, page },
            { preserveScroll: true, preserveState: true },
        );
    };

    const cards = activeTab === "cards" ? (activity.data as CardRow[]) : [];
    const stores = activeTab === "stores" ? (activity.data as CardRow[]) : [];
    const payments =
        activeTab === "payments" ? (activity.data as Payment[]) : [];
    const nfcOrders =
        activeTab === "nfc_orders" ? (activity.data as NfcOrder[]) : [];
    const nfcPayments =
        activeTab === "nfc_payments" ? (activity.data as NfcPayment[]) : [];
    const aiPayments =
        activeTab === "ai_payments" ? (activity.data as AiPayment[]) : [];

    const plan = customer.plan;
    const planProgress =
        plan && plan.validity > 0 && !plan.expired && plan.validity < 9999
            ? Math.max(0, Math.min(100, (plan.daysLeft / plan.validity) * 100))
            : null;

    const cardTable = (rows: CardRow[], empty: string) => (
        <div dir={dir} className="border rounded-lg">
            <Table>
                <TableHeader>
                    <TableRow className="hover:bg-transparent">
                        <TableHead>{t("Title")}</TableHead>
                        <TableHead className="hidden md:table-cell">
                            {t("Theme")}
                        </TableHead>
                        <TableHead>{t("Status")}</TableHead>
                        <TableHead className="hidden sm:table-cell">
                            {t("Created")}
                        </TableHead>
                        <TableHead className="w-24" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rows.length === 0 && <EmptyRow colSpan={5} text={empty} />}
                    {rows.map((card) => (
                        <TableRow key={card.id}>
                            <TableCell className="max-w-[260px]">
                                <div className="flex flex-col">
                                    <span className="truncate font-medium">
                                        {card.title}
                                    </span>
                                    <span
                                        className="truncate text-xs text-muted-foreground"
                                        dir="ltr"
                                    >
                                        {card.url.replace(/^https?:\/\//, "")}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell className="hidden text-muted-foreground md:table-cell">
                                {card.theme ?? "—"}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    variant="outline"
                                    className={
                                        card.active
                                            ? "border-success/30 text-success"
                                            : "text-muted-foreground"
                                    }
                                >
                                    {card.active
                                        ? t("Active")
                                        : t(
                                              card.state === "activated"
                                                  ? "Disabled"
                                                  : "Inactive",
                                          )}
                                </Badge>
                            </TableCell>
                            <TableCell className="hidden text-muted-foreground sm:table-cell">
                                {formatDate(card.created)}
                            </TableCell>
                            <TableCell className="text-end">
                                <div className="flex justify-end gap-1">
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                asChild
                                            >
                                                <a
                                                    href={card.url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    aria-label={t("Open")}
                                                >
                                                    <ExternalLink />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            {t("Open")}
                                        </TooltipContent>
                                    </Tooltip>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                onClick={() => setCopying(card)}
                                                aria-label={t(
                                                    "Copy to another customer",
                                                )}
                                            >
                                                <Copy />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            {t("Copy to another customer")}
                                        </TooltipContent>
                                    </Tooltip>
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );

    const allTabs: {
        value: ActivityTab;
        label: string;
        count: number | undefined;
        content: ReactNode;
    }[] = [
        {
            value: "cards",
            label: t("Business cards"),
            count: tabCounts.cards,
            content: cardTable(cards, t("No business cards yet")),
        },
        {
            value: "stores",
            label: t("Stores"),
            count: tabCounts.stores,
            content: cardTable(stores, t("No stores yet")),
        },
        {
            value: "payments",
            label: t("Plan payments"),
            count: tabCounts.payments,
            content: (
                <div dir={dir} className="border rounded-lg">
                    <Table>
                        <TableHeader>
                            <TableRow className="hover:bg-transparent">
                                <TableHead>{t("Description")}</TableHead>
                                <TableHead className="hidden md:table-cell">
                                    {t("Gateway")}
                                </TableHead>
                                <TableHead>{t("Status")}</TableHead>
                                <TableHead className="text-end">
                                    {t("Amount")}
                                </TableHead>
                                <TableHead className="w-10" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payments.length === 0 && (
                                <EmptyRow
                                    colSpan={5}
                                    text={t("No payments yet")}
                                />
                            )}
                            {payments.map((payment) => (
                                <TableRow key={payment.id}>
                                    <TableCell>
                                        <div className="flex flex-col">
                                            <span className="font-medium">
                                                {payment.description}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {formatDate(
                                                    payment.date,
                                                    "dd MMM yyyy, HH:mm",
                                                )}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {payment.gateway}
                                    </TableCell>
                                    <TableCell>
                                        <StatusPill status={payment.status} />
                                    </TableCell>
                                    <TableCell className="text-end font-medium tabular-nums">
                                        {money(payment.amount)}
                                    </TableCell>
                                    <TableCell className="text-end">
                                        {payment.invoice && (
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                asChild
                                            >
                                                <a
                                                    href={payment.invoice}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    aria-label={t("Invoice")}
                                                >
                                                    <FileText />
                                                </a>
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            ),
        },
        ...(props.nfcEnabled
            ? [
                  {
                      value: "nfc_orders" as const,
                      label: t("NFC orders"),
                      count: tabCounts.nfc_orders,
                      content: (
                          <div dir={dir} className="border rounded-lg">
                              <Table>
                                  <TableHeader>
                                      <TableRow className="hover:bg-transparent">
                                          <TableHead>{t("Item")}</TableHead>
                                          <TableHead className="hidden md:table-cell">
                                              {t("Order ID")}
                                          </TableHead>
                                          <TableHead>{t("Delivery")}</TableHead>
                                          {!props.hideRevenue && (
                                              <TableHead className="text-end">
                                                  {t("Amount")}
                                              </TableHead>
                                          )}
                                      </TableRow>
                                  </TableHeader>
                                  <TableBody>
                                      {nfcOrders.length === 0 && (
                                          <EmptyRow
                                              colSpan={props.hideRevenue ? 3 : 4}
                                              text={t("No NFC card orders")}
                                          />
                                      )}
                                      {nfcOrders.map((order) => (
                                          <TableRow key={order.id}>
                                              <TableCell>
                                                  <div className="flex flex-col">
                                                      <span className="font-medium">
                                                          {order.item ??
                                                              t(
                                                                  "NFC card",
                                                              )}{" "}
                                                          × {order.quantity}
                                                      </span>
                                                      <span className="text-xs text-muted-foreground">
                                                          {formatDate(
                                                              order.date,
                                                          )}
                                                      </span>
                                                  </div>
                                              </TableCell>
                                              <TableCell className="hidden font-mono text-xs md:table-cell">
                                                  {order.id}
                                              </TableCell>
                                              <TableCell>
                                                  <StatusPill
                                                      status={order.status}
                                                  />
                                              </TableCell>
                                              {!props.hideRevenue && order.amount !== null && (
                                                  <TableCell className="text-end font-medium tabular-nums">
                                                      {money(order.amount)}
                                                  </TableCell>
                                              )}
                                          </TableRow>
                                      ))}
                                  </TableBody>
                              </Table>
                          </div>
                      ),
                  },
                  {
                      value: "nfc_payments" as const,
                      label: t("NFC payments"),
                      count: tabCounts.nfc_payments,
                      content: (
                          <div dir={dir} className="border rounded-lg">
                              <Table>
                                  <TableHeader>
                                      <TableRow className="hover:bg-transparent">
                                          <TableHead>{t("Order ID")}</TableHead>
                                          <TableHead className="hidden md:table-cell">
                                              {t("Gateway")}
                                          </TableHead>
                                          <TableHead>{t("Status")}</TableHead>
                                          <TableHead className="text-end">
                                              {t("Amount")}
                                          </TableHead>
                                      </TableRow>
                                  </TableHeader>
                                  <TableBody>
                                      {nfcPayments.length === 0 && (
                                          <EmptyRow
                                              colSpan={4}
                                              text={t("No NFC payments")}
                                          />
                                      )}
                                      {nfcPayments.map((payment) => (
                                          <TableRow key={payment.id}>
                                              <TableCell>
                                                  <div className="flex flex-col">
                                                      <span className="font-mono text-xs">
                                                          {payment.orderId}
                                                      </span>
                                                      <span className="text-xs text-muted-foreground">
                                                          {formatDate(
                                                              payment.date,
                                                          )}
                                                      </span>
                                                  </div>
                                              </TableCell>
                                              <TableCell className="hidden md:table-cell">
                                                  {payment.gateway}
                                              </TableCell>
                                              <TableCell>
                                                  <StatusPill
                                                      status={payment.status}
                                                  />
                                              </TableCell>
                                              <TableCell className="text-end font-medium tabular-nums">
                                                  {money(payment.amount)}
                                              </TableCell>
                                          </TableRow>
                                      ))}
                                  </TableBody>
                              </Table>
                          </div>
                      ),
                  },
              ]
            : []),
        ...(props.aiBuilderEnabled
            ? [
                  {
                      value: "ai_payments" as const,
                      label: t("AI credits"),
                      count: tabCounts.ai_payments,
                      content: (
                          <div dir={dir} className="border rounded-lg">
                              <Table>
                                  <TableHeader>
                                      <TableRow className="hover:bg-transparent">
                                          <TableHead>{t("Plan")}</TableHead>
                                          <TableHead className="hidden md:table-cell">
                                              {t("Gateway")}
                                          </TableHead>
                                          <TableHead>{t("Status")}</TableHead>
                                          <TableHead className="text-end">
                                              {t("Amount")}
                                          </TableHead>
                                      </TableRow>
                                  </TableHeader>
                                  <TableBody>
                                      {aiPayments.length === 0 && (
                                          <EmptyRow
                                              colSpan={4}
                                              text={t("No AI credit purchases")}
                                          />
                                      )}
                                      {aiPayments.map((payment) => (
                                          <TableRow key={payment.id}>
                                              <TableCell>
                                                  <div className="flex flex-col">
                                                      <span className="font-medium">
                                                          {payment.plan ?? "—"}
                                                      </span>
                                                      <span className="text-xs text-muted-foreground">
                                                          {formatDate(
                                                              payment.date,
                                                          )}
                                                      </span>
                                                  </div>
                                              </TableCell>
                                              <TableCell className="hidden md:table-cell">
                                                  {payment.gateway}
                                              </TableCell>
                                              <TableCell>
                                                  <StatusPill
                                                      status={payment.status}
                                                  />
                                              </TableCell>
                                              <TableCell className="text-end font-medium tabular-nums">
                                                  {money(payment.amount)}
                                              </TableCell>
                                          </TableRow>
                                      ))}
                                  </TableBody>
                              </Table>
                          </div>
                      ),
                  },
              ]
            : []),
    ];

    // Payment history is money: the server doesn't send it when revenue is hidden
    const tabs = allTabs.filter((tab) => tab.value in tabCounts);

    return (
        <>
            <Head title={customer.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <nav className="flex items-center gap-1 text-sm text-muted-foreground">
                    <Link
                        href={route("admin.dashboard")}
                        className="hover:text-foreground"
                    >
                        {t("Dashboard")}
                    </Link>
                    <span>/</span>
                    <Link
                        href={route("admin.customers", { status: "all" })}
                        className="hover:text-foreground"
                    >
                        {t("Customers")}
                    </Link>
                    <span>/</span>
                    <span className="truncate text-foreground">
                        {customer.name}
                    </span>
                </nav>

                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex min-w-0 items-center gap-4">
                        <Avatar className="size-16">
                            {customer.avatar && (
                                <AvatarImage
                                    src={customer.avatar}
                                    alt={customer.name}
                                />
                            )}
                            <AvatarFallback className="text-lg">
                                {initials(customer.name)}
                            </AvatarFallback>
                        </Avatar>
                        <div className="flex min-w-0 flex-col gap-1">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="truncate text-2xl font-semibold tracking-tight">
                                    {customer.name}
                                </h1>
                                <StateBadge state={customer.state} />
                                {!customer.verified &&
                                    customer.state === "active" && (
                                        <UnverifiedBadge />
                                    )}
                            </div>
                            <a
                                href={`mailto:${customer.email}`}
                                className="truncate text-sm text-muted-foreground hover:underline"
                            >
                                {customer.email}
                            </a>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {!deleted && (
                            <>
                                <Button variant="outline" asChild>
                                    <Link
                                        href={route(
                                            "admin.edit.customer",
                                            customer.id,
                                        )}
                                    >
                                        <Pencil />
                                        {t("Edit")}
                                    </Link>
                                </Button>
                            </>
                        )}
                        <CustomerActions
                            customer={customer}
                            plans={plans}
                            showView={false}
                            planDialogOpen={planOpen}
                            onPlanDialogChange={setPlanOpen}
                            trigger={
                                <Button
                                    variant="outline"
                                    size="icon"
                                    aria-label={t("More actions")}
                                >
                                    <MoreHorizontal />
                                </Button>
                            }
                        />
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-3">
                    <div className="flex flex-col gap-6">
                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle>{t("Subscription")}</CardTitle>
                                {!deleted && (
                                    <CardAction>
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={() => setPlanOpen(true)}
                                        >
                                            {t("Change")}
                                        </Button>
                                    </CardAction>
                                )}
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                {plan ? (
                                    <>
                                        <div className="flex items-baseline justify-between gap-2">
                                            <span className="text-xl font-semibold">
                                                {plan.name}
                                            </span>
                                            {plan.price !== null && (
                                                <span className="text-sm text-muted-foreground">
                                                    {plan.price > 0
                                                        ? money(plan.price)
                                                        : t("Free")}
                                                </span>
                                            )}
                                        </div>
                                        {planProgress !== null && (
                                            <div className="flex flex-col gap-1.5">
                                                <Progress
                                                    value={planProgress}
                                                />
                                                <span className="text-xs text-muted-foreground">
                                                    {plan.daysLeft}{" "}
                                                    {t("days left")}
                                                </span>
                                            </div>
                                        )}
                                        <div className="flex flex-col gap-2">
                                            <InfoRow label={t("Status")}>
                                                {plan.expired ? (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-destructive/30 text-destructive"
                                                    >
                                                        {t("Expired")}
                                                    </Badge>
                                                ) : (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-success/30 text-success"
                                                    >
                                                        {t("Active")}
                                                    </Badge>
                                                )}
                                            </InfoRow>
                                            <InfoRow label={t("Started")}>
                                                {formatDate(plan.activatedAt)}
                                            </InfoRow>
                                            <InfoRow
                                                label={
                                                    plan.expired
                                                        ? t("Ended")
                                                        : t("Renews / ends")
                                                }
                                            >
                                                {plan.validity >= 9999
                                                    ? t("Lifetime")
                                                    : formatDate(
                                                          plan.expiresAt,
                                                      )}
                                            </InfoRow>
                                        </div>
                                    </>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        {t("This customer has no plan.")}
                                    </p>
                                )}
                                {customer.aiCredits && (
                                    <div className="flex items-center justify-between rounded-lg border p-3 text-sm">
                                        <span className="flex items-center gap-2 text-muted-foreground">
                                            <Sparkles className="size-4" />
                                            {t("AI credits")}
                                        </span>
                                        <span className="font-medium tabular-nums">
                                            {customer.aiCredits.credits < 0
                                                ? t("Unlimited")
                                                : formatNumber(
                                                      customer.aiCredits
                                                          .credits,
                                                  )}
                                            {customer.aiCredits.trial > 0 && (
                                                <span className="ms-1 text-xs text-muted-foreground">
                                                    + {customer.aiCredits.trial}{" "}
                                                    {t("trial")}
                                                </span>
                                            )}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="gap-4">
                            <CardHeader>
                                <CardTitle>{t("Profile")}</CardTitle>
                                <CardDescription className="font-mono text-xs">
                                    {customer.id}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2.5">
                                <InfoRow label={t("Email")}>
                                    <a
                                        href={`mailto:${customer.email}`}
                                        className="flex items-center gap-1 hover:underline"
                                    >
                                        <Mail className="size-3.5" />
                                        {customer.email}
                                    </a>
                                </InfoRow>
                                {customer.phone && (
                                    <InfoRow label={t("Phone")}>
                                        <a
                                            href={`tel:${customer.phone.replace(/\s/g, "")}`}
                                            className="flex items-center gap-1 hover:underline"
                                            dir="ltr"
                                        >
                                            <Phone className="size-3.5" />
                                            {customer.phone}
                                        </a>
                                    </InfoRow>
                                )}
                                {customer.whatsapp && (
                                    <InfoRow label="WhatsApp">
                                        <span dir="ltr">
                                            {customer.whatsapp}
                                        </span>
                                    </InfoRow>
                                )}
                                <InfoRow label={t("Email verified")}>
                                    {customer.verified ? t("Yes") : t("No")}
                                </InfoRow>
                                {customer.signInWith && (
                                    <InfoRow label={t("Signs in with")}>
                                        {customer.signInWith}
                                    </InfoRow>
                                )}
                                {customer.language && (
                                    <InfoRow label={t("Language")}>
                                        {customer.language.toUpperCase()}
                                    </InfoRow>
                                )}
                                <InfoRow label={t("Joined")}>
                                    <span
                                        title={formatDate(
                                            customer.joined,
                                            "dd MMM yyyy, HH:mm",
                                        )}
                                    >
                                        {formatDate(customer.joined)}
                                    </span>
                                </InfoRow>
                                <InfoRow label={t("Last updated")}>
                                    {fromNow(customer.updated)}
                                </InfoRow>
                                {customer.referredBy && (
                                    <InfoRow label={t("Referred by")}>
                                        <Link
                                            href={route(
                                                "admin.view.customer",
                                                customer.referredBy.id,
                                            )}
                                            className="flex items-center gap-1 hover:underline"
                                        >
                                            <UserPlus className="size-3.5" />
                                            {customer.referredBy.name}
                                        </Link>
                                    </InfoRow>
                                )}
                            </CardContent>
                        </Card>

                        {Object.keys(customer.billing).length > 0 && (
                            <Card className="gap-4">
                                <CardHeader>
                                    <CardTitle>
                                        {t("Billing details")}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-2.5">
                                    {customer.billing.name && (
                                        <InfoRow label={t("Name")}>
                                            {customer.billing.name}
                                        </InfoRow>
                                    )}
                                    {customer.billing.type && (
                                        <InfoRow label={t("Type")}>
                                            <span className="capitalize">
                                                {customer.billing.type}
                                            </span>
                                        </InfoRow>
                                    )}
                                    {customer.billing.email && (
                                        <InfoRow label={t("Email")}>
                                            {customer.billing.email}
                                        </InfoRow>
                                    )}
                                    {customer.billing.phone && (
                                        <InfoRow label={t("Phone")}>
                                            <span dir="ltr">
                                                {customer.billing.phone}
                                            </span>
                                        </InfoRow>
                                    )}
                                    {customer.billing.address && (
                                        <InfoRow label={t("Address")}>
                                            {customer.billing.address}
                                        </InfoRow>
                                    )}
                                    {customer.billing.taxId && (
                                        <InfoRow label={t("Tax ID")}>
                                            {customer.billing.taxId}
                                        </InfoRow>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <Card className="gap-4 xl:col-span-2">
                        <Tabs
                            dir={dir}
                            value={activeTab}
                            onValueChange={(value) =>
                                changeTab(value as ActivityTab)
                            }
                            className="gap-4"
                        >
                            <CardHeader className="flex flex-col gap-3">
                                <CardTitle>{t("Activity")}</CardTitle>
                                <div className="w-full overflow-x-auto">
                                    <TabsList>
                                        {tabs.map((tab) => (
                                            <TabsTrigger
                                                key={tab.value}
                                                value={tab.value}
                                            >
                                                {tab.label}
                                                <span className="text-muted-foreground tabular-nums">
                                                    {tab.count}
                                                </span>
                                            </TabsTrigger>
                                        ))}
                                    </TabsList>
                                </div>
                            </CardHeader>

                            <CardContent>
                                {tabs.map((tab) => (
                                    <TabsContent
                                        key={tab.value}
                                        value={tab.value}
                                    >
                                        {tab.content}

                                        <div className="flex flex-wrap items-center justify-between gap-3 pt-4 text-sm">
                                            <span className="text-muted-foreground">
                                                {t("Showing")}{" "}
                                                {activity.from ?? 0}–
                                                {activity.to ?? 0} {t("of")}{" "}
                                                {activity.total}
                                            </span>

                                            <div className="flex items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    disabled={
                                                        activity.current_page <=
                                                        1
                                                    }
                                                    onClick={() =>
                                                        changePage(
                                                            activity.current_page -
                                                                1,
                                                        )
                                                    }
                                                >
                                                    {t("Previous")}
                                                </Button>

                                                <span className="tabular-nums">
                                                    {activity.current_page} /{" "}
                                                    {activity.last_page}
                                                </span>

                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    disabled={
                                                        activity.current_page >=
                                                        activity.last_page
                                                    }
                                                    onClick={() =>
                                                        changePage(
                                                            activity.current_page +
                                                                1,
                                                        )
                                                    }
                                                >
                                                    {t("Next")}
                                                </Button>
                                            </div>
                                        </div>
                                    </TabsContent>
                                ))}
                            </CardContent>
                        </Tabs>
                    </Card>
                </div>
            </div>

            <CopyCardDialog
                card={copying}
                ownerId={customer.id}
                onClose={() => setCopying(null)}
            />
        </>
    );
}
