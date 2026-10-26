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

use App\Http\Controllers\Controller;
use App\ReferralWithdrawRequest;
use App\Services\Admin\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class ReferralWithdrawalRequestController extends Controller
{
    private const TABS = [
        'pending'     => ReferralService::PENDING,
        'accepted'    => ReferralService::ACCEPTED,
        'transferred' => ReferralService::TRANSFERRED,
        'rejected'    => ReferralService::REJECTED,
    ];

    public function __construct(private ReferralService $referrals)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(\App\Support\Admin\AdminSharedProps::features()['referral'], 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tab    = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'pending';
        $search = trim((string) $request->query('search', ''));

        $base = fn() => ReferralWithdrawRequest::where('referral_withdraw_requests.status', 1)
            ->leftJoin('users', 'users.user_id', '=', 'referral_withdraw_requests.user_id')
            ->when($search !== '', fn($q) => $q->where(fn($q) => $q
                    ->where('users.name', 'like', "%{$search}%")->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('referral_withdraw_requests.referral_withdraw_request_id', 'like', "%{$search}%")));

        $page = $base()
            ->when(self::TABS[$tab] !== null, fn($q) => $q->where('referral_withdraw_requests.payment_status', self::TABS[$tab]))
            ->select('referral_withdraw_requests.*', 'users.name', 'users.email')
            ->orderByDesc('referral_withdraw_requests.id')
            ->paginate(20)
            ->withQueryString();

        $userIds = $page->getCollection()->pluck('user_id')->unique()->values()->all();
        $earned  = $this->referrals->earnings($userIds);
        $paid    = $this->referrals->paidOut($userIds);

        $page->through(fn($row) => [
            'id'       => $row->referral_withdraw_request_id,
            'amount'   => (float) $row->amount,
            'details'  => ReferralService::payoutDetails($row->bank_details),
            'notes'    => $row->notes,
            'status'   => array_search((int) $row->payment_status, self::TABS, true) ?: 'pending',
            'date'     => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
            'customer' => $row->name ? ['id' => $row->user_id, 'name' => $row->name, 'email' => $row->email] : null,
            'balance'  => round(($earned[$row->user_id] ?? 0) - ($paid[$row->user_id] ?? 0), 2),
        ]);

        $counts = $base()->selectRaw('SUM(payment_status = 0) as pending, SUM(payment_status = 1) as accepted, SUM(payment_status = 2) as transferred, SUM(payment_status = -1) as rejected')->first();

        return Inertia::render('referrals/withdrawals', [
            'requests' => $page,
            'tab'      => $tab,
            'search'   => $search,
            'counts'   => collect(array_keys(self::TABS))->mapWithKeys(fn($k) => [$k => (int) $counts->{$k}]),
        ]);
    }

    // Accept / reject
    public function updateWithdrawalRequestStatus(Request $request)
    {
        $data       = $request->validate(['requestId' => 'required|string', 'status' => 'required|in:accepted,rejected']);
        $withdrawal = ReferralWithdrawRequest::where('referral_withdraw_request_id', $data['requestId'])->first();

        if (! $withdrawal) {
            return back()->with('failed', __('Not Found!'));
        }

        $this->referrals->setStatus($withdrawal, $data['status'] === 'accepted' ? ReferralService::ACCEPTED : ReferralService::REJECTED);

        return back()->with('success', $data['status'] === 'accepted' ? __('Request accepted. Send the money, then mark it as paid.') : __('Request rejected.'));
    }

    // Mark as paid out
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'request_id'              => 'required|string',
            'transfer_transaction_id' => 'required|string|max:191',
            'transfer_notes'          => 'nullable|string|max:1000',
        ], [], ['transfer_transaction_id' => __('transfer reference'), 'transfer_notes' => __('note')]);

        $withdrawal = ReferralWithdrawRequest::where('referral_withdraw_request_id', $data['request_id'])->first();

        if (! $withdrawal) {
            return back()->with('failed', __('Not Found!'));
        }

        $this->referrals->setStatus($withdrawal, ReferralService::TRANSFERRED, $data['transfer_transaction_id'] ?? null, $data['transfer_notes'] ?? null);

        return back()->with('success', __('Payout recorded.'));
    }
}
