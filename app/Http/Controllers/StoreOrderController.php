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

namespace App\Http\Controllers;

use App\BusinessCard;
use App\StoreOrder;
use App\StoreProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
    /** store_orders.delivery_method values (the storefront sends them capitalised) */
    private const DELIVERY_METHODS = ['order for delivery', 'take away', 'dine in'];

    /**
     * Place order ajax
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder(Request $request)
    {
        // Check items in cart
        if ($request->order_items == null) {
            return response()->json([
                'status' => 'failed',
                'message' => trans('Please add atleast one product.')
            ]);
        }

        // Customer details and delivery type ("Order For Delivery" → store_orders' "order for delivery")
        $validator = Validator::make($request->all(), [
            'store_id'           => 'required|string',
            'customer_name'      => 'required|string|max:191',
            'customer_phone'     => 'required|string|max:30',
            'delivery_address'   => 'nullable|string|max:1000',
            'delivery_note'      => 'nullable|string|max:1000',
            'delivery_method'    => ['required', 'string', function ($attribute, $value, $fail) {
                if (! in_array(Str::lower((string) $value), self::DELIVERY_METHODS, true)) {
                    $fail(trans('Please choose a delivery type.'));
                }
            }],
            'order_items'        => 'required|array|min:1|max:100',
            'order_items.*.qty'  => 'required|integer|min:1|max:999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->errors()->first(),
            ]);
        }

        // Get store details (only a live store takes orders)
        $storeDetails = BusinessCard::where('card_id', $request->store_id)->where('card_type', 'store')->where('card_status', 'activated')->where('status', '1')->first();
        if (! $storeDetails) {
            return response()->json([
                'status' => 'failed',
                'message' => trans('Store not found!')
            ]);
        }

        // Items priced from the database, never from the browser
        $priced = $this->pricedItems($storeDetails->card_id, $request->order_items);
        if (is_string($priced)) {
            return response()->json([
                'status' => 'failed',
                'message' => $priced
            ]);
        }

        // Order number
        $order_number = "OD" . preg_replace('/\D/', '', Str::uuid());

        // Invoice prefix
        $invoice_prefix = isset(json_decode($storeDetails->description)->invoice_prefix) ? json_decode($storeDetails->description)->invoice_prefix : 'INV-';

        // Delivery details (Generate JSON)
        $deliveryDetails = [];
        $deliveryDetails['name'] = $request->customer_name ?? '';
        $deliveryDetails['address'] = $request->delivery_address ?? '';
        $deliveryDetails['mobile'] = $request->customer_phone ?? '';
        $deliveryDetails['notes'] = $request->delivery_note ?? '';
        $deliveryDetails = json_encode($deliveryDetails);

        $orderItems = json_encode(['items' => $priced['items']], JSON_UNESCAPED_SLASHES);

        // Place order  
        $order = new StoreOrder;
        $order->user_id = $storeDetails->user_id;
        $order->store_id = $storeDetails->card_id;
        $order->order_number = $order_number;
        $order->order_item = $orderItems;
        $order->delivery_method = Str::lower($request->delivery_method);
        $order->delivery_details = $deliveryDetails;
        $order->payment_method = "cash";
        $order->order_total = $priced['total'];
        $order->invoice_prefix = $invoice_prefix;
        $order->invoice_details = "";
        $order->order_notes = $request->delivery_note ?? "";

        // Invoice number (per store): lock the store row so parallel orders can't get the same number
        DB::transaction(function () use ($order, $storeDetails) {
            BusinessCard::where('card_id', $storeDetails->card_id)->lockForUpdate()->first();

            $order->invoice_number = (int) StoreOrder::where('store_id', $storeDetails->card_id)
                ->max(DB::raw('CAST(invoice_number AS UNSIGNED)')) + 1;
            $order->save();
        });

        // Return success message
        return response()->json([
            'status' => 'success',
            'message' => __('Order placed successfully.')
        ]);
    }

    /**
     * The cart's items with name, option, image and price read from the store's
     * products, and the order total. A message instead when an item can't be bought
     * or its price changed since it was added (so the WhatsApp message, which the
     * browser writes, never shows other prices than the saved order).
     *
     * @return array{items: array<int, array>, total: string}|string
     */
    private function pricedItems(string $storeId, array $orderItems)
    {
        $items = [];
        $total = 0.0;

        foreach ($orderItems as $orderItem) {
            $productKey = (string) ($orderItem['product_id'] ?? '');

            // product_id (older carts saved the row id)
            $product = StoreProduct::where('card_id', $storeId)
                ->where('status', '1')
                ->where(fn ($query) => $query->where('product_id', $productKey)->orWhere('id', $productKey))
                ->first();

            if (! $product || $product->product_status !== 'instock') {
                return trans(':name is no longer available. Please remove it from your cart.', ['name' => $orderItem['product_name'] ?? trans('A product')]);
            }

            $variant = null;
            if ((int) $product->has_variants === 1 && DB::table('store_product_variants')->where('product_id', $product->product_id)->exists()) {
                $variant = DB::table('store_product_variants')
                    ->where('product_id', $product->product_id)
                    ->where('id', $orderItem['variant_id'] ?? null)
                    ->first();

                if (! $variant || $variant->status !== 'instock') {
                    return trans(':name is no longer available in this option. Please remove it from your cart.', ['name' => $product->product_name]);
                }
            }

            $price = (float) ($variant ? $variant->sales_price : $product->sales_price);
            if (abs($price - (float) ($orderItem['price'] ?? 0)) > 0.005) {
                return trans('The price of :name has changed. Please remove it from your cart and add it again.', ['name' => $product->product_name]);
            }

            $quantity = (int) $orderItem['qty'];
            $total += $price * $quantity;

            // Site-relative like older orders ("/storage/…"); full URLs stay as they are
            $image = trim(explode(',', (string) $product->product_image)[0]);
            if ($image !== '' && ! Str::startsWith($image, ['http://', 'https://', '/'])) {
                $image = '/' . $image;
            }

            $items[] = [
                'product_image' => $image,
                'product_name'  => $product->product_name,
                'variant_name'  => $variant?->variant_name,
                'price'         => number_format($price, 2, '.', ''),
                'quantity'      => $quantity,
            ];
        }

        return ['items' => $items, 'total' => number_format($total, 2, '.', '')];
    }
}
