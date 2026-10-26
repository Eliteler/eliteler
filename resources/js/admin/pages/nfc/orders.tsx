import { Head, Link, usePage } from "@inertiajs/react";
import { ImageIcon, PackageSearch } from "lucide-react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Pagination } from "@admin/components/pagination";
import { Button } from "@admin/components/ui/button";
import { Card } from "@admin/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { Tooltip, TooltipContent, TooltipTrigger } from "@admin/components/ui/tooltip";
import { useHideRevenue } from "@admin/hooks/use-hide-revenue";
import { formatDate, fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";
import type { Paginated } from "@admin/types";

import { ListToolbar } from "@admin/components/list-toolbar";
import { DeliveryBadge, PaymentBadge } from "./components/status-badges";
import type { Customer } from "./types";

type OrderRow = {
    id: string;
    date: string | null;
    item: string;
    image: string | null;
    quantity: number;
    amount: number | null; // null when revenue is hidden
    payment: string;
    method: string;
    delivery: string;
    hasLogo: boolean;
    customer: Customer | null;
};

type Props = { orders: Paginated<OrderRow>; tab: string; search: string; counts: Record<string, number> };

export default function NfcOrders({ orders, tab, search, counts }: Props) {
    const { t } = useTranslation();
    const { currency } = usePage().props;
    const hideRevenue = useHideRevenue();

    const tabs = [
        { value: "all", label: t("All") },
        { value: "new", label: t("New") },
        { value: "in_progress", label: t("In progress") },
        { value: "hold", label: t("On hold") },
        { value: "delivered", label: t("Delivered") },
        { value: "unpaid", label: t("Awaiting payment") },
        { value: "cancelled", label: t("Cancelled") },
    ];

    return (
        <>
            <Head title={t("NFC card orders")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("NFC card orders")}
                    description={t("Track physical NFC card orders from payment to delivery.")}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: t("NFC card orders") }]}
                />

                <ListToolbar
                    routeName="admin.orders"
                    tabs={tabs}
                    tab={tab}
                    counts={counts}
                    search={search}
                    highlight="new"
                    placeholder={t("Search order ID, customer or card...")}
                />

                {orders.data.length === 0 ? (
                    <EmptyState icon={PackageSearch} title={t("No orders found")} description={search ? t("Try a different search.") : undefined} />
                ) : (
                    <Card className="gap-0 py-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead className="ps-4">{t("Order")}</TableHead>
                                    <TableHead className="hidden md:table-cell">{t("Customer")}</TableHead>
                                    <TableHead className="hidden lg:table-cell">{t("Item")}</TableHead>
                                    {!hideRevenue && <TableHead className="text-end">{t("Amount")}</TableHead>}
                                    <TableHead className="hidden sm:table-cell">{t("Payment")}</TableHead>
                                    <TableHead>{t("Delivery")}</TableHead>
                                    <TableHead className="w-20 pe-4" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {orders.data.map((order) => (
                                    <TableRow key={order.id}>
                                        <TableCell className="ps-4">
                                            <Link href={route("admin.order.show", order.id)} className="flex flex-col hover:underline">
                                                <span className="font-mono text-xs font-medium">{order.id}</span>
                                                <span className="text-xs text-muted-foreground" title={formatDate(order.date, "dd MMM yyyy, HH:mm")}>
                                                    {fromNow(order.date)}
                                                </span>
                                            </Link>
                                        </TableCell>
                                        <TableCell className="hidden max-w-[220px] md:table-cell">
                                            {order.customer ? (
                                                <Link href={route("admin.view.customer", order.customer.id)} className="flex flex-col hover:underline">
                                                    <span className="truncate text-sm">{order.customer.name}</span>
                                                    <span className="truncate text-xs text-muted-foreground">{order.customer.email}</span>
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-muted-foreground">{t("Deleted customer")}</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="hidden lg:table-cell">
                                            <div className="flex items-center gap-2.5">
                                                {order.image && <img src={order.image} alt="" className="h-8 w-12 rounded border object-cover" />}
                                                <span className="text-sm">
                                                    {order.item}
                                                    {order.quantity > 1 && <span className="ms-1 text-muted-foreground">× {order.quantity}</span>}
                                                </span>
                                                {order.hasLogo && (
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <ImageIcon className="size-3.5 text-muted-foreground" />
                                                        </TooltipTrigger>
                                                        <TooltipContent>{t("Customer uploaded a logo")}</TooltipContent>
                                                    </Tooltip>
                                                )}
                                            </div>
                                        </TableCell>
                                        {!hideRevenue && order.amount !== null && (
                                            <TableCell className="text-end font-medium tabular-nums">{formatCurrency(order.amount, currency)}</TableCell>
                                        )}
                                        <TableCell className="hidden sm:table-cell">
                                            <PaymentBadge status={order.payment} />
                                        </TableCell>
                                        <TableCell>
                                            <DeliveryBadge status={order.delivery} />
                                        </TableCell>
                                        <TableCell className="pe-4 text-end">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={route("admin.order.show", order.id)}>{t("Open")}</Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                <Pagination data={orders} />
            </div>
        </>
    );
}
