import { Link } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { Alert, AlertDescription } from "@admin/components/ui/alert";

export function MailgunNotConfigured() {
    const { t } = useTranslation();
    return (
        <Alert variant="warning">
            <AlertDescription className="text-foreground">
                <span>
                    {t("Mailgun isn’t set up yet, so campaigns can’t be sent.")}{" "}
                    <Link href={route("admin.marketing.mailgun")} className="font-medium underline">
                        {t("Set up Mailgun")}
                    </Link>
                </span>
            </AlertDescription>
        </Alert>
    );
}
