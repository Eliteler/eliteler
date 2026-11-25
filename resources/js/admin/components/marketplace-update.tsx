import { ArrowUpRight } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";

const MARKETPLACE_DOWNLOADS = "https://gobizsaas.com/downloads";

export function MarketplaceUpdateBadge({ version }: { version: string | null }) {
    const { t } = useTranslation();
    if (!version) return null;

    return (
        <Badge className="absolute end-2 top-2 z-10 shadow-sm" title={version}>
            {t("Update available")}
        </Badge>
    );
}

export function MarketplaceUpdateButton() {
    const { t } = useTranslation();

    return (
        <Button size="sm" asChild>
            <a
                href={MARKETPLACE_DOWNLOADS}
                target="_blank"
                rel="noopener noreferrer"
            >
                {t("Update")}
                <ArrowUpRight />
            </a>
        </Button>
    );
}
