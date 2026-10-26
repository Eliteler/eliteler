import { useTranslation } from "react-i18next";

import { Avatar, AvatarFallback, AvatarImage } from "@admin/components/ui/avatar";
import { Badge } from "@admin/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { initials } from "@admin/components/nav-user";
import { fromNow } from "@admin/lib/date";

import { customerUrl, type CustomerRow, type DashboardLinks } from "../types";

function CustomerList({
    rows,
    links,
    empty,
    meta,
}: {
    rows: CustomerRow[];
    links: DashboardLinks;
    empty: string;
    meta: (row: CustomerRow) => React.ReactNode;
}) {
    if (!rows.length) {
        return <p className="py-10 text-center text-sm text-muted-foreground">{empty}</p>;
    }

    return (
        <ul className="flex flex-col">
            {rows.map((row) => {
                const href = customerUrl(links, row.id);
                const Name = href ? "a" : "span";
                return (
                    <li key={row.id} className="flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-muted/50">
                        <Avatar className="size-9">
                            {row.avatar && <AvatarImage src={row.avatar} alt={row.name} />}
                            <AvatarFallback className="text-xs">{initials(row.name)}</AvatarFallback>
                        </Avatar>
                        <div className="flex min-w-0 flex-1 flex-col">
                            <Name {...(href ? { href } : {})} className="truncate text-sm font-medium hover:underline">
                                {row.name}
                            </Name>
                            <span className="truncate text-xs text-muted-foreground">{row.email}</span>
                        </div>
                        <div className="flex shrink-0 flex-col items-end gap-1">{meta(row)}</div>
                    </li>
                );
            })}
        </ul>
    );
}

export function CustomersCard({
    recent,
    expiring,
    links,
}: {
    recent: CustomerRow[];
    expiring: CustomerRow[];
    links: DashboardLinks;
}) {
    const { t } = useTranslation();

    return (
        <Card className="gap-4">
            <Tabs defaultValue="recent" className="gap-4">
                <CardHeader className="flex items-center justify-between gap-2">
                    <CardTitle>{t("Customers")}</CardTitle>
                    <TabsList>
                        <TabsTrigger value="recent">{t("Newest")}</TabsTrigger>
                        <TabsTrigger value="expiring">
                            {t("Expiring")}
                            {expiring.length > 0 && (
                                <Badge className="h-4 min-w-4 rounded-full px-1 text-[10px] tabular-nums">{expiring.length}</Badge>
                            )}
                        </TabsTrigger>
                    </TabsList>
                </CardHeader>
                <CardContent className="px-4">
                    <TabsContent value="recent">
                        <CustomerList
                            rows={recent}
                            links={links}
                            empty={t("No customers yet")}
                            meta={(row) => (
                                <>
                                    {row.plan ? (
                                        <Badge variant="secondary">{row.plan}</Badge>
                                    ) : (
                                        <Badge variant="outline">{t("No plan")}</Badge>
                                    )}
                                    <span className="text-xs text-muted-foreground">{fromNow(row.date)}</span>
                                </>
                            )}
                        />
                    </TabsContent>
                    <TabsContent value="expiring">
                        <CustomerList
                            rows={expiring}
                            links={links}
                            empty={t("No paid plans expire in the next 7 days")}
                            meta={(row) => (
                                <>
                                    <Badge variant="secondary">{row.plan}</Badge>
                                    <span className="text-xs font-medium text-warning">{fromNow(row.expires)}</span>
                                </>
                            )}
                        />
                    </TabsContent>
                </CardContent>
            </Tabs>
        </Card>
    );
}
