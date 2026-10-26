import { Head, Link } from "@inertiajs/react";
import { ArrowUpCircle, LayoutTemplate, Puzzle } from "lucide-react";
import { useTranslation } from "react-i18next";

import { EmptyState } from "@admin/components/empty-state";
import { PageHeader } from "@admin/components/page-header";
import { Button } from "@admin/components/ui/button";

type Props = {
    name?: string;
    message?: string;
    title?: string;
    update?: boolean; // an idle old (Blade) version: ask for the new version
    section?: "plugins" | "templates"; // where the page belongs (breadcrumbs, back button)
};

/** Shown when a plugin's or web template's React page is missing, too new, fails to load, or needs updating. */
export default function PluginUnavailable({ name, message, title, update, section = "plugins" }: Props) {
    const { t } = useTranslation();
    const templates = section === "templates";
    const label = name ?? (templates ? t("Template") : t("Plugin"));
    const back = templates
        ? { href: route("admin.web-templates"), crumb: t("Website templates"), action: update ? t("Go to Website templates") : t("Back to templates") }
        : { href: route("admin.plugins"), crumb: t("Plugins"), action: update ? t("Go to Plugins") : t("Back to plugins") };

    return (
        <>
            <Head title={label} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={label}
                    breadcrumbs={[{ label: t("Dashboard"), href: route("admin.dashboard") }, { label: back.crumb, href: back.href }, { label }]}
                />
                <EmptyState
                    icon={update ? ArrowUpCircle : templates ? LayoutTemplate : Puzzle}
                    title={title ?? t("This page couldn't be opened")}
                    description={
                        message ??
                        (templates
                            ? t("The template's files may be incomplete. Reinstall the template and try again.")
                            : t("The plugin's files may be incomplete. Reinstall the plugin and try again."))
                    }
                    action={
                        <Button variant="secondary" asChild>
                            <Link href={back.href}>{back.action}</Link>
                        </Button>
                    }
                />
            </div>
        </>
    );
}
