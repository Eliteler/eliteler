import { Head, router, useForm } from "@inertiajs/react";
import {
    Check,
    ChevronLeft,
    ChevronRight,
    EyeOff,
    Loader2,
    LogIn,
    MoreHorizontal,
    Pencil,
    Plus,
    Power,
    PowerOff,
    Search,
    ShieldCheck,
    Trash2,
    Users,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import { ConfirmDialog } from "@admin/components/confirm-dialog";
import { EmptyState } from "@admin/components/empty-state";
import { FieldError } from "@admin/components/field-error";
import { PageHeader } from "@admin/components/page-header";
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from "@admin/components/ui/avatar";
import { Badge } from "@admin/components/ui/badge";
import { Button } from "@admin/components/ui/button";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@admin/components/ui/card";
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
    DropdownMenuSeparator,
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
import { formatDate } from "@admin/lib/date";
import { cn } from "@admin/lib/utils";

type Staff = {
    id: string;
    name: string;
    email: string;
    avatar: string;
    role: 3 | 4;
    active: boolean;
    permissions: string[];
    hideRevenue: boolean;
    createdAt: string | null;
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

type Group = {
    title: string;
    items: {
        key: string;
        label: string;
        grantable: boolean;
        revenue: boolean;
    }[];
};

type Props = {
    staff: PaginatedResponse<Staff>;
    groups: Group[];
    canLoginAs: boolean;
    canToggleRevenue: boolean;
    open: string | null;
    search?: string;
};

type Pending = { member: Staff; action: "status" | "delete" | "login" };

export default function StaffPage({
    staff,
    groups,
    canLoginAs,
    canToggleRevenue,
    open,
    search = "",
}: Props) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(search);
    const isFirstRender = useRef(true);

    const [editing, setEditing] = useState<Staff | "new" | null>(
        open === "new"
            ? "new"
            : (staff.data.find((member) => member.id === open) ?? null),
    );
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const total = groups.reduce((sum, group) => sum + group.items.length, 0);
    const roleLabel = (role: number) =>
        role === 3 ? t("Administrator") : t("Manager");

    // Real-time debounced search
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timer = setTimeout(() => {
            router.get(
                route("admin.users"),
                {
                    search: searchTerm || undefined,
                    per_page: staff.per_page,
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
            route("admin.users"),
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
            route("admin.users"),
            {
                page: pageNumber,
                per_page: staff.per_page,
                search: searchTerm || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const run = () => {
        if (!pending) return;
        const { member, action } = pending;
        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        };
        if (action === "login")
            router.post(route("admin.login-as.user", member.id), {}, options);
        else
            router.post(
                route(
                    action === "status"
                        ? "admin.update.user.status"
                        : "admin.delete.user",
                ),
                { id: member.id },
                options,
            );
    };

    const copy =
        pending &&
        {
            status: pending.member.active
                ? {
                      title: t("Deactivate :name?"),
                      description: t(
                          "They can't sign in to the admin panel until you activate them again.",
                      ),
                      label: t("Deactivate"),
                  }
                : {
                      title: t("Activate :name?"),
                      description: t(
                          "They can sign in to the admin panel again.",
                      ),
                      label: t("Activate"),
                  },
            delete: {
                title: t("Remove :name?"),
                description: t(
                    "Their admin account is deleted. This can't be undone.",
                ),
                label: t("Remove"),
            },
            login: {
                title: t("Sign in as :name?"),
                description: t(
                    "You'll see the admin panel with their permissions. Sign out and back in to return to your account.",
                ),
                label: t("Sign in as them"),
            },
        }[pending.action];

    return (
        <>
            <Head title={t("Administrators and staff")} />
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <PageHeader
                    title={t("Administrators and staff")}
                    description={t(
                        "People who can sign in to this admin panel, and what each of them can manage.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Administrators and staff") },
                    ]}
                    actions={
                        <Button onClick={() => setEditing("new")}>
                            <Plus />
                            {t("Add staff")}
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
                                value={String(staff.per_page)}
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
                                placeholder={t("Search staff...")}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-8 pl-8 text-xs sm:h-9 sm:pl-9 sm:text-sm"
                            />
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        {staff.data.length === 0 ? (
                            <EmptyState
                                icon={Users}
                                title={t("No staff match your search")}
                                description={
                                    staff.total === 0
                                        ? t(
                                              "Add teammates and choose which parts of the admin panel they can use.",
                                          )
                                        : undefined
                                }
                                className="border-0"
                            />
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="ps-4">
                                            {t("Name")}
                                        </TableHead>
                                        <TableHead>{t("Role")}</TableHead>
                                        <TableHead>{t("Access")}</TableHead>
                                        <TableHead>{t("Status")}</TableHead>
                                        <TableHead>{t("Added")}</TableHead>
                                        <TableHead className="pe-4 text-end">
                                            <span className="sr-only">
                                                {t("Actions")}
                                            </span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {staff.data.map((member) => (
                                        <TableRow
                                            key={member.id}
                                            className={cn(
                                                !member.active && "opacity-60",
                                            )}
                                        >
                                            <TableCell className="ps-4">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setEditing(member)
                                                    }
                                                    className="flex items-center gap-3 text-start"
                                                >
                                                    <Avatar className="size-8">
                                                        <AvatarImage
                                                            src={member.avatar}
                                                            alt=""
                                                        />
                                                        <AvatarFallback>
                                                            {member.name.slice(
                                                                0,
                                                                1,
                                                            )}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    <span className="flex min-w-0 flex-col">
                                                        <span className="truncate font-medium hover:underline">
                                                            {member.name}
                                                        </span>
                                                        <span className="truncate text-xs text-muted-foreground">
                                                            {member.email}
                                                        </span>
                                                    </span>
                                                </button>
                                            </TableCell>
                                            <TableCell>
                                                {roleLabel(member.role)}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground tabular-nums">
                                                <span className="flex flex-wrap items-center gap-2">
                                                    {member.permissions
                                                        .length === total
                                                        ? t("Everything")
                                                        : t(
                                                              ":count of :total areas",
                                                          )
                                                              .replace(
                                                                  ":count",
                                                                  String(
                                                                      member
                                                                          .permissions
                                                                          .length,
                                                                  ),
                                                              )
                                                              .replace(
                                                                  ":total",
                                                                  String(total),
                                                              )}
                                                    {member.hideRevenue && (
                                                        <Badge variant="outline">
                                                            {t(
                                                                "Revenue hidden",
                                                            )}
                                                        </Badge>
                                                    )}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {member.active ? (
                                                    <Badge variant="secondary">
                                                        {t("Active")}
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">
                                                        {t("Deactivated")}
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {formatDate(member.createdAt)}
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
                                                                    member,
                                                                )
                                                            }
                                                        >
                                                            <Pencil />
                                                            {t("Edit")}
                                                        </DropdownMenuItem>
                                                        {canLoginAs &&
                                                            member.active && (
                                                                <DropdownMenuItem
                                                                    onSelect={() =>
                                                                        setPending(
                                                                            {
                                                                                member,
                                                                                action: "login",
                                                                            },
                                                                        )
                                                                    }
                                                                >
                                                                    <LogIn />
                                                                    {t(
                                                                        "Sign in as them",
                                                                    )}
                                                                </DropdownMenuItem>
                                                            )}
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            onSelect={() =>
                                                                setPending({
                                                                    member,
                                                                    action: "status",
                                                                })
                                                            }
                                                        >
                                                            {member.active ? (
                                                                <PowerOff />
                                                            ) : (
                                                                <Power />
                                                            )}
                                                            {member.active
                                                                ? t(
                                                                      "Deactivate",
                                                                  )
                                                                : t("Activate")}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            onSelect={() =>
                                                                setPending({
                                                                    member,
                                                                    action: "delete",
                                                                })
                                                            }
                                                        >
                                                            <Trash2 />
                                                            {t("Remove")}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>

                    {/* Server Pagination Footer */}
                    {staff.total > 0 && (
                        <CardFooter className="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                            <span className="text-xs text-muted-foreground">
                                {t("Showing :from to :to of :total staff")
                                    .replace(":from", String(staff.from ?? 0))
                                    .replace(":to", String(staff.to ?? 0))
                                    .replace(":total", String(staff.total))}
                            </span>

                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(staff.current_page - 1)
                                    }
                                    disabled={staff.current_page <= 1}
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
                                            String(staff.current_page),
                                        )
                                        .replace(
                                            ":total",
                                            String(staff.last_page),
                                        )}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        goToPage(staff.current_page + 1)
                                    }
                                    disabled={
                                        staff.current_page >= staff.last_page
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
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                    {editing && (
                        <StaffForm
                            key={editing === "new" ? "new" : editing.id}
                            member={editing === "new" ? null : editing}
                            groups={groups}
                            canToggleRevenue={canToggleRevenue}
                            onClose={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(isOpen) => !isOpen && setPending(null)}
                title={
                    copy?.title.replace(":name", pending?.member.name ?? "") ??
                    ""
                }
                description={copy?.description}
                confirmLabel={copy?.label}
                destructive={
                    pending?.action === "delete" ||
                    (pending?.action === "status" && pending.member.active)
                }
                processing={processing}
                onConfirm={run}
            />
        </>
    );
}

function StaffForm({
    member,
    groups,
    canToggleRevenue,
    onClose,
}: {
    member: Staff | null;
    groups: Group[];
    canToggleRevenue: boolean;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const grantable = groups.flatMap((group) =>
        group.items.filter((item) => item.grantable).map((item) => item.key),
    );
    const form = useForm({
        user_id: member?.id ?? "",
        role: String(member?.role ?? 3),
        name: member?.name ?? "",
        email: member?.email ?? "",
        password: "",
        permissions: member?.permissions ?? [],
        hide_revenue: member?.hideRevenue ?? !canToggleRevenue,
    });
    const { data, setData, errors } = form;
    const selected = new Set(data.permissions);

    const toggle = (key: string) =>
        setData(
            "permissions",
            selected.has(key)
                ? data.permissions.filter((item) => item !== key)
                : [...data.permissions, key],
        );

    const toggleGroup = (group: Group, on: boolean) => {
        const keys = group.items
            .filter((item) => item.grantable)
            .map((item) => item.key);
        setData(
            "permissions",
            on
                ? [...new Set([...data.permissions, ...keys])]
                : data.permissions.filter((key) => !keys.includes(key)),
        );
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(
            member ? route("admin.update.user") : route("admin.save.user"),
            { preserveScroll: true, onSuccess: onClose },
        );
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <DialogHeader>
                <DialogTitle>
                    {member
                        ? t("Edit :name").replace(":name", member.name)
                        : t("Add staff")}
                </DialogTitle>
                <DialogDescription>
                    {t(
                        "They sign in with this email and password, and only see the areas you allow.",
                    )}
                </DialogDescription>
            </DialogHeader>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-2">
                    <Label htmlFor="name">{t("Name")}</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(event) =>
                            setData("name", event.target.value)
                        }
                        aria-invalid={!!errors.name}
                        required
                    />
                    <FieldError message={errors.name} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="email">{t("Email")}</Label>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(event) =>
                            setData("email", event.target.value)
                        }
                        aria-invalid={!!errors.email}
                        required
                    />
                    <FieldError message={errors.email} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label htmlFor="password">
                        {member ? t("New password") : t("Password")}
                    </Label>
                    <Input
                        id="password"
                        type="password"
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(event) =>
                            setData("password", event.target.value)
                        }
                        placeholder={
                            member
                                ? t("Leave blank to keep it")
                                : t("At least 8 characters")
                        }
                        aria-invalid={!!errors.password}
                        required={!member}
                    />
                    <FieldError message={errors.password} />
                </div>
                <div className="flex flex-col gap-2">
                    <Label>{t("Role")}</Label>
                    <Tabs
                        value={data.role}
                        onValueChange={(value) => setData("role", value)}
                    >
                        <TabsList className="w-full">
                            <TabsTrigger value="3" className="flex-1">
                                {t("Administrator")}
                            </TabsTrigger>
                            <TabsTrigger value="4" className="flex-1">
                                {t("Manager")}
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <FieldError message={errors.role} />
                </div>
            </div>

            <div className="flex flex-col gap-3">
                <div className="flex items-center justify-between gap-2">
                    <Label className="flex items-center gap-2">
                        <ShieldCheck className="size-4 text-muted-foreground" />
                        {t("What they can manage")}
                    </Label>
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        onClick={() =>
                            setData(
                                "permissions",
                                grantable.every((key) => selected.has(key))
                                    ? data.permissions.filter(
                                          (key) => !grantable.includes(key),
                                      )
                                    : [
                                          ...new Set([
                                              ...data.permissions,
                                              ...grantable,
                                          ]),
                                      ],
                            )
                        }
                    >
                        {grantable.every((key) => selected.has(key))
                            ? t("Clear all")
                            : t("Select all")}
                    </Button>
                </div>
                {groups.map((group) => {
                    const keys = group.items
                        .filter((item) => item.grantable)
                        .map((item) => item.key);
                    const allOn =
                        keys.length > 0 &&
                        keys.every((key) => selected.has(key));
                    return (
                        <div key={group.title} className="rounded-lg border">
                            <div className="flex items-center justify-between border-b bg-muted/40 px-3 py-2">
                                <span className="text-sm font-medium">
                                    {group.title}
                                </span>
                                {keys.length > 0 && (
                                    <button
                                        type="button"
                                        className="text-xs text-primary hover:underline"
                                        onClick={() =>
                                            toggleGroup(group, !allOn)
                                        }
                                    >
                                        {allOn ? t("None") : t("All")}
                                    </button>
                                )}
                            </div>
                            <div className="grid gap-1 p-2 sm:grid-cols-2">
                                {group.items.map((item) => {
                                    const blocked =
                                        data.hide_revenue && item.revenue;
                                    const on =
                                        selected.has(item.key) && !blocked;
                                    return (
                                        <button
                                            key={item.key}
                                            type="button"
                                            role="checkbox"
                                            aria-checked={on}
                                            disabled={
                                                !item.grantable || blocked
                                            }
                                            onClick={() => toggle(item.key)}
                                            title={
                                                blocked
                                                    ? t(
                                                          "Not available while revenue is hidden",
                                                      )
                                                    : item.grantable
                                                      ? undefined
                                                      : t(
                                                            "You don't have this permission yourself",
                                                        )
                                            }
                                            className="flex items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm hover:bg-muted/60 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <span
                                                className={cn(
                                                    "flex size-4 shrink-0 items-center justify-center rounded-[4px] border",
                                                    on &&
                                                        "border-primary bg-primary text-primary-foreground",
                                                )}
                                            >
                                                {on && (
                                                    <Check className="size-3" />
                                                )}
                                            </span>
                                            {item.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    );
                })}
            </div>

            <div className="flex flex-col gap-3">
                <Label className="flex items-center gap-2">
                    <EyeOff className="size-4 text-muted-foreground" />
                    {t("Restrictions")}
                </Label>
                <div className="rounded-lg border p-2">
                    <button
                        type="button"
                        role="checkbox"
                        aria-checked={data.hide_revenue}
                        disabled={!canToggleRevenue}
                        onClick={() =>
                            setData("hide_revenue", !data.hide_revenue)
                        }
                        title={
                            canToggleRevenue
                                ? undefined
                                : t("Revenue is hidden for your own account")
                        }
                        className="flex w-full items-start gap-2 rounded-md px-2 py-1.5 text-start text-sm hover:bg-muted/60 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span
                            className={cn(
                                "mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-[4px] border",
                                data.hide_revenue &&
                                    "border-primary bg-primary text-primary-foreground",
                            )}
                        >
                            {data.hide_revenue && <Check className="size-3" />}
                        </span>
                        <span className="flex flex-col gap-0.5">
                            {t("Hide revenue")}
                            <span className="text-xs text-muted-foreground">
                                {t(
                                    "No revenue, payments or prices anywhere in the admin panel. Money areas like transactions, plans and coupons are closed to them.",
                                )}
                            </span>
                        </span>
                    </button>
                </div>
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onClose}>
                    {t("Cancel")}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing && <Loader2 className="animate-spin" />}
                    {member ? t("Save") : t("Add staff")}
                </Button>
            </div>
        </form>
    );
}
