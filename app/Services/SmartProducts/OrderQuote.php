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

use App\AppliedCoupon;
use App\Coupon;
use App\NfcCardDesign;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The price of one Smart Product order. Every checkout path (checkout page,
 * coupon preview, all payment gateways and free orders) uses this, so the
 * customer is charged exactly what the checkout page showed.
 *
 *   subtotal = unit price × quantity
 *   tax      = subtotal × tax rate
 *   discount = fixed amount, or percentage of (subtotal + tax);
 *              never more than subtotal + tax
 *   shipping = flat fee once per order; free when the subtotal reaches
 *              "free shipping above" (when set). Not taxed or discounted.
 *   total    = subtotal + tax − discount + shipping
 *
 * Coupons are checked here too (active, not expired, valid for the product,
 * usage limits); a coupon that fails is ignored.
 */
class OrderQuote
{
    public float $unitPrice;
    public float $subtotal;
    public float $tax;
    public float $discount = 0;
    public float $shipping;
    public float $total;
    public ?Coupon $coupon = null;
    public ?string $couponCode = null;

    private function __construct(public NfcCardDesign $design, public int $quantity)
    {
    }

    /**
     * @param Coupon|string|null $coupon a coupon or a coupon code (" " or "" = none)
     */
    public static function make(NfcCardDesign $design, int $quantity, Coupon|string|null $coupon = null, ?User $user = null): self
    {
        $quote           = new self($design, max($quantity, 1));
        $quote->unitPrice = (float) $design->nfc_card_price;
        $quote->subtotal = round($quote->unitPrice * $quote->quantity, 2);
        $quote->tax      = round($quote->subtotal * self::taxRate() / 100, 2);

        if (is_string($coupon)) {
            $coupon = trim($coupon) === '' ? null : self::findCoupon($coupon);
        }

        if ($coupon && self::couponError($coupon, $design, $user ?? auth()->user()) === null) {
            $withTax          = $quote->subtotal + $quote->tax;
            $discount         = $coupon->coupon_type === 'fixed'
                ? (float) $coupon->coupon_amount
                : $withTax * (float) $coupon->coupon_amount / 100;
            $quote->discount   = round(min(max($discount, 0), $withTax), 2);
            $quote->coupon     = $coupon;
            $quote->couponCode = $coupon->coupon_code;
        }

        $quote->shipping = self::shippingFor($quote->subtotal);
        $quote->total    = round(max($quote->subtotal + $quote->tax - $quote->discount, 0) + $quote->shipping, 2);

        return $quote;
    }

    public static function findCoupon(string $code): ?Coupon
    {
        return Coupon::where('used_for', 'nfc')->where('coupon_code', trim($code))->first();
    }

    /** Why a coupon can't be used for this product and customer, or null when it can. */
    public static function couponError(Coupon $coupon, NfcCardDesign $design, ?User $user): ?string
    {
        if ((int) $coupon->status !== 1 || $coupon->used_for !== 'nfc') {
            return __('Coupon not valid!');
        }

        if ($coupon->coupon_expired_on && $coupon->coupon_expired_on < Carbon::now()) {
            return __('Coupon expired!');
        }

        $allowed = json_decode((string) $coupon->plan_id, true);
        if (! is_array($allowed)) {
            $allowed = $coupon->plan_id ? [$coupon->plan_id] : ['all'];
        }

        if (! in_array('all', $allowed) && ! in_array($design->nfc_card_id, $allowed)) {
            return __('This coupon is not applicable for the selected NFC Card!');
        }

        if ($user && AppliedCoupon::where('user_id', $user->id)->where('coupon_id', $coupon->coupon_id)->where('status', 1)->count() >= $coupon->coupon_user_usage_limit) {
            return __('Coupon already used.');
        }

        if (AppliedCoupon::where('coupon_id', $coupon->coupon_id)->where('status', 1)->count() >= $coupon->coupon_total_usage_limit) {
            return __('Total number of users already reached.');
        }

        return null;
    }

    /** Flat shipping fee for an order with this subtotal. */
    public static function shippingFor(float $subtotal): float
    {
        $fee       = (float) self::config('smart_product_shipping_fee', 0);
        $freeAbove = (float) self::config('smart_product_free_shipping_above', 0);

        if ($fee <= 0 || ($freeAbove > 0 && $subtotal >= $freeAbove)) {
            return 0.0;
        }

        return round($fee, 2);
    }

    public static function taxRate(): float
    {
        return (float) self::config('tax_value', 0);
    }

