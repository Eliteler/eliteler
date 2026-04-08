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
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
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

        // Order number
        $order_number = "OD" . preg_replace('/\D/', '', Str::uuid());

        // Get store details
        $storeDetails = BusinessCard::where('card_id', $request->store_id)->first();

        // Invoice prefix
        $invoice_prefix = isset(json_decode($storeDetails->description)->invoice_prefix) ? json_decode($storeDetails->description)->invoice_prefix : 'INV-';

        // Invoice number
        $orderNumber = StoreOrder::where('store_id', $request->store_id)->count() + 1;

        // Delivery details (Generate JSON)
        $deliveryDetails = [];
        $deliveryDetails['name'] = $request->customer_name ?? '';
        $deliveryDetails['address'] = $request->delivery_address ?? '';
        $deliveryDetails['mobile'] = $request->customer_phone ?? '';
        $deliveryDetails['notes'] = $request->delivery_note ?? '';
        $deliveryDetails = json_encode($deliveryDetails);

        // Order_items array to items array
        $order_items = $request->order_items;

        $items = [];

        foreach ($order_items as $order_item) {
            // Remove domain from URL
            $productImagePath = $order_item['product_image'];

            // Remove domain if present (e.g., https://yourdomain.com/img/...)
            if (Str::startsWith($productImagePath, ['http://', 'https://'])) {
                $productImagePath = Str::replaceFirst(url('/'), '', $productImagePath);
            }

            $items[] = [
                'product_image' => $productImagePath,
                'product_name'  => $order_item['product_name'],
                'price'         => number_format((float) $order_item['price'], 2, '.', ''),
                'quantity'      => (int) $order_item['qty'], // Renamed from 'qty' to 'quantity'
            ];
        }

        $orderItems = json_encode(['items' => $items], JSON_UNESCAPED_SLASHES);

        // Place order  
        $order = new StoreOrder;
        $order->user_id = $storeDetails->user_id;
        $order->store_id = $storeDetails->card_id;
        $order->order_number = $order_number;
        $order->order_item = $orderItems;
        $order->delivery_method = Str::lower($request->delivery_method);
        $order->delivery_details = $deliveryDetails;
        $order->payment_method = "cash";
        $order->order_total = $request->total_price ?? 0;
        $order->invoice_prefix = $invoice_prefix;
        $order->invoice_number = $orderNumber;
        $order->invoice_details = "";
        $order->order_notes = $request->delivery_note ?? "";
        $order->save();

        // Return success message
        return response()->json([
            'status' => 'success',
            'message' => __('Order placed successfully.')
        ]);
    }
}
