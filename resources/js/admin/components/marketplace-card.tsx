import {
    ArrowUpRight,
    CheckCircle2,
    ExternalLink,
    LayoutTemplate,
    Puzzle,
} from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import {
    MarketplaceUpdateBadge,
    MarketplaceUpdateButton,
} from "@admin/components/marketplace-update";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardFooter } from "@admin/components/ui/card";
import { cn } from "@admin/lib/utils";

export type MarketplaceItem = {
    id: string;
    name: string;
    description: string | null;
    logo: string | null;
    url: string;
    installed: boolean;
    openUrl: string | null;
    updateVersion: string | null;
};

export function MarketplaceCard({
    item,
    template = false,
}: {
    item: MarketplaceItem;
    template?: boolean;
}) {
    const { t } = useTranslation();
    const [logoFailed, setLogoFailed] = useState(false);
    const { updateVersion } = item;
    const artwork =
        item.logo && !logoFailed ? (
            <img
                src={item.logo}
                alt=""
                loading="lazy"
                onError={() => setLogoFailed(true)}
                className={
                    template
                        ? "size-full object-cover object-top"
                        : "max-h-full max-w-full object-contain"
                }
            />
        ) : template ? (
            <LayoutTemplate className="size-10 text-muted-foreground" />
        ) : (
            <Puzzle className="size-5 text-muted-foreground" />
        );

    return (
        <Card
            className={cn(
                "relative gap-0 overflow-hidden py-0",
                !template && updateVersion && "pt-8",
            )}
        >
            {template && (
                <div className="flex aspect-[16/10] items-center justify-center overflow-hidden border-b bg-muted">
                    {artwork}
                </div>
            )}
            <MarketplaceUpdateBadge version={updateVersion} />
            <CardContent className="flex flex-1 items-start gap-3 p-4">
                {!template && (
                    <div className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white p-1.5">
                        {artwork}
                    </div>
                )}
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <div className="flex items-center gap-2">
                        <span
                            className="truncate font-medium"
                            title={item.name}
                        >
                            {item.name}
                        </span>
                        {item.installed && (
                            <Badge
                                variant="outline"
                                className="ms-auto shrink-0 border-success/30 text-success"
                            >
                                <CheckCircle2 />
                                {t("Installed")}
                            </Badge>
                        )}
                    </div>
                    {item.description && (
                        <p className="line-clamp-2 text-sm text-muted-foreground">
                            {item.description}
                        </p>
                    )}
                </div>
            </CardContent>
            <CardFooter className="flex items-center justify-end gap-2 border-t p-3">
                {updateVersion && <MarketplaceUpdateButton />}
                {item.installed ? (
                    item.openUrl && (
                        <Button variant="outline" size="sm" asChild>
                            <a href={item.openUrl}>
                                <ExternalLink />
                                {t("Open")}
                            </a>
                        </Button>
                    )
                ) : (
                    <Button size="sm" asChild>
                        <a
                            href={item.url}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {t("Download")}
                            <ArrowUpRight />
                        </a>
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}