    /** nfc_card_orders.order_details */
    public function orderDetails(): array
    {
        return [
            'nfc_card_id'       => $this->design->nfc_card_id,
            'order_item'        => $this->design->nfc_card_name,
            'order_description' => $this->design->nfc_card_description,
            'product_type'      => $this->design->product_type ?: 'nfc_card',
            'order_quantity'    => $this->quantity,
            'price'             => $this->unitPrice,
            'invoice_amount'    => $this->total,
            'tax_name'          => self::config('tax_name'),
            'tax_type'          => self::config('tax_type'),
            'tax_value'         => self::config('tax_value'),
            'tax_amount'        => $this->tax,
            'applied_coupon'    => $this->couponCode,
            'discounted_price'  => $this->discount,
            'shipping_amount'   => $this->shipping,
            'subtotal'          => $this->subtotal,
            // Files the customer is asked for (kept as they were when ordered)
            'image_assets'      => OrderAssets::snapshotFor($this->design),
        ];
    }

    /** nfc_card_order_transactions.invoice_details */
    public function invoiceDetails(User $user): array
    {
        return [
            'from_billing_name'    => self::config('invoice_name'),
            'from_billing_address' => self::config('invoice_address'),
            'from_billing_city'    => self::config('invoice_city'),
            'from_billing_state'   => self::config('invoice_state'),
            'from_billing_zipcode' => self::config('invoice_zipcode'),
            'from_billing_country' => self::config('invoice_country'),
            'from_vat_number'      => self::config('tax_number'),
            'from_billing_phone'   => self::config('invoice_phone'),
            'from_billing_email'   => self::config('invoice_email'),
            'to_billing_name'      => $user->billing_name,
            'to_billing_address'   => $user->billing_address,
            'to_billing_city'      => $user->billing_city,
            'to_billing_state'     => $user->billing_state,
            'to_billing_zipcode'   => $user->billing_zipcode,
            'to_billing_country'   => $user->billing_country,
            'to_billing_phone'     => $user->billing_country_code . $user->billing_phone,
            'to_billing_email'     => $user->billing_email,
            'to_vat_number'        => $user->vat_number,
            'tax_name'             => self::config('tax_name'),
            'tax_type'             => self::config('tax_type'),
            'tax_value'            => self::config('tax_value'),
            'applied_coupon'       => $this->couponCode,
            'discounted_price'     => $this->discount,
            'shipping_amount'      => $this->shipping,
            'invoice_amount'       => $this->total,
            'subtotal'             => $this->subtotal,
            'tax_amount'           => $this->tax,
        ];
    }

    /** nfc_card_orders.delivery_address (taken from the customer's saved billing details). */
    public static function deliveryAddress(User $user): array
    {
        $phone = $user->billing_country_code . $user->billing_phone;

        return [
            'billing_name'     => $user->billing_name,
            'billing_address'  => $user->billing_address,
            'billing_city'     => $user->billing_city,
            'billing_state'    => $user->billing_state,
            'billing_zipcode'  => $user->billing_zipcode,
            'billing_country'  => $user->billing_country,
            'billing_phone'    => $phone,
            'billing_email'    => $user->billing_email,
            'shipping_name'    => $user->billing_name,
            'shipping_address' => $user->billing_address,
            'shipping_city'    => $user->billing_city,
            'shipping_state'   => $user->billing_state,
            'shipping_zipcode' => $user->billing_zipcode,
            'shipping_country' => $user->billing_country,
            'shipping_phone'   => $phone,
            'shipping_email'   => $user->billing_email,
            'type'             => $user->type,
            'vat_number'       => $user->vat_number,
        ];
    }

    /** For the checkout page and the coupon preview. */
    public function toArray(): array
    {
        return [
            'quantity'      => $this->quantity,
            'unitPrice'     => $this->unitPrice,
            'subtotal'      => $this->subtotal,
            'taxAmount'     => $this->tax,
            'discountPrice' => $this->discount,
            'shipping'      => $this->shipping,
            'total'         => $this->total,
        ];
    }

    /** @var \Illuminate\Support\Collection<string, string>|null settings, read once per request */
    private static $config;

    private static function config(string $key, mixed $default = null): mixed
    {
        self::$config ??= DB::table('config')->pluck('config_value', 'config_key');

        return self::$config[$key] ?? $default;
    }

    /** Forget cached settings (after they change in the same process). */
    public static function flushConfig(): void
    {
        self::$config = null;
    }
}
