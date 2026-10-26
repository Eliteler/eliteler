export type UsedFor = "plan" | "nfc" | "ai_credits";

export type Coupon = {
    id: string;
    code: string;
    description: string | null;
    usedFor: UsedFor;
    itemIds: string[];
    items: string[] | null;
    type: "percentage" | "fixed";
    amount: number;
    expires: string | null;
    expired: boolean;
    perCustomer: number;
    total: number;
    active: boolean;
    redeemed?: number;
    customers?: number;
};

export type ItemOption = { id: string; name: string; price: number };

export const USED_FOR_LABELS: Record<UsedFor, string> = {
    plan: "Plans",
    nfc: "NFC cards",
    ai_credits: "AI credit packs",
};
