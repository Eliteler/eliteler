<?php

namespace App\Support\Store;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** 
 * Options (variations) of a store product in store_product_variants, the same rows
 * the Blade editor writes: name, optional regular price, sales price, stock status.
 * Saving replaces the product's whole set; orders keep the option's name, not its id.
 */
class StoreProductVariants
{
    public const MAX = 50;

    public const STATUSES = ['instock', 'outstock'];

    /** Validation rules for `hasVariants` + `variants` sent by the product form. */
    public static function rules(Request $request): array
    {
        $on = $request->boolean('hasVariants');

        return [
            'hasVariants'             => 'nullable|boolean',
            'variants'                => [Rule::requiredIf($on), 'nullable', 'array', 'max:' . self::MAX],
            // ":" and "|" separate options in the CSV export, so they can't be part of a name
            'variants.*.name'         => [Rule::requiredIf($on), 'nullable', 'string', 'max:191', 'not_regex:/[:|]/'],
            'variants.*.regularPrice' => 'nullable|numeric|min:0',
            'variants.*.salesPrice'   => [Rule::requiredIf($on), 'nullable', 'numeric', 'min:0'],
            'variants.*.status'       => ['nullable', Rule::in(self::STATUSES)],
        ];
    }

    public static function attributes(): array
    {
        return [
            'variants'                => __('Variations'),
            'variants.*.name'         => __('Variation name'),
            'variants.*.regularPrice' => __('Regular price'),
            'variants.*.salesPrice'   => __('Sales price'),
            'variants.*.status'       => __('Status'),
        ];
    }

    public static function messages(): array
    {
        return [
            'variants.required'         => __('Add at least one variation, or turn variations off.'),
            'variants.*.name.not_regex' => __('Variation names can’t contain “:” or “|”.'),
        ];
    }

    /**
     * The validated options as rows (empty when variations are off).
     *
     * @return array<int, array{name: string, regular_price: ?float, sales_price: float, status: string}>
     */
    public static function fromRequest(Request $request): array
    {
        if (! $request->boolean('hasVariants')) {
            return [];
        }

        return collect($request->input('variants', []))
            ->map(fn ($variant) => [
                'name'          => trim((string) $variant['name']),
                'regular_price' => isset($variant['regularPrice']) && $variant['regularPrice'] !== '' ? (float) $variant['regularPrice'] : null,
                'sales_price'   => (float) $variant['salesPrice'],
                'status'        => in_array($variant['status'] ?? null, self::STATUSES, true) ? $variant['status'] : 'instock',
            ])
            ->values()
            ->all();
    }

    /** Replace the product's options with $variants. Call inside a transaction. */
    public static function replace(string $productId, array $variants): void
    {
        DB::table('store_product_variants')->where('product_id', $productId)->delete();

        if ($variants === []) {
            return;
        }

        DB::table('store_product_variants')->insert(array_map(fn ($variant) => [
            'product_id'    => $productId,
            'variant_name'  => $variant['name'],
            'regular_price' => $variant['regular_price'],
            'sales_price'   => $variant['sales_price'],
            'status'        => $variant['status'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ], $variants));
    }

    public static function delete(string $productId): void
    {
        DB::table('store_product_variants')->where('product_id', $productId)->delete();
    }

    /**
     * Options of several products, in saved order, keyed by product_id.
     *
     * @return Collection<string, Collection<int, object>>
     */
    public static function forProducts(array $productIds): Collection
    {
        if ($productIds === []) {
            return collect();
        }

        return DB::table('store_product_variants')
            ->whereIn('product_id', $productIds)
            ->orderBy('id')
            ->get(['product_id', 'variant_name', 'regular_price', 'sales_price', 'status'])
            ->groupBy('product_id');
    }
}

