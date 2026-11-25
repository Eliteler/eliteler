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

namespace App\Services\SmartProducts;

use App\BusinessCard;
use App\NfcCardKey;
use App\NfcCardOrder;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Where a Smart Product's NFC chip sends people (nfc_card_keys).
 *
 * The chip itself always holds /nfc/{key}; this decides what that opens:
 *   link_type vcard → card_id (business card, card_type "vcard")
 *   link_type store → card_id (WhatsApp store, card_type "store")
 *   link_type url   → link_target (https/http, or upi:, tel:, mailto:, sms:)
 * link_status "linked" means a destination is set.
 *
 * A key belongs to a customer when nfc_card_keys.user_id is theirs or the
 * key was made for one of their orders (keys from before Smart Products).
 */
class ChipLinks
{
    public const URL_SCHEMES = ['https', 'http', 'upi', 'tel', 'mailto', 'sms'];

    /** order key => [order id, product name], for the customer's orders */
    public static function orderKeys(User $user): array
    {
        $map = [];

        NfcCardOrder::where('user_id', $user->id)
            ->leftJoin('nfc_card_designs', 'nfc_card_designs.nfc_card_id', '=', 'nfc_card_orders.nfc_card_id')
            ->get(['nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.order_details', 'nfc_card_designs.nfc_card_name'])
            ->each(function ($order) use (&$map) {
                $details = json_decode($order->order_details ?? '', true) ?: [];
                $keys    = $details['unique_key'] ?? [];

                foreach (is_array($keys) ? $keys : [$keys] as $key) {
                    if ($key) {
                        $map[$key] = ['order' => $order->nfc_card_order_id, 'product' => $details['order_item'] ?? $order->nfc_card_name];
                    }
                }
            });

        return $map;
    }

    /** Keys the customer owns. */
    public static function ownedQuery(User $user, ?array $orderKeys = null): Builder
    {
        $orderKeys ??= array_keys(self::orderKeys($user));

        return NfcCardKey::query()->where(function ($q) use ($user, $orderKeys) {
            $q->where('nfc_card_keys.user_id', $user->id);

            if ($orderKeys) {
                $q->orWhereIn('nfc_card_keys.unqiue_key', $orderKeys);
            }
        });
    }

    public static function findOwned(User $user, ?string $keyId): ?NfcCardKey
    {
        return $keyId ? self::ownedQuery($user)->where('nfc_card_key_id', $keyId)->first() : null;
    }

    /** The customer's activated business cards and stores, for the destination pickers. */
    public static function cards(User $user, string $type): \Illuminate\Support\Collection
    {
        return BusinessCard::where('user_id', $user->user_id)
            ->where('card_type', $type)
            ->where('card_status', 'activated')
            ->where('status', '1')
            ->orderBy('title')
            ->get(['card_id', 'title', 'card_url', 'custom_domain']);
    }

    /**
     * Point a key somewhere. $target is a card_id (vcard / store) or a URL.
     *
     * @throws ValidationException
     */
    public static function setDestination(NfcCardKey $key, User $user, string $type, ?string $target, ?string $label = null): void
    {
        $label = trim((string) $label) === '' ? null : mb_substr(trim($label), 0, 100);

        if ($type === 'url') {
            $url = self::normalizeUrl($target);

            if (! $url) {
                throw ValidationException::withMessages(['link_target' => __('Enter a full link starting with https:// (or upi:, tel:, mailto:, sms:).')]);
            }

            $key->forceFill(['link_type' => 'url', 'link_target' => $url, 'card_id' => null]);
        } elseif (in_array($type, ['vcard', 'store'], true)) {
            $card = BusinessCard::where('user_id', $user->user_id)
                ->where('card_id', (string) $target)
                ->where('card_type', $type)
                ->where('card_status', 'activated')
                ->where('status', '1')
                ->first();

            if (! $card) {
                throw ValidationException::withMessages(['card_id' => $type === 'store' ? __('Choose one of your stores.') : __('Choose one of your business cards.')]);
            }

            $key->forceFill(['link_type' => $type, 'card_id' => $card->card_id, 'link_target' => null]);
        } else {
            throw ValidationException::withMessages(['link_type' => __('Choose where the NFC tag should open.')]);
        }

        $key->forceFill(['link_status' => 'linked', 'label' => $label, 'user_id' => $user->id, 'status' => 1])->save();
    }

    /** Remove the destination; the key keeps its owner, label and link type. */
    public static function unlink(NfcCardKey $key, User $user): void
    {
        $key->forceFill(['card_id' => null, 'link_target' => null, 'link_status' => 'unlinked', 'user_id' => $user->id, 'status' => 1])->save();
    }

    /** Only full links with a safe scheme; returns null otherwise. */
    public static function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || mb_strlen($url) > 2048 || preg_match('/\s/', $url)) {
            return null;
        }

        // "example.com/review" → "https://example.com/review"
        if (! preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) && preg_match('/^[\w-]+(\.[\w-]+)+/', $url)) {
            $url = 'https://' . $url;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, self::URL_SCHEMES, true)) {
            return null;
        }

        if (in_array($scheme, ['http', 'https'], true) && ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $url;
    }

    /** Public address of a business card or store. */
    public static function cardUrl(BusinessCard $card): string
    {
        if ($card->custom_domain) {
            return 'https://www.' . $card->custom_domain . '/';
        }

        $subdomain = DB::table('config')->where('config_key', 'enable_subdomain')->value('config_value') == '1';

        return $subdomain ? route('subdomain.profile', $card->card_url) : route('profile', $card->card_url);
    }

    /** Where a tap goes right now, or null when nothing (valid) is set. */
    public static function destination(NfcCardKey $key): ?string
    {
        if ($key->link_status !== 'linked') {
            return null;
        }

        // A URL key that was linked to a card by an older screen still opens the card
        if (($key->link_type ?? 'vcard') === 'url' && ! $key->card_id) {
            return self::normalizeUrl($key->link_target);
        }

        if (! $key->card_id) {
            return null;
        }

        $card = BusinessCard::where('card_id', $key->card_id)->first();

        return $card ? self::cardUrl($card) : null;
    }

    /** Count a tap (one cheap UPDATE). */
    public static function recordScan(NfcCardKey $key): void
    {
        NfcCardKey::whereKey($key->getKey())->update([
            'scan_count'      => DB::raw('scan_count + 1'),
            'last_scanned_at' => now(),
        ]);
    }

    /** Short text for lists: "Business card: My card", "https://…". */
    public static function describe(NfcCardKey $key, ?array $cardTitles = null): ?string
    {
        if ($key->link_status !== 'linked') {
            return null;
        }

        if (($key->link_type ?? 'vcard') === 'url' && ! $key->card_id) {
            return $key->link_target;
        }

        $title = $cardTitles[$key->card_id] ?? BusinessCard::where('card_id', $key->card_id)->value('title');

        return $title ?: null;
    }
}
