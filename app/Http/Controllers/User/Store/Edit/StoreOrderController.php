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

namespace App\Http\Controllers\User\Store\Edit;

use App\Setting;
use App\Currency;
use App\StoreOrder;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StoreOrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Store orders
    public function orders(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }

        // Check currency
        $currency = json_decode($business_card->description, true)['currency'];

        // Get currency symbol
        $currency = Currency::where('iso_code', $currency)->first();
        $currency = $currency->symbol;

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Queries
            if ($request->ajax()) {
                // Queries
                $storeOrders = StoreOrder::where('store_id', $id)->orderBy('id', 'desc')->get();

                return DataTables::of($storeOrders)
                    ->addIndexColumn()
                    ->editColumn('order_date', function ($storeOrder) {
                        return formatDateForUser($storeOrder->created_at);
                    })
                    ->editColumn('order_id', function ($storeOrder) {
                        return '<a href="#" onclick="viewOrder(`' . $storeOrder->order_number . '`)">' . $storeOrder->order_number . '</a>';
                    })
                    ->editColumn('delivery_method', function ($storeOrder) {
                        return '<span class="badge bg-primary text-white text-capitalize">' . trans($storeOrder->delivery_method) . '</span>';
                    })
                    ->editColumn('delivery_address', function ($storeOrder) {
                        // Delivery address
                        $deliveryDetails = json_decode($storeOrder->delivery_details);

                        // Customer name
                        $customerName = $deliveryDetails->name ?? '';
                        $deliveryAddress = $deliveryDetails->address ?? '';
                        $customerPhone = $deliveryDetails->mobile ?? '';
                        $deliveryNotes = $deliveryDetails->notes ?? '';

                        $notesHtml = $deliveryNotes ? '<div class="text-secondary">' . e($deliveryNotes) . '</div>' : '';

                        return '<div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">' . e($customerName) . '</div>
                                        <div class="text-secondary"><a href="https://www.google.com/maps/dir/?api=1&destination=' . urlencode($deliveryAddress) . '" target="_blank">' . e($deliveryAddress) . '</a></div>
                                        <div class="text-secondary"><a href="tel:' . e($customerPhone) . '" target="_blank">' . e($customerPhone) . '</a></div>
                                        ' . $notesHtml . '
                                    </div>
                                </div>';
                    })
                    ->editColumn('order_status', function ($storeOrder) {
                        // Order status
                        $orderStatus = $storeOrder->order_status;

                        // Order status
                        if ($orderStatus == 'processing') {
                            $orderStatus = '<span class="badge bg-success text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'shipped') {
                            $orderStatus = '<span class="badge bg-success text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'out for delivery') {
                            $orderStatus = '<span class="badge bg-warning text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'delivered') {
                            $orderStatus = '<span class="badge bg-success text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'cancelled') {
                            $orderStatus = '<span class="badge bg-danger text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'failed') {
                            $orderStatus = '<span class="badge bg-danger text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'hold') {
                            $orderStatus = '<span class="badge bg-warning text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'pending') {
                            $orderStatus = '<span class="badge bg-primary text-white text-capitalize">' . trans($orderStatus) . '</span>';
                        }

                        return $orderStatus;
                    })
                    ->addColumn('order_total', function ($storeOrder) use ($currency) {
                        return '<span class="">' . $currency . '' . $storeOrder->order_total . '</span>';
                    })
                    ->addColumn('payment_status', function ($storeOrder) {
                        // Payment status
                        $paymentStatus = $storeOrder->payment_status;

                        // Payment status
                        if ($paymentStatus == 'processing') {
                            $paymentStatus = '<span class="badge bg-warning me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'failed') {
                            $paymentStatus = '<span class="badge bg-danger me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'pending') {
                            $paymentStatus = '<span class="badge bg-primary me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'paid') {
                            $paymentStatus = '<span class="badge bg-success me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'refunded') {
                            $paymentStatus = '<span class="badge bg-danger me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'partially_refunded') {
                            $paymentStatus = '<span class="badge bg-danger me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'cancelled') {
                            $paymentStatus = '<span class="badge bg-warning me-1 text-capitalize text-white">' . trans($paymentStatus) . '</span>';
                        }

                        return $paymentStatus;
                    })
                    ->addColumn('actions', function ($storeOrder) {
                        $actionBtn = '<a class="dropdown-item" onclick="viewOrder(`' . $storeOrder->order_number . '`)">' . __('View') . '</a>';

                        $actionBtn .= '<a class="dropdown-item" onclick="viewInvoice(`' . $storeOrder->order_number . '`)">' . __('Invoice') . '</a>';

                        // Update status modal
                        $actionBtn .= '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#updateOrderStatusModal" data-order-id="' . $storeOrder->order_number . '">' . __('Update Status') . '</a>';

                        // Mark as paid modal
                        $actionBtn .= '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#markAsPaidModal" data-order-id="' . $storeOrder->order_number . '">' . __('Update Payment Status') . '</a>';

                        return '<a class="link-secondary show" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            ' . $actionBtn . '
                        </div>';
                    })
                    ->rawColumns(['order_date', 'order_id', 'delivery_method', 'delivery_address', 'order_status', 'order_total', 'payment_status', 'actions'])
                    ->make(true);
            }
        }

        $config   = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.edit-store.store-order.orders', compact('business_card', 'config', 'settings', 'currency'));
    }

    // View order
    public function viewOrder(Request $request, $id)
    {
        // Queries
        $storeOrder = StoreOrder::where('order_number', $id)->first();

        return response()->json(['success' => true, 'data' => $storeOrder]);
    }

    // View invoice
    public function viewInvoice(Request $request, $id)
    {
        // Queries
        $storeOrder = StoreOrder::where('order_number', $id)->first();

        return response()->json(['success' => true, 'data' => $storeOrder]);
    }

    // Update order
    public function updateOrder(Request $request, $orderId)
    {
        // Check order id
        if (empty($orderId)) {
            return response()->json(['success' => false, 'message' => 'Order ID is required.']);
        }

        // Update order
        StoreOrder::where('order_number', $orderId)->update([
            'order_status' => $request->status ?? '',
        ]);

        // Get order
        $order = StoreOrder::where('order_number', $orderId)->first();

        // WhatsApp details
        $whatsappNumber = '';
        if (!empty($order->delivery_details)) {
            $details = json_decode($order->delivery_details);
            $whatsappNumber = $details->mobile ?? '';
        }

        // Prepare message
        switch ($request->status) {
            case 'pending':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} is now *Pending*.";

            case 'processing':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} is currently *Processing*.";
                break;

            case 'shipped':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} has been *Shipped*.";
                break;

            case 'out for delivery':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} is *Out for Delivery*.";
                break;

            case 'delivered':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} has been *Delivered*. Enjoy!";
                break;

            case 'cancelled':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} has been *Cancelled*.";
                break;

            case 'failed':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} has *Failed*. Please contact support.";
                break;

            case 'hold':
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} is on *Hold*.";
                break;

            default:
                $viewOrderUrl = route('user.store.orders', ['id' => $order->store_id]);
                $message = "Your order ID: {$orderId} has been updated to *{$request->status}*.";
                break;
        }

        // WhatsApp URL with emoji-safe encoding
        $encodedMessage = rawurlencode($message);
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";

        // Redirect to WhatsApp if requested
        if (!empty($whatsappNumber) && $request->has('redirect') && $request->redirect == 1) {
            return redirect()->away($whatsappUrl);
        }

        // Otherwise return JSON response
        return response()->json([
            'success' => true,
            'data' => $order,
            'whatsapp_url' => $whatsappUrl,
            'message_preview' => $message, // debug preview
        ]);
    }

    // Mark as paid
    public function markAsPaid(Request $request, $orderId)
    {
        // Update order
        StoreOrder::where('order_number', $orderId)->update([
            'payment_status' => $request->payment_status ?? '',
        ]);

        return response()->json(['success' => true, 'data' => StoreOrder::where('order_number', $orderId)->first()]);
    }
}
