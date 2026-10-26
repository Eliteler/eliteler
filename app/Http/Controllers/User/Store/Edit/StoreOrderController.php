<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
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
     * Display a listing of storefront orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function orders(Request $request, $id)
    {
        // Fetch business card and authenticate matching ownership parameters
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }

        // Decode and fetch configured ISO code details cleanly
        $descriptionData = json_decode($business_card->description, true);
        $currencyCode = $descriptionData['currency'] ?? 'USD';

        // Retrieve corresponding currency display symbols
        $currencyRecord = Currency::where('iso_code', $currencyCode)->first();

        // Aligned to native $currency variable naming to prevent Blade rendering issues
        $currency = $currencyRecord ? $currencyRecord->symbol : '$';

            if ($request->ajax()) {
            // Retrieve orders matching active store parameter filters
                $storeOrders = StoreOrder::where('store_id', $id)->orderBy('id', 'desc')->get();

                return DataTables::of($storeOrders)
                    ->addIndexColumn()
                    ->editColumn('order_date', function ($storeOrder) {
                    return '<span class="fw-bold text-secondary">' .
                        formatDateForUser($storeOrder->created_at) .
                        '</span>';
                    })
                    ->editColumn('order_id', function ($storeOrder) {
                    return '<a href="#" class="fw-bold text-decoration-none text-dark hover-primary" onclick="viewOrder(`' . $storeOrder->order_number . '`)">' .
                        e($storeOrder->order_number) .
                        '</a>';
                    })
                    ->editColumn('delivery_method', function ($storeOrder) {
                    return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 text-capitalize">' .
                        e(trans($storeOrder->delivery_method)) .
                        '</span>';
                    })
                    ->editColumn('delivery_address', function ($storeOrder) {
                        $deliveryDetails = json_decode($storeOrder->delivery_details);

                        $customerName = $deliveryDetails->name ?? '';
                        $deliveryAddress = $deliveryDetails->address ?? '';
                        $customerPhone = $deliveryDetails->mobile ?? '';
                        $deliveryNotes = $deliveryDetails->notes ?? '';

                    $notesHtml = $deliveryNotes ? '<div class="text-secondary small mt-1">' . e($deliveryNotes) . '</div>' : '';

                        return '<div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                <div class="fw-bold text-dark">' . e($customerName) . '</div>
                                <div class="text-secondary small mt-1">
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=' . urlencode($deliveryAddress) . '" target="_blank" class="text-decoration-none hover-primary">' .
                        e($deliveryAddress) .
                        '</a>
                                </div>
                                <div class="text-secondary small mt-1">
                                    <a href="tel:' . e($customerPhone) . '" class="text-decoration-none hover-primary fw-semibold">' .
                        e($customerPhone) .
                        '</a>
                                </div>' .
                        $notesHtml . '
                                    </div>
                                </div>';
                    })
                    ->editColumn('order_status', function ($storeOrder) {
                        $orderStatus = $storeOrder->order_status;

                    // Modern soft-muted badge palette conversion matching the guidelines
                        if ($orderStatus == 'processing') {
                        return '<span class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'shipped') {
                        return '<span class="badge bg-blue-100 text-blue-800 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'out for delivery') {
                        return '<span class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'delivered') {
                        return '<span class="badge bg-green-600 text-green-50 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'cancelled') {
                        return '<span class="badge bg-red-100 text-red-700 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'failed') {
                        return '<span class="badge bg-red-100 text-red-700 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        } else if ($orderStatus == 'hold') {
                        return '<span class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                    } else {
                        return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 text-capitalize">' . trans($orderStatus) . '</span>';
                        }
                    })
                    ->addColumn('order_total', function ($storeOrder) use ($currency) {
                    return '<span class="fw-bold text-dark">' .
                        e($currency) . number_format($storeOrder->order_total, 2) .
                        '</span>';
                    })
                    ->addColumn('payment_status', function ($storeOrder) {
                        $paymentStatus = $storeOrder->payment_status;

                    // Modern soft-muted badge palette conversion matching the guidelines
                        if ($paymentStatus == 'processing') {
                        return '<span class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'failed') {
                        return '<span class="badge bg-red-100 text-red-700 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'pending') {
                        return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'paid') {
                        return '<span class="badge bg-green-600 text-green-50 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'refunded') {
                        return '<span class="badge bg-red-100 text-red-700 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        } else if ($paymentStatus == 'partially_refunded') {
                        return '<span class="badge bg-red-100 text-red-700 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                    } else {
                        return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 text-capitalize">' . trans($paymentStatus) . '</span>';
                        }
                    })
                    ->addColumn('actions', function ($storeOrder) {
                        $actionBtn = '<a class="dropdown-item" onclick="viewOrder(`' . $storeOrder->order_number . '`)">' . __('View') . '</a>';
                        $actionBtn .= '<a class="dropdown-item" onclick="viewInvoice(`' . $storeOrder->order_number . '`)">' . __('Invoice') . '</a>';
                        $actionBtn .= '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#updateOrderStatusModal" data-order-id="' . $storeOrder->order_number . '">' . __('Update Status') . '</a>';
                        $actionBtn .= '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#markAsPaidModal" data-order-id="' . $storeOrder->order_number . '">' . __('Update Payment Status') . '</a>';

                    return '<a class="btn-action text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="5" cy="12" r="1" />
                        <circle cx="12" cy="12" r="1" />
                        <circle cx="19" cy="12" r="1" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            ' . $actionBtn . '
                        </div>';
                    })
                    ->rawColumns(['order_date', 'order_id', 'delivery_method', 'delivery_address', 'order_status', 'order_total', 'payment_status', 'actions'])
                    ->make(true);
            }

        $config   = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.edit-store.store-order.orders', compact('business_card', 'config', 'settings', 'currency'));
    }

    /**
     * View detailed order parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewOrder(Request $request, $id)
    {
        $storeOrder = StoreOrder::where('order_number', $id)->first();
        return response()->json(['success' => true, 'data' => $storeOrder]);
    }

    /**
     * View detailed invoice parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewInvoice(Request $request, $id)
    {
        $storeOrder = StoreOrder::where('order_number', $id)->first();
        return response()->json(['success' => true, 'data' => $storeOrder]);
    }

    /**
     * Update dynamic status for orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateOrder(Request $request, $orderId)
    {
        if (empty($orderId)) {
            return response()->json(['success' => false, 'message' => 'Order ID is required.']);
        }

        StoreOrder::where('order_number', $orderId)->update([
            'order_status' => $request->status ?? '',
        ]);

        $order = StoreOrder::where('order_number', $orderId)->first();

        $whatsappNumber = '';
        if (!empty($order->delivery_details)) {
            $details = json_decode($order->delivery_details);
            $whatsappNumber = $details->mobile ?? '';
        }

        switch ($request->status) {
            case 'pending':
                $message = "Your order ID: {$orderId} is now *Pending*.";
                break;

            case 'processing':
                $message = "Your order ID: {$orderId} is currently *Processing*.";
                break;

            case 'shipped':
                $message = "Your order ID: {$orderId} has been *Shipped*.";
                break;

            case 'out for delivery':
                $message = "Your order ID: {$orderId} is *Out for Delivery*.";
                break;

            case 'delivered':
                $message = "Your order ID: {$orderId} has been *Delivered*. Enjoy!";
                break;

            case 'cancelled':
                $message = "Your order ID: {$orderId} has been *Cancelled*.";
                break;

            case 'failed':
                $message = "Your order ID: {$orderId} has *Failed*. Please contact support.";
                break;

            case 'hold':
                $message = "Your order ID: {$orderId} is on *Hold*.";
                break;

            default:
                $message = "Your order ID: {$orderId} has been updated to *{$request->status}*.";
                break;
        }

        $encodedMessage = rawurlencode($message);
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";

        if (!empty($whatsappNumber) && $request->has('redirect') && $request->redirect == 1) {
            return redirect()->away($whatsappUrl);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'whatsapp_url' => $whatsappUrl,
            'message_preview' => $message,
        ]);
    }

    /**
     * Update active payment status parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(Request $request, $orderId)
    {
        StoreOrder::where('order_number', $orderId)->update([
            'payment_status' => $request->payment_status ?? '',
        ]);

        return response()->json([
            'success' => true,
            'data' => StoreOrder::where('order_number', $orderId)->first()
        ]);
    }
}
