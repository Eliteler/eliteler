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

namespace App\Http\Controllers\User;

use App\Currency;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\NfcCardOrder;
use App\Plan;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ManageOrderNfcCardController extends Controller
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

    // Manage NFC Card orders
    public function index(Request $request)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Currency symbol
        $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
        $symbol = $currencies[$config[1]->config_value] ?? '';

        // Check if the user has a plan
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);

        if ($active_plan != null) {
                if ($request->ajax()) {
                    $orders = NfcCardOrder::join('nfc_card_order_transactions', 'nfc_card_orders.nfc_card_order_id', '=', 'nfc_card_order_transactions.nfc_card_order_id')
                        ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
                    ->select(
                        'nfc_card_orders.nfc_card_order_id',
                        'nfc_card_orders.nfc_card_id',
                        'nfc_card_orders.nfc_card_logo',
                        'nfc_card_orders.order_details',
                        'nfc_card_orders.order_status',
                        'nfc_card_orders.status',
                        'nfc_card_orders.created_at',
                        'nfc_card_orders.updated_at',
                        'nfc_card_order_transactions.payment_status',
                        'nfc_card_order_transactions.amount',
                        'nfc_card_designs.nfc_card_name',
                        'nfc_card_designs.nfc_card_price'
                    )
                        ->where('nfc_card_orders.user_id', Auth::user()->id)
                    ->orderBy('nfc_card_order_transactions.created_at', 'desc');

                    return DataTables::of($orders)
                        ->addIndexColumn()
                    // Resolved: filterColumn declarations explicitly map relationship-based searches to prevent SQL clause errors
                    ->filterColumn('nfc_card_name', function ($query, $keyword) {
                        $query->where('nfc_card_designs.nfc_card_name', 'like', "%{$keyword}%");
                    })
                    ->filterColumn('nfc_card_price', function ($query, $keyword) {
                        $query->where('nfc_card_designs.nfc_card_price', 'like', "%{$keyword}%");
                    })
                    ->filterColumn('payment_status', function ($query, $keyword) {
                        $query->where('nfc_card_order_transactions.payment_status', 'like', "%{$keyword}%");
                    })
                    ->filterColumn('delivery_status', function ($query, $keyword) {
                        $query->where('nfc_card_orders.order_status', 'like', "%{$keyword}%");
                    })
                    ->filterColumn('created_at', function ($query, $keyword) {
                        $query->where('nfc_card_orders.created_at', 'like', "%{$keyword}%");
                    })
                        ->editColumn('created_at', function ($order) {
                            return formatDateForUser($order->created_at);
                        })
                        ->editColumn('nfc_card_order_id', function ($order) {
                        return '<a href="' . route('user.order.nfc.card.view', $order->nfc_card_order_id) . '" class="fw-bold text-decoration-none text-dark">' . $order->nfc_card_order_id . '</a>';
                        })
                        ->editColumn('order_details', function ($order) {
                            $orderDetails = json_decode($order->order_details);

                        if ($orderDetails && isset($orderDetails->unique_key) && is_array($orderDetails->unique_key) && count($orderDetails->unique_key)) {
                            $keys = $orderDetails->unique_key;
                            $firstKey = $keys[0];
                            $remainingCount = count($keys) - 1;

                            $html = '
                            <div class="dropdown d-flex align-items-center gap-2">
                                <a href="' . route('user.activate.nfc.card') . '?id=' . $firstKey . '" 
                                class="fw-bold text-break text-decoration-none">
                                    ' . $firstKey . '
                                </a>';

                            if ($remainingCount > 0) {
                                $html .= '
                                    <a href="#" 
                                    class="badge bg-blue-lt text-decoration-none d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                        + ' . $remainingCount . '
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-start p-2" style="min-width:260px;">';

                                foreach ($keys as $index => $key) {
                                    if ($index == 0) {
                                        continue;
                                    }
                                    $html .= '
                                        <a href="' . route('user.activate.nfc.card') . '?id=' . $key . '" 
                                        class="dropdown-item text-break fw-semibold">
                                            ' . $key . '
                                        </a>';
                                }
                                $html .= '
                                    </div>';
                            }
                            $html .= '
                            </div>';

                            return $html;
                            } else {
                                return '-';
                            }
                        })
                        ->editColumn('nfc_card_logo', function ($order) {
                            if (!empty($order->nfc_card_logo)) {
                                $output = '<a href="' . asset($order->nfc_card_logo) . '" class="fw-bold" data-bs-toggle="tooltip" data-bs-placement="top" title="' . trans('Download') . '" download>
                                    <img src="' . asset($order->nfc_card_logo) . '" class="img-fluid rounded border" width="40" height="40" style="object-fit: cover;" alt="NFC Card Attachment">
                                        </a>';

                                if ($order->order_status == 'processing' || $order->order_status == 'hold') {
                                $output .= '<br><a href="' . route('user.upload.nfc.card.logo', $order->nfc_card_order_id) . '" class="fw-bold btn small-btn text-success bg-white mt-1 border">' . __('Re-upload') . '</a>';
                                }
                                return $output;
                            } elseif ($order->order_status == 'printing process begun' || $order->order_status == 'processing' || $order->order_status == 'hold') {
                            return '<a href="' . route('user.upload.nfc.card.logo', $order->nfc_card_order_id) . '" class="fw-bold btn small-btn text-success bg-white border">' . __('Upload') . '</a>';
                            }
                        return '<span class="text-muted">-</span>';
                        })
                        ->editColumn('nfc_card_name', function ($order) {
                        return '<span class="fw-bold text-dark">' . $order->nfc_card_name . '</span>';
                        })
                        ->editColumn('nfc_card_price', function ($order) {
                        return '<span class="fw-bold text-dark">' . formatCurrency($order->amount) . '</span>';
                        })
                        ->editColumn('payment_status', function ($order) {
                            if ($order->payment_status == 'success') {
                            return '<span class="badge bg-green-600 text-green-50">' . __('Paid') . '</span>';
                            } elseif ($order->payment_status == 'failed') {
                            return '<span class="badge bg-red-100 text-red-700">' . __('Failed') . '</span>';
                            } elseif ($order->payment_status == 'pending') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Pending') . '</span>';
                            } elseif ($order->payment_status == 'cancelled') {
                            return '<span class="badge bg-red-100 text-red-700">' . __('Cancelled') . '</span>';
                            }
                        })
                        ->editColumn('delivery_status', function ($order) {
                            if ($order->order_status == 'pending') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Pending') . '</span>';
                            } elseif ($order->order_status == 'processing') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Processing') . '</span>';
                            } elseif ($order->order_status == 'out for delivery') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Out for delivery') . '</span>';
                            } elseif ($order->order_status == 'delivered') {
                            return '<span class="badge bg-green-600 text-green-50">' . __('Delivered') . '</span>';
                            } elseif ($order->order_status == 'cancelled') {
                            return '<span class="badge bg-red-100 text-red-700">' . __('Cancelled') . '</span>';
                            } elseif ($order->order_status == 'hold') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Hold') . '</span>';
                            } elseif ($order->order_status == 'shipped') {
                            return '<span class="badge bg-green-600 text-green-50">' . __('Shipped') . '</span>';
                            } elseif ($order->order_status == 'printing process begun') {
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Printing Process Begun') . '</span>';
                            }
                        })
                        ->editColumn('action', function ($order) {
                        $actionBtn = '<a href="' . route('user.order.nfc.card.view', $order->nfc_card_order_id) . '" class="dropdown-item d-inline-flex align-items-center"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon me-2 icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>' . __('View') . '</a>';
                        return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                            <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                            <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        </svg>
                                    </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                            ' . $actionBtn . '
                                    </div>';
                        })
                    ->rawColumns(['nfc_card_order_id', 'order_details', 'nfc_card_logo', 'nfc_card_name', 'nfc_card_price', 'payment_status', 'delivery_status', 'action'])
                        ->make(true);
                }

            return view('user.pages.manage-orders.index', compact('settings', 'config'));
        } else {
            return redirect()->route('user.plans');
        }
    }

    // View order details
    public function viewOrder($orderId)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
        $symbol = $currencies[$config[1]->config_value] ?? '';

        // Check if the user has a plan
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);

        // Get the order and transaction and user tables (joins)
        $order = NfcCardOrder::join('nfc_card_order_transactions', 'nfc_card_orders.nfc_card_order_id', '=', 'nfc_card_order_transactions.nfc_card_order_id')
            ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
            ->join('users', 'nfc_card_orders.user_id', '=', 'users.id')
            ->select('nfc_card_order_transactions.*', 'users.*', 'nfc_card_orders.order_details', 'nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.nfc_card_id', 'nfc_card_designs.nfc_card_name', 'nfc_card_designs.nfc_card_price', 'nfc_card_orders.delivery_address', 'nfc_card_orders.order_status', 'nfc_card_orders.status', 'nfc_card_orders.created_at', 'nfc_card_orders.updated_at')
            ->where('nfc_card_order_transactions.status', '!=', 2)
            ->where('nfc_card_orders.nfc_card_order_id', $orderId)->where('nfc_card_orders.user_id', Auth::user()->id)
            ->first();

        // Check if the order exists
        if (!$order) {
            return redirect()->route('user.manage.nfc.orders')->with('failed', __('Order not found!'));
        }

            return view('user.pages.manage-orders.view', compact('order', 'settings', 'config', 'symbol'));
    }

    // Upload NFC card logo
    public function uploadNfcCardLogo($nfcId)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Check if the user has a plan
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);

        // Get the order and transaction and user tables (joins)
        $order = NfcCardOrder::join('nfc_card_order_transactions', 'nfc_card_orders.nfc_card_order_id', '=', 'nfc_card_order_transactions.nfc_card_order_id')
            ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
            ->join('users', 'nfc_card_orders.user_id', '=', 'users.id')
            ->select('nfc_card_order_transactions.*', 'users.*', 'nfc_card_orders.order_details', 'nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.nfc_card_id', 'nfc_card_designs.nfc_card_name', 'nfc_card_designs.nfc_card_price', 'nfc_card_orders.delivery_address', 'nfc_card_orders.order_status', 'nfc_card_orders.status', 'nfc_card_orders.created_at', 'nfc_card_orders.updated_at')
            ->where('nfc_card_order_transactions.status', '!=', 2)
            ->where('nfc_card_orders.nfc_card_order_id', $nfcId)->where('nfc_card_orders.user_id', Auth::user()->id)
            ->first();

        // Check if the order exists
        if (!$order) {
            return redirect()->route('user.manage.nfc.orders')->with('failed', __('Order not found!'));
        }

        // Check if the order status is "processing" or "hold"
        if ($order->order_status != 'processing' && $order->order_status != 'hold') {
            return redirect()->route('user.manage.nfc.orders')->with('failed', __('Unable to re-upload NFC card attachment. Order status is not "Processing" or "Holding".'));
        }

            return view('user.pages.manage-orders.upload-nfc-card-logo', compact('order', 'settings', 'config'));
    }

    // Update NFC card logo
    public function updateNfcCardLogo(Request $request, $nfcId)
    {
        // Check if the order exists
        $order = NfcCardOrder::where('nfc_card_order_id', $nfcId)->first();

        // Check if the order exists
        if (!$order) {
            return redirect()->route('user.manage.nfc.orders')->with('failed', __('Order not found!'));
        }

        // Check if the order status is "processing" or "hold"
        if ($order->order_status != 'processing' && $order->order_status != 'hold') {
            return redirect()->route('user.manage.nfc.orders')->with('failed', __('Unable to re-upload NFC card attachment. Order status is not "Processing" or "Holding".'));
        }

        // Upload NFC card logo
        if ($request->hasFile('nfc_card_logo') && $request->file('nfc_card_logo')->isValid()) {
            // Upload NFC card logo and get relative path (e.g., 'storage/nfc-cards/filename.png')
            $nfcCardLogoUrl = StorageHelper::uploadImage($request->file('nfc_card_logo'), 'nfc-cards');

            // Update logo in database
            NfcCardOrder::where('nfc_card_order_id', $nfcId)->update([
                'nfc_card_logo' => $nfcCardLogoUrl,
            ]);
        }

        return redirect()->route('user.manage.nfc.orders')->with('success', __('NFC Card Logo updated successfully!'));
    }
}
