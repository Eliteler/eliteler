<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Support\SmartProducts;

use App\NfcCardOrder;
use App\NfcCardOrderHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Order tracking timeline (nfc_card_order_histories) and the shipped /
 * delivered timestamps on the order.
 */
class OrderHistory
{
    /** Statuses that mean the parcel has left. */
    private const SHIPPED = ['shipped', 'out for delivery', 'delivered'];

    /**
     * Add a timeline entry. Quietly does nothing on installs that have not
     * run the Smart Products migration yet.
     *
     * @param array{message?: ?string, tracking_number?: ?string, courier_partner?: ?string, notified?: bool, created_by?: ?int} $data
     */
    public static function record(NfcCardOrder $order, string $status, array $data = []): ?NfcCardOrderHistory
    {
        if (! self::available()) {
            return null;
        }

        self::stamp($order, $status);

        return NfcCardOrderHistory::create([
            'nfc_card_order_id' => $order->nfc_card_order_id,
            'order_status'      => $status,
            'message'           => ($data['message'] ?? null) ?: null,
            'tracking_number'   => ($data['tracking_number'] ?? null) ?: null,
            'courier_partner'   => ($data['courier_partner'] ?? null) ?: null,
            'notified'          => (bool) ($data['notified'] ?? false),
            'created_by'        => array_key_exists('created_by', $data) ? $data['created_by'] : Auth::id(),
        ]);
    }

    /**
     * Timeline for display, oldest first. Always starts with "placed" so
     * orders from before Smart Products still show something.
     *
     * @return array<int, array{status: string, date: ?string, message: ?string, tracking: ?string, courier: ?string, notified: bool}>
     */
    public static function timeline(NfcCardOrder $order): array
    {
        $rows = [[
            'status'   => 'placed',
            'date'     => $order->created_at?->toIso8601String(),
            'message'  => null,
            'tracking' => null,
            'courier'  => null,
            'notified' => false,
        ]];

        if (! self::available()) {
            return $rows;
        }

        foreach ($order->histories()->get() as $history) {
            $rows[] = [
                'status'   => $history->order_status,
                'date'     => $history->created_at?->toIso8601String(),
                'message'  => $history->message,
                'tracking' => $history->tracking_number,
                'courier'  => $history->courier_partner,
                'notified' => $history->notified,
            ];
        }

        return $rows;
    }

    /** First time an order ships / is delivered. */
    private static function stamp(NfcCardOrder $order, string $status): void
    {
        $changes = [];

        if (in_array($status, self::SHIPPED, true) && ! $order->shipped_at) {
            $changes['shipped_at'] = now();
        }

        if ($status === 'delivered' && ! $order->delivered_at) {
            $changes['delivered_at'] = now();
        }

        if ($changes) {
            $order->forceFill($changes)->save();
        }
    }

    private static function available(): bool
    {
        static $available;

        return $available ??= Schema::hasTable('nfc_card_order_histories');
    }
}
