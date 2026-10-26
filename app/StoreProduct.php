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

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StoreProduct extends Model
{
    protected $fillable = [
        'card_id',
        'product_id',
        'category_id',
        'badge',
        'product_image',
        'product_name',
        'product_short_description',
        'product_description',
        'regular_price',
        'sales_price',
        'product_status'
    ];

    /**
     * Lowest in-stock option price of the outer query's store_products row, for "Starts from".
     * Null for products without (in-stock) options. Use with selectSub/addSelect as min_variant_price.
     */
    public static function minVariantPriceQuery(): Builder
    {
        return DB::table('store_product_variants')
            ->selectRaw('MIN(store_product_variants.sales_price)')
            ->whereColumn('store_product_variants.product_id', 'store_products.product_id')
            ->where('store_product_variants.status', 'instock');
    }

    /** The product has options (variations) the shopper must choose from */
    public function hasOptions(): bool
    {
        return (int) ($this->has_variants ?? 0) === 1;
    }

    /** Price shown on listings: the cheapest in-stock option ("Starts from") or the product price */
    public function listingPrice(): float
    {
        return (float) ($this->hasOptions() && $this->min_variant_price !== null ? $this->min_variant_price : $this->sales_price);
    }

    /** Crossed-out price on listings, only for products without options that are on sale */
    public function listingRegularPrice(): ?float
    {
        return ! $this->hasOptions() && $this->regular_price && (float) $this->regular_price > (float) $this->sales_price
            ? (float) $this->regular_price
            : null;
    }

    /** data-* attributes for a listing "Add to cart" button (read by public/js/store/storefront.js) */
    public function cartAttributes(): \Illuminate\Support\HtmlString
    {
        $images = array_filter(array_map('trim', explode(',', (string) $this->product_image)));

        return \App\Support\Store\ProductOptions::toAttributes([
            'data-add-to-cart' => true,
            'data-product-id'  => $this->product_id,
            'data-name'        => $this->product_name,
            'data-price'       => (float) $this->sales_price,
            'data-image'       => $images ? url(reset($images)) : '',
            'data-subtitle'    => $this->product_short_description ?? '',
        ]);
    }

    public function product_category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id', 'category_id');
    }
}
 