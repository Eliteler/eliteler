import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@admin/components/ui/table";
import { fromNow } from "@admin/lib/date";
import { formatCurrency } from "@admin/lib/format";

import { customerUrl, type DashboardLinks, type RecentPayment } from "../types";

export function RecentPayments({ payments, links }: { payments: RecentPayment[]; links: DashboardLinks }) {
    const { t } = useTranslation();
    const { currency } = usePage().props;

    return (
        <Card className="gap-4 lg:col-span-2">
            <CardHeader>
                <CardTitle>{t("Latest payments")}</CardTitle>
                <CardDescription>{t("Most recent paid plan purchases")}</CardDescription>
                {links.transactions && (
                    <CardAction>
                        <Button variant="outline" size="sm" asChild>
                            <a href={links.transactions}>{t("View all")}</a>
                        </Button>
                    </CardAction>
                )}
            </CardHeader>
            <CardContent>
                {payments.length === 0 ? (
                    <p className="py-10 text-center text-sm text-muted-foreground">{t("No payments yet")}</p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t("Customer")}</TableHead>
                                <TableHead className="hidden sm:table-cell">{t("Plan")}</TableHead>
                                <TableHead className="hidden md:table-cell">{t("Gateway")}</TableHead>
                                <TableHead className="text-end">{t("Amount")}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payments.map((payment) => {
                                const href = customerUrl(links, payment.customerId);
                                return (
                                    <TableRow key={payment.id}>
                                        <TableCell className="max-w-[220px]">
                                            <div className="flex flex-col">
                                                {href ? (
                                                    <a href={href} className="truncate font-medium hover:underline">
                                                        {payment.customer}
                                                    </a>
                                                ) : (
                                                    <span className="truncate font-medium">{payment.customer}</span>
                                                )}
                                                <span className="truncate text-xs text-muted-foreground">{payment.email}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="hidden sm:table-cell">{payment.plan}</TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <Badge variant="outline">{payment.gateway}</Badge>
                                        </TableCell>
                                        <TableCell className="text-end">
                                            <div className="flex flex-col items-end">
                                                <span className="font-medium tabular-nums">
                                                    {formatCurrency(payment.amount, currency)}
                                                </span>
                                                <span className="text-xs text-muted-foreground">{fromNow(payment.date)}</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>
                )}
            </CardContent>
        </Card>
    );
}
