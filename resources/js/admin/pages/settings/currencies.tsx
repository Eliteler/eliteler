import { Head, router, useForm } from "@inertiajs/react";
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Coins,
    Loader2,
    MoreHorizontal,
    Pencil,
    Plus,
    Search,
    Trash2,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@admin/components/ui/card";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@admin/components/ui/collapsible";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@admin/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@admin/components/ui/table";
import { Tabs, TabsList, TabsTrigger } from "@admin/components/ui/tabs";

type Currency = {
    id: number;
    name: string;
    iso_code: string;
    symbol: string;
    symbol_first: boolean;
    priority: string;
    subunit: string;
    subunit_to_unit: string;
    decimal_mark: string;
    thousand_separator: string;
    html_entity: string;
    iso_numeric: string;
};

type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type Props = {
    currencies: PaginatedResponse<Currency>;
    defaultCurrency: string;
    open: string | null;
    search?: string;
};

const sample = (symbol: string, before: boolean) =>
    before ? `${symbol}1,234.56` : `1,234.56 ${symbol}`;

export default function Currencies({
    currencies,
    defaultCurrency,
    open,
    search = "",
}: Props) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(search);
    const isFirstRender = useRef(true);

    const [editing, setEditing] = useState<Currency | "new" | null>(
        open === "new"
            ? "new"
            : (currencies.data.find(
                  (currency) => String(currency.id) === open,
              ) ?? null),
    );
    const [deleting, setDeleting] = useState<Currency | null>(null);
    const [processing, setProcessing] = useState(false);

    // Real-time debounced search
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timer = setTimeout(() => {
            router.get(
                route("admin.currencies"),
                {
                    search: searchTerm || undefined,
                    per_page: currencies.per_page,
                    page: 1,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [searchTerm]);

    // Handle per page limit selection
    const handlePerPageChange = (newPerPage: string) => {
        router.get(
            route("admin.currencies"),
            {
                per_page: newPerPage,
                search: searchTerm || undefined,
                page: 1,
            },
            { preserveState: true, replace: true },
        );
    };

    // Navigate to a specific page
    const goToPage = (pageNumber: number) => {
        router.get(
            route("admin.currencies"),
            {
                page: pageNumber,
                per_page: currencies.per_page,
                search: searchTerm || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const remove = () => {
        if (!deleting) return;
        router.post(
            route("admin.delete.currency"),
            { id: deleting.id },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setDeleting(null);
                },
            },
        );
    };

    return (
        <>
            <Head title={t("Currencies")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Currencies")}
                    description={t(
                        "Currencies you can use for plans and payments. The default is set in General settings.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Currencies") },
                    ]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add currency")}
                        </Button>
                    }
                />

                <Card className="gap-0 py-0">
                    {/* Top Toolbar: Left (Per-page) & Right (Search) - Mobile inline */}
                    <CardHeader className="flex flex-row items-center justify-between gap-2 border-b p-3 sm:p-4">
                        {/* Left: Per page selector */}
                        <div className="flex items-center gap-1.5 shrink-0">
                            <span className="hidden text-xs text-muted-foreground sm:inline sm:text-sm">
                                {t("Show")}
                            </span>
                            <Select
                                value={String(currencies.per_page)}
                                onValueChange={handlePerPageChange}
                            >
                                <SelectTrigger className="h-8 w-16 text-xs sm:h-9 sm:w-20 sm:text-sm">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="10">10</SelectItem>
                                    <SelectItem value="25">25</SelectItem>
                                    <SelectItem value="50">50</SelectItem>
                                    <SelectItem value="100">100</SelectItem>
                                </SelectContent>
                            </Select>
                            <span className="text-xs text-muted-foreground sm:text-sm">
                                {t("entries")}
                            </span>
                        </div>

                        {/* Right: Real-time search box */}
                        <div className="relative w-full max-w-[160px] sm:max-w-xs">
                            <Search className="absolute left-2.5 top-2 size-3.5 text-muted-foreground sm:top-2.5 sm:size-4" />
                            <Input
                                type="search"
                                placeholder={t("Search...")}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-8 pl-8 text-xs sm:h-9 sm:pl-9 sm:text-sm"
                            />
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        {currencies.data.length === 0 ? (
                            <EmptyState
                                icon={Coins}
                                title={t("No currencies found")}
                                className="border-0"
                            />
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="ps-4">
                                            {t("Currency")}
                                        </TableHead>
                                        <TableHead>{t("Code")}</TableHead>
                                        <TableHead>{t("Symbol")}</TableHead>
                                        <TableHead>{t("Looks like")}</TableHead>
                                        <TableHead className="pe-4 text-end">
                                            <span className="sr-only">
                                                {t("Actions")}
                                            </span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {currencies.data.map((currency) => {
                                        const isDefault =
                                            currency.iso_code ===
                                            defaultCurrency;
                                        return (
                                            <TableRow key={currency.id}>
                                                <TableCell className="ps-4">
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setEditing(currency)
                                                        }
                                                        className="flex items-center gap-2 text-start font-medium hover:underline"
                                                    >
                                                        {t(currency.name)}
                                                        {isDefault && (
                                                            <Badge>
                                                                {t("Default")}
                                                            </Badge>
                                                        )}
                                                    </button>
                                                </TableCell>
                                                <TableCell className="font-mono text-xs">
                                                    {currency.iso_code}
                                                </TableCell>
                                                <TableCell>
                                                    {currency.symbol}
                                                </TableCell>
                                                <TableCell className="text-muted-foreground tabular-nums">
                                                    {sample(
                                                        currency.symbol,
                                                        currency.symbol_first,
                                                    )}
                                                </TableCell>
                                                <TableCell className="pe-4 text-end">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger
                                                            asChild
                                                        >
                                                            <Button
                                                                variant="ghost"
                                                                size="icon-sm"
                                                                aria-label={t(
                                                                    "Actions",
                                                                )}
                                                            >
                                                                <MoreHorizontal />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem
                                                                onSelect={() =>
                                                                    setEditing(
                                                                        currency,
                                                                    )
                                                                }
                                                            >
                                                                <Pencil />
                                                                {t("Edit")}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                variant="destructive"
                                                                disabled={
                                                                    isDefault
                                                                }
                                                                onSelect={() =>
                                                                    setDeleting(
                                                                        currency,
                                                                    )
                                                                }
                                                            >
                                                                <Trash2 />
                                                                {isDefault
                                                                    ? t(
                                                                          "Default can't be deleted",
                                                                      )
                                                                    : t(
                                                                          "Delete",
                                                                      )}
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>

                    {/* Server Pagination Footer */}
                    {currencies.total > 0 && (
                        <CardFooter className="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                            <span className="text-xs text-muted-foreground">
                                {t("Showing :from to :to of :total currencies")
                                    .replace(
                                        ":from",
                                        String(currencies.from ?? 0),
                                    )
                                    .replace(":to", String(currencies.to ?? 0))
                                    .replace(
                                        ":total",
                                        String(currencies.total),
                                    )}
                            </span>

                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(currencies.current_page - 1)
                                    }
                                    disabled={currencies.current_page <= 1}
                                >
                                    <ChevronLeft className="size-4" />
                                    <span className="sr-only sm:not-sr-only sm:ms-1">
                                        {t("Previous")}
                                    </span>
                                </Button>

                                <span className="text-xs font-medium px-2">
                                    {t("Page :current of :total")
                                        .replace(
                                            ":current",
                                            String(currencies.current_page),
                                        )
                                        .replace(
                                            ":total",
                                            String(currencies.last_page),
                                        )}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(currencies.current_page + 1)
                                    }
                                    disabled={
                                        currencies.current_page >=
                                        currencies.last_page
                                    }
                                >
                                    <span className="sr-only sm:not-sr-only sm:me-1">
                                        {t("Next")}
                                    </span>
                                    <ChevronRight className="size-4" />
                                </Button>
                            </div>
                        </CardFooter>
                    )}
                </Card>
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(isOpen) => !isOpen && setEditing(null)}
            >
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-lg">
                    {editing && (
                        <CurrencyForm
                            key={editing === "new" ? "new" : editing.id}
                            currency={editing === "new" ? null : editing}
                            isDefault={
                                editing !== "new" &&
                                editing.iso_code === defaultCurrency
                            }
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(isOpen) => !isOpen && setDeleting(null)}
                title={t("Delete :code?").replace(
                    ":code",
                    deleting?.iso_code ?? "",
                )}
                description={t(
                    "It's removed from this list. Past payments in this currency are not affected, and you can add it again later.",
                )}
                confirmLabel={t("Delete")}
                destructive
                processing={processing}
                onConfirm={remove}
            />
        </>
    );
}

function CurrencyForm({
    currency,
    isDefault,
    onClose,
}: {
    currency: Currency | null;
    isDefault: boolean;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm({
        id: currency?.id ?? "",
        name: currency?.name ?? "",
        iso_code: currency?.iso_code ?? "",
        symbol: currency?.symbol ?? "",
        symbol_first: currency?.symbol_first ?? true,
        priority: currency?.priority ?? "100",
        subunit: currency?.subunit ?? "",
        subunit_to_unit: currency?.subunit_to_unit ?? "100",
        decimal_mark: currency?.decimal_mark ?? ".",
        thousand_separator: currency?.thousand_separator ?? ",",
        html_entity: currency?.html_entity ?? "",
        iso_numeric: currency?.iso_numeric ?? "",
    });
    const { data, setData, errors } = form;
    const hasAdvancedError = [
        "priority",
        "subunit",
        "subunit_to_unit",
        "decimal_mark",
        "thousand_separator",
        "html_entity",
        "iso_numeric",
    ].some((key) => key in errors);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            symbol_first: current.symbol_first ? 1 : 0,
        }));
        form.post(
            currency
                ? route("admin.update.currency")
                : route("admin.save.currency"),
            { preserveScroll: true, onSuccess: onClose },
        );
    };

    const text = (
        key: keyof typeof data & string,
        label: string,
        props: React.ComponentProps<typeof Input> = {},
    ) => (
        <div className="flex flex-col gap-2">
            <Label htmlFor={key}>{label}</Label>
            <Input
                id={key}
                value={String(data[key] ?? "")}
                onChange={(event) => setData(key, event.target.value as never)}
                aria-invalid={!!errors[key as keyof typeof errors]}
                {...props}
            />
            <FieldError message={errors[key as keyof typeof errors]} />
        </div>
    );

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {currency
                        ? t("Edit :code").replace(":code", currency.iso_code)
                        : t("Add currency")}
                </DialogTitle>
                <DialogDescription>
                    {isDefault
                        ? t(
                              "This is your default currency. Prices across the site use it.",
                          )
                        : t("Name, code and symbol are all customers need.")}
                </DialogDescription>
            </DialogHeader>

            <div className="grid gap-4 sm:grid-cols-[1fr_7rem]">
                {text("name", t("Name"), {
                    placeholder: t("e.g., US Dollar"),
                    required: true,
                })}
                {text("iso_code", t("Code"), {
                    placeholder: "USD",
                    maxLength: 3,
                    className: "font-mono uppercase",
                    required: true,
                })}
            </div>

            <div className="grid gap-4 sm:grid-cols-[7rem_1fr]">
                {text("symbol", t("Symbol"), {
                    placeholder: "$",
                    required: true,
                })}
                <div className="flex flex-col gap-2">
                    <Label>{t("Symbol position")}</Label>
                    <Tabs
                        value={data.symbol_first ? "before" : "after"}
                        onValueChange={(value) =>
                            setData("symbol_first", value === "before")
                        }
                    >
                        <TabsList className="w-full">
                            <TabsTrigger
                                value="before"
                                className="flex-1 tabular-nums"
                            >
                                {sample(data.symbol || "$", true)}
                            </TabsTrigger>
                            <TabsTrigger
                                value="after"
                                className="flex-1 tabular-nums"
                            >
                                {sample(data.symbol || "$", false)}
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>
            </div>

            <Collapsible defaultOpen={hasAdvancedError}>
                <CollapsibleTrigger asChild>
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        className="[&[data-state=open]>svg]:rotate-180"
                    >
                        {t("More details")}
                        <ChevronDown className="transition-transform" />
                    </Button>
                </CollapsibleTrigger>
                <CollapsibleContent className="grid gap-4 pt-3 sm:grid-cols-2">
                    {text("subunit", t("Subunit name"), {
                        placeholder: t("e.g., Cent"),
                    })}
                    {text("subunit_to_unit", t("Subunits per unit"), {
                        type: "number",
                        min: 0,
                    })}
                    {text("decimal_mark", t("Decimal mark"), { maxLength: 2 })}
                    {text("thousand_separator", t("Thousands separator"), {
                        maxLength: 2,
                    })}
                    {text("html_entity", t("HTML entity"), {
                        placeholder: "&#36;",
                    })}
                    {text("iso_numeric", t("Numeric code"), {
                        type: "number",
                        min: 0,
                        placeholder: "840",
                    })}
                    {text("priority", t("Sort priority"), {
                        type: "number",
                        min: 0,
                    })}
                </CollapsibleContent>
            </Collapsible>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {currency ? t("Save") : t("Add currency")}
                </Button>
            </div>
        </form>
    );
}
