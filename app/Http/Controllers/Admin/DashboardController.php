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

namespace App\Http\Controllers\Admin;

use App\User;
use App\Currency;
use App\Transaction;
use App\BusinessCard;
use App\NfcCardDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\NfcCardOrderTransaction;
use App\Classes\AvailableVersion;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;

class DashboardController extends Controller
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
    public function index()
    {
        // Config & settings
        $settings = GoBizCommonService::settings();
        $config   = GoBizCommonService::config();
        $currency = Currency::where('iso_code', $config['1']->config_value)->first();

        // Dates
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        /*
    |--------------------------------------------------------------------------
    | Users summary
    |--------------------------------------------------------------------------
    */
        $usersStats = User::where('role_id', 2)
            ->where('status', 1)
            ->selectRaw('COUNT(*) as total, SUM(created_at >= ?) as today', [$today])
            ->first();

        $overall_users = $usersStats->total ?? 0;
        $today_users   = $usersStats->today ?? 0;

        /*
        |--------------------------------------------------------------------------
        Current week sales
        |--------------------------------------------------------------------------
        */
        $currentWeekSalesRaw = Transaction::where('payment_status', 'Success')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('WEEKDAY(created_at) as day, SUM(transaction_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // WEEKDAY() returns:
        // 0 = Monday
        // 1 = Tuesday
        // ...
        // 6 = Sunday

        $currentWeekSales = [];
        for ($i = 0; $i < 7; $i++) {
            $currentWeekSales[$i] = $currentWeekSalesRaw[$i] ?? 0;
        }

        /*
    |--------------------------------------------------------------------------
    | Income summary (Plans + NFC)
    |--------------------------------------------------------------------------
    */
        $thisMonthIncome = Transaction::where('payment_status', 'Success')
            ->where('payment_gateway_name', '!=', 'TRIAL')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('transaction_amount');

        $today_income = Transaction::where('payment_status', 'Success')
            ->where('payment_gateway_name', '!=', 'TRIAL')
            ->whereDate('created_at', $today)
            ->sum('transaction_amount');

        // NFC income
        $thisMonthIncome += NfcCardOrderTransaction::where('payment_status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $today_income += NfcCardOrderTransaction::where('payment_status', 'success')
            ->whereDate('created_at', $today)
            ->sum('amount');

        /*
    |--------------------------------------------------------------------------
    | Cards summary
    |--------------------------------------------------------------------------
    */
        $cardCounts = BusinessCard::where('card_status', '!=', 'deleted')
            ->where('status', 1)
            ->selectRaw('card_type, COUNT(*) as total')
            ->groupBy('card_type')
            ->pluck('total', 'card_type')
            ->toArray();

        $totalvCards = $cardCounts['vcard'] ?? 0;
        $totalStores = $cardCounts['store'] ?? 0;

        /*
    |--------------------------------------------------------------------------
    | Total earnings (used only for KPI display)
    |--------------------------------------------------------------------------
    */
        $totalEarnings = Transaction::where('payment_status', 'Success')
            ->sum('transaction_amount');

        /*
    |--------------------------------------------------------------------------
    | Version & support check
    |--------------------------------------------------------------------------
    */
        $resp_data = (new AvailableVersion)->availableVersion();

        if ($resp_data && $resp_data['status'] && $resp_data['update']) {
            session()->flash(
                'message',
                '<a href="' . route('admin.check') . '" class="text-white">
                A new version is available!
                <span style="position:absolute;right:7.5vh;">Install</span>
            </a>'
            );
        }

        if ($resp_data && isset($resp_data['support_remaining_days']) && $resp_data['support_remaining_days'] <= 0) {
            session()->flash(
                'support_status_message',
                '<a href="https://store.nativecode.in/checkout/buy/0f1f87da-5adc-443d-947f-17db72d9f3a2" target="_blank" class="text-white">
                Your support plan has ended!
                <span style="position:absolute;right:7.5vh;">Renew</span>
            </a>'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | NFC low stock alert
    |--------------------------------------------------------------------------
    */
        if (
            NfcCardDesign::where('available_stocks', '<', 10)
            ->where('status', 1)
            ->exists()
        ) {
            session()->flash(
                'stock_message',
                '<a href="' . route('admin.designs') . '" class="text-white">
                Some NFC card designs have stock below 10
                <span class="ms-lg-2">Manage</span>
            </a>'
            );
        }

        return view('admin.dashboard', compact(
            'settings',
            'currency',
            'overall_users',
            'today_users',
            'currentWeekSales',
            'thisMonthIncome',
            'today_income',
            'totalEarnings',
            'totalvCards',
            'totalStores'
        ));
    }

    // Get date range
    private function getDateRange(Request $request)
    {
        if ($request->type === 'last_week') {
            $start = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
            $end   = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
        } elseif ($request->type === 'current_week') {
            $start = Carbon::now()->startOfWeek()->startOfDay();
            $end   = Carbon::now()->endOfWeek()->endOfDay();
        } else {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->endOfDay();
        }

        return [$start, $end];
    }

    // Sales filter
    public function salesFilter(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $sales = Transaction::where('payment_status', 'Success')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(transaction_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $sales->pluck('date')->map(
                fn($d) =>
                Carbon::parse($d)->format('d M')
            )->toArray(),
            'data' => $sales->pluck('total')->toArray(),
        ]);
    }

    // Users filter
    public function usersFilter(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $users = User::where('role_id', 2)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $users->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'data' => $users->pluck('total')->toArray(),
        ]);
    }

    // Overview filter
    public function overviewFilter(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        // Earnings
        $earnings = Transaction::where('payment_status', 'Success')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(transaction_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Cards
        $cards = BusinessCard::where('card_status', '!=', 'deleted')
            ->where('status', 1)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, card_type, COUNT(*) as total')
            ->groupBy('date', 'card_type')
            ->orderBy('date')
            ->get();

        $labels = $earnings->pluck('date')->map(
            fn($d) =>
            Carbon::parse($d)->format('d M')
        )->toArray();

        $earningsData = $earnings->pluck('total')->toArray();

        $vcards = [];
        $stores = [];

        foreach ($labels as $i => $label) {
            $date = Carbon::createFromFormat('d M', $label)->format('Y-m-d');
            $vcards[$i] = $cards->where('date', $date)->where('card_type', 'vcard')->sum('total');
            $stores[$i] = $cards->where('date', $date)->where('card_type', 'store')->sum('total');
        }

        return response()->json([
            'labels' => $labels,
            'earnings' => $earningsData,
            'vcards' => array_values($vcards),
            'stores' => array_values($stores),
            'total_earnings' => formatCurrency(array_sum($earningsData)),
            'total_vcards' => array_sum($vcards),
            'total_stores' => array_sum($stores),
        ]);
    }
}
