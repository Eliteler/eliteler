<?php

namespace App\Support\Store;

use App\Currency;
use App\StoreBusinessHour;
use App\Services\GoBizCommonService;

/**
 * Everything a store page needs that doesn't depend on the theme: its addresses (main domain,
 * subdomain or custom domain) and the settings of the shared storefront script
 * (public/js/store/storefront.js): cart storage, money format, WhatsApp, opening hours, texts.
 *
 * Built once per store per request and shared with every store view by StorefrontComposer.
 */
class StorefrontContext
{
    private static array $cache = [];

    public readonly bool $onOwnDomain;

    public readonly string $storeUrl;

    public readonly string $productUrlPrefix;

    public readonly string $categoriesUrl;

    private function __construct(private object $store)
    {
        $this->onOwnDomain = request()->getHost() !== parse_url(config('app.url'), PHP_URL_HOST);
        $this->storeUrl = $this->onOwnDomain ? url('/') : url($store->card_url);
        $this->productUrlPrefix = $this->onOwnDomain ? url('product') : url($store->card_url . '/product');
        $this->categoriesUrl = $this->onOwnDomain ? url('categories') : url($store->card_url . '/categories');
    }

    /** The store's home page (product list) is open */
    public function onHome(): bool
    {
        return $this->onOwnDomain ? request()->is('/') : request()->is($this->store->card_url);
    }

    /** The store's categories page is open */
    public function onCategories(): bool
    {
        return request()->is($this->onOwnDomain ? 'categories*' : $this->store->card_url . '/categories*');
    }

    public static function for(object $store): self
    {
        return self::$cache[$store->card_id] ??= new self($store);
    }

    public function productUrl(object $product): string
    {
        return $this->productUrlPrefix . '/' . $product->id;
    }

    /** Settings for GoBizStore.init() in the shared storefront script */
    public function scriptConfig(): array
    {
        $details = json_decode($this->store->description ?? '', true) ?: [];
        $delivery = json_decode($this->store->delivery_options ?? '', true) ?: [];
        $hours = StoreBusinessHour::where('store_id', $this->store->card_id)->value('business_hours');

        return [
            'storeId'    => $this->store->card_id,
            'storageKey' => 'cart_' . $this->store->card_id,
            'orderUrl'   => url('order/place'),
            'csrf'       => csrf_token(),
            'money'      => $this->money($details['currency'] ?? null),
            'whatsapp'   => [
                'number'  => preg_replace('/\D/', '', (string) ($details['whatsapp_no'] ?? '')),
                'message' => (string) ($details['whatsapp_msg'] ?? ''),
            ],
            // null = no opening hours set: the store takes orders any time
            'hours'      => json_decode((string) $hours, true) ?: null,
            'delivery'   => [
                'Order For Delivery' => (bool) ($delivery['order_for_delivery'] ?? true),
                'Take Away'          => (bool) ($delivery['take_away'] ?? false),
                'Dine In'            => (bool) ($delivery['dine_in'] ?? false),
            ],
            'texts'      => $this->texts(),
        ];
    }

    /** Same rules as formatCurrencyBase() in app/helpers.php */
    private function money(?string $isoCode): array
    {
        $config = GoBizCommonService::config()->pluck('config_value', 'config_key');
        $currency = Currency::where('iso_code', $isoCode)->first();

        return [
            'symbol'      => (string) ($currency->symbol ?? ''),
            'symbolFirst' => ($currency->symbol_first ?? 'true') !== 'false',
            'format'      => (string) ($config['currency_format_type'] ?? '1,234,567.89'),
            'decimals'    => (int) ($config['currency_decimals_place'] ?? 2),
        ];
    }

    private function texts(): array
    {
        return collect([
            'addedToCart'      => 'Item added to cart',
            'cartUpdated'      => 'Cart updated',
            'itemRemoved'      => 'Item Removed',
            'inStock'          => 'In Stock',
            'outOfStock'       => 'Out of Stock',
            'optionOutOfStock' => 'Selected option is out of stock',
            'closed'           => 'Sorry, we are currently closed.',
            'fillFields'       => 'Please fill out all fields.',
            'orderFailed'      => 'Failed to place order.',
            'orderPlaced'      => 'Order Placed!',
            'newOrder'         => 'New Order',
            'orderDetails'     => 'Order Details:',
            'total'            => 'Total',
            'customerDetails'  => 'Customer Details:',
            'customerName'     => 'Customer Name',
            'contactNumber'    => 'Contact Number',
            'deliveryAddress'  => 'Delivery Address',
            'deliveryType'     => 'Delivery Type',
            'notes'            => 'Notes',
            'Order For Delivery' => 'Order For Delivery',
            'Take Away'        => 'Take Away',
            'Dine In'          => 'Dine In',
        ])->map(fn (string $text) => __($text))->all();
    }
}
