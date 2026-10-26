export type CustomerState = "active" | "inactive" | "suspended" | "deleted";

export type CustomerPlan = {
    id: string | null;
    name: string;
    price: number | null; // null when revenue is hidden (paid plans)
    validity: number;
    activatedAt: string | null;
    expiresAt: string | null;
    expired: boolean;
    daysLeft: number;
};

export type CustomerRow = {
    id: string;
    name: string;
    email: string;
    avatar: string | null;
    state: CustomerState;
    verified: boolean;
    plan: CustomerPlan | null;
    vcards: number;
    stores: number;
    joined: string | null;
};

export type PlanOption = {
    id: string;
    name: string;
    price: number | null; // null when revenue is hidden (paid plans)
    validity: number;
    type?: string;
    vcards?: number;
    stores?: number;
};

export type CustomerTab = "all" | "verified" | "unverified" | "inactive" | "banned" | "deleted";

/** Minimal customer shape the action menu needs. */
export type ActionTarget = {
    id: string;
    name: string;
    email: string;
    state: CustomerState;
    verified: boolean;
    plan: CustomerPlan | null;
};

/** 999 means unlimited in plan limits. */
export const limitLabel = (value: number | undefined, unlimited: string) =>
    value === undefined ? "" : value >= 999 ? unlimited : String(value);
