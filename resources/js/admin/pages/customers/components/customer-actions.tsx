import { Link, router } from "@inertiajs/react";
import {
    BadgeCheck,
    Ban,
    CreditCard,
    Eye,
    LogIn,
    MoreHorizontal,
    Pencil,
    Power,
    PowerOff,
    RotateCcw,
    ShieldCheck,
    Trash2,
} from "lucide-react";
import { useState, type ReactNode } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { Button } from "@admin/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@admin/components/ui/dropdown-menu";

import type { ActionTarget, PlanOption } from "../types";
import { ChangePlanDialog } from "./change-plan-dialog";

type Confirmable = "verify" | "suspend" | "unsuspend" | "activate" | "deactivate" | "restore" | "delete" | "login";

/**
 * Everything an admin can do to a customer, with confirmations.
 * Used in the customers table and on the customer page.
 */
export function CustomerActions({
    customer,
    plans,
    showView = true,
    trigger,
    planDialogOpen,
    onPlanDialogChange,
}: {
    customer: ActionTarget;
    plans: PlanOption[];
    showView?: boolean;
    trigger?: ReactNode;
    planDialogOpen?: boolean;
    onPlanDialogChange?: (open: boolean) => void;
}) {
    const { t } = useTranslation();
    const [confirm, setConfirm] = useState<Confirmable | null>(null);
    const [processing, setProcessing] = useState(false);
    const [planOpenLocal, setPlanOpenLocal] = useState(false);
    const planOpen = planDialogOpen ?? planOpenLocal;
    const setPlanOpen = onPlanDialogChange ?? setPlanOpenLocal;

    const { state, verified } = customer;
    const deleted = state === "deleted";

    const post = (url: string, data: Record<string, string>) =>
        router.post(url, data, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setConfirm(null);
            },
        });

    const run = () => {
        switch (confirm) {
            case "verify":
            case "suspend":
            case "unsuspend":
                return post(route("admin.customer.verified"), { id: customer.id, action: confirm });
            case "activate":
            case "deactivate":
                return post(route("admin.update.status"), { id: customer.id });
            case "restore":
                return post(route("admin.undelete.customer"), { id: customer.id });
            case "delete":
                return post(route("admin.delete.customer"), { id: customer.id });
            case "login":
                // Full page load: this signs the admin out and into the customer's account.
                window.location.href = route("admin.login-as.customer", customer.id);
        }
    };

    const copy: Record<Confirmable, { title: string; description: string; label: string; destructive?: boolean }> = {
        verify: {
            title: t("Mark email as verified?"),
            description: t("The customer can use their account without confirming their email address."),
            label: t("Mark verified"),
        },
        suspend: {
            title: t("Suspend this customer?"),
            description: t("They can no longer sign in and all their business cards and stores go offline."),
            label: t("Suspend"),
            destructive: true,
        },
        unsuspend: {
            title: t("Unsuspend this customer?"),
            description: t("They can sign in again and their business cards and stores go back online."),
            label: t("Unsuspend"),
        },
        activate: {
            title: t("Activate this customer?"),
            description: t("Their account and business cards become active again."),
            label: t("Activate"),
        },
        deactivate: {
            title: t("Deactivate this customer?"),
            description: t("Their account is set to inactive and their business cards go offline."),
            label: t("Deactivate"),
            destructive: true,
        },
        restore: {
            title: t("Restore this account?"),
            description: t("The customer deleted this account. Restoring gives back their original email address and access."),
            label: t("Restore"),
        },
        delete: {
            title: t("Permanently delete this customer?"),
            description: t(
                "The account, its business cards and stores and their content are deleted. Payment records are kept. This cannot be undone.",
            ),
            label: t("Delete permanently"),
            destructive: true,
        },
        login: {
            title: t("Sign in as this customer?"),
            description: t("You will be signed out of the admin panel and signed in to the customer's dashboard."),
            label: t("Sign in as customer"),
        },
    };

    const current = confirm ? copy[confirm] : null;

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    {trigger ?? (
                        <Button variant="ghost" size="icon-sm" aria-label={t("Actions")}>
                            <MoreHorizontal />
                        </Button>
                    )}
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-52">
                    {showView && (
                        <DropdownMenuItem asChild>
                            <Link href={route("admin.view.customer", customer.id)}>
                                <Eye />
                                {t("View details")}
                            </Link>
                        </DropdownMenuItem>
                    )}
                    {!deleted && (
                        <>
                            <DropdownMenuItem asChild>
                                <Link href={route("admin.edit.customer", customer.id)}>
                                    <Pencil />
                                    {t("Edit")}
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem onSelect={() => setPlanOpen(true)}>
                                <CreditCard />
                                {t("Change plan")}
                            </DropdownMenuItem>
                        </>
                    )}
                    {state === "active" && (
                        <DropdownMenuItem onSelect={() => setConfirm("login")}>
                            <LogIn />
                            {t("Sign in as customer")}
                        </DropdownMenuItem>
                    )}
                    <DropdownMenuSeparator />
                    {state === "active" && !verified && (
                        <DropdownMenuItem onSelect={() => setConfirm("verify")}>
                            <BadgeCheck />
                            {t("Mark email verified")}
                        </DropdownMenuItem>
                    )}
                    {state === "inactive" && (
                        <DropdownMenuItem onSelect={() => setConfirm("activate")}>
                            <Power />
                            {t("Activate")}
                        </DropdownMenuItem>
                    )}
                    {state === "active" && (
                        <DropdownMenuItem onSelect={() => setConfirm("deactivate")}>
                            <PowerOff />
                            {t("Deactivate")}
                        </DropdownMenuItem>
                    )}
                    {state === "suspended" ? (
                        <DropdownMenuItem onSelect={() => setConfirm("unsuspend")}>
                            <ShieldCheck />
                            {t("Unsuspend")}
                        </DropdownMenuItem>
                    ) : (
                        !deleted && (
                            <DropdownMenuItem onSelect={() => setConfirm("suspend")}>
                                <Ban />
                                {t("Suspend")}
                            </DropdownMenuItem>
                        )
                    )}
                    {deleted && (
                        <DropdownMenuItem onSelect={() => setConfirm("restore")}>
                            <RotateCcw />
                            {t("Restore account")}
                        </DropdownMenuItem>
                    )}
                    <DropdownMenuItem variant="destructive" onSelect={() => setConfirm("delete")}>
                        <Trash2 />
                        {t("Delete permanently")}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <ConfirmDialog
                open={confirm !== null}
                onOpenChange={(open) => !open && setConfirm(null)}
                title={current?.title ?? ""}
                description={current ? `${customer.name} (${customer.email}). ${current.description}` : undefined}
                confirmLabel={current?.label}
                destructive={current?.destructive}
                processing={processing}
                onConfirm={run}
            />
            {!deleted && planOpen && (
                <ChangePlanDialog customer={customer} plans={plans} open={planOpen} onOpenChange={setPlanOpen} />
            )}
        </>
    );
}
