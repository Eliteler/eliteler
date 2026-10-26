export type PaymentStatus = "success" | "pending" | "failed" | "cancelled";

export type DeliveryStatus =
    | "pending"
    | "processing"
    | "printing process begun"
    | "shipped"
    | "out for delivery"
    | "delivered"
    | "hold"
    | "cancelled";

export const DELIVERY_LABELS: Record<DeliveryStatus, string> = {
    pending: "Awaiting payment",
    processing: "New",
    "printing process begun": "Printing",
    shipped: "Shipped",
    "out for delivery": "Out for delivery",
    delivered: "Delivered",
    hold: "On hold",
    cancelled: "Cancelled",
};

export const PAYMENT_LABELS: Record<PaymentStatus, string> = {
    success: "Paid",
    pending: "Pending",
    failed: "Failed",
    cancelled: "Cancelled",
};

export type Customer = { id: string; name: string; email: string };
