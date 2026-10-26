<?php

namespace App\Support\Store;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * The options (variations) of one store product, ready for a product page:
 * which option to start on, whether anything can be bought, and the prices to show.
 * Every store theme uses this, so the rules live in one place.
 */
class ProductOptions
{
    /** @var Collection<int, object> */
    public readonly Collection $list;

    public readonly bool $hasOptions;

    /** Index in $list of the option the page starts on (the first one in stock) */
    public readonly int $initialIndex;

    public readonly ?object $initial;

    public readonly bool $canBuy;

    public readonly float $salesPrice;

    public readonly ?float $regularPrice;

    public function __construct(object $product, iterable $variants = [])
    {
        $this->list = collect($variants)->values();
        $this->hasOptions = (int) ($product->has_variants ?? 0) === 1 && $this->list->isNotEmpty();

        $firstInStock = $this->hasOptions ? $this->list->search(fn ($variant) => self::inStock($variant)) : false;

        $this->initialIndex = $firstInStock === false ? 0 : $firstInStock;
        $this->initial = $this->hasOptions ? $this->list[$this->initialIndex] : null;
        $this->canBuy = ($product->product_status ?? 'instock') === 'instock'
            && (! $this->hasOptions || $firstInStock !== false);

        $source = $this->initial ?? $product;
        $this->salesPrice = (float) $source->sales_price;
        $this->regularPrice = isset($source->regular_price) && (float) $source->regular_price > (float) $source->sales_price
            ? (float) $source->regular_price
            : null;
    }

    public static function inStock(object $variant): bool
    {
        return ($variant->status ?? 'instock') === 'instock';
    }

    public function isActive(int $index): bool
    {
        return $this->canBuy && $index === $this->initialIndex;
    }

    /**
     * data-* attributes for the element wrapping the product's price, options and add button
     * (read by public/js/store/storefront.js).
     */
    public function attributes(object $product, string $activeClass = 'active'): HtmlString
    {
        $images = array_filter(array_map('trim', explode(',', (string) $product->product_image)));

        return self::toAttributes([
            'data-product'        => true,
            'data-product-id'     => $product->product_id,
            'data-name'           => $product->product_name,
            'data-price'          => $this->salesPrice,
            'data-image'          => $images ? url(reset($images)) : '',
            'data-subtitle'       => $product->product_short_description ?? '',
            'data-initial-option' => $this->initialIndex,
            'data-active-class'   => $activeClass,
        ]);
    }

    /** @param array<string, scalar> $attributes  true = attribute without a value; everything else is escaped */
    public static function toAttributes(array $attributes): HtmlString
    {
        return new HtmlString(collect($attributes)
            ->map(fn ($value, $name) => $value === true ? $name : $name . '="' . e((string) $value) . '"')
            ->implode(' '));
    }

    /** What the storefront script needs about each option */
    public function forScript(): array
    {
        return $this->list->map(fn ($variant) => [
            'id'           => $variant->id,
            'name'         => (string) $variant->variant_name,
            'price'        => (float) $variant->sales_price,
            'regularPrice' => $variant->regular_price !== null ? (float) $variant->regular_price : null,
            'inStock'      => self::inStock($variant),
        ])->all();
    }
}
