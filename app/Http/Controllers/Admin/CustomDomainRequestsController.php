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

use App\CustomDomainRequest;
use App\Http\Controllers\Controller;
use App\Services\Admin\CustomDomainService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class CustomDomainRequestsController extends Controller
{
    private const TABS = [
        'all'      => null,
        'pending'  => CustomDomainService::PENDING,
        'approved' => CustomDomainService::APPROVED,
        'rejected' => CustomDomainService::REJECTED,
    ];

    public function __construct(private CustomDomainService $domains)
    {
        $this->middleware('auth');
    }

    // Custom domain requests
    public function customDomainRequests(Request $request, $status)
    {
        if (! array_key_exists($status, self::TABS)) {
            return redirect()->route('admin.custom.domain.requests', ['status' => 'all']);
        }

        $search = trim((string) $request->query('search', ''));

        $base = fn () => CustomDomainRequest::query()
            ->leftJoin('business_cards', 'business_cards.card_id', '=', 'custom_domain_requests.card_id')
            ->leftJoin('users', 'users.user_id', '=', 'custom_domain_requests.user_id')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('custom_domain_requests.current_domain', 'like', "%{$search}%")
                    ->orWhere('custom_domain_requests.previous_domain', 'like', "%{$search}%")
                    ->orWhere('business_cards.title', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }));

        $requests = $base()
            ->when(self::TABS[$status] !== null, fn ($q) => $q->where('custom_domain_requests.transfer_status', self::TABS[$status]))
            ->select([
                'custom_domain_requests.custom_domain_request_id', 'custom_domain_requests.previous_domain',
                'custom_domain_requests.current_domain', 'custom_domain_requests.transfer_status',
                'custom_domain_requests.created_at', 'custom_domain_requests.updated_at',
                'business_cards.title as card_title', 'business_cards.card_url', 'business_cards.card_type',
                'business_cards.custom_domain as live_domain',
                'users.user_id as owner_id', 'users.name as owner_name', 'users.email as owner_email',
            ])
            ->orderByRaw('custom_domain_requests.transfer_status = 0 DESC')
            ->orderByDesc('custom_domain_requests.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($row) => [
                'id'       => $row->custom_domain_request_id,
                'domain'   => $row->current_domain,
                'previous' => in_array($row->previous_domain, [null, '', '-'], true) ? null : $row->previous_domain,
                'status'   => array_search((int) $row->transfer_status, self::TABS, true) ?: 'pending',
                'live'     => $row->live_domain && $row->live_domain === $row->current_domain,
                'card'     => $row->card_url ? [
                    'title' => $row->card_title,
                    'url'   => url($row->card_url),
                    'type'  => $row->card_type === 'store' ? 'store' : 'vcard',
                ] : null,
                'owner'    => $row->owner_id ? ['id' => $row->owner_id, 'name' => $row->owner_name, 'email' => $row->owner_email] : null,
                'created'  => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'updated'  => $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null,
            ]);

        $counts = $base()
            ->selectRaw('COUNT(*) as `all`')
            ->selectRaw('SUM(custom_domain_requests.transfer_status = 0) as pending')
            ->selectRaw('SUM(custom_domain_requests.transfer_status = 1) as approved')
            ->selectRaw('SUM(custom_domain_requests.transfer_status = -1) as rejected')
            ->first();

        return Inertia::render('custom-domains/index', [
            'requests' => $requests,
            'tab'      => $status,
            'search'   => $search,
            'counts'   => collect(array_keys(self::TABS))->mapWithKeys(fn ($tab) => [$tab => (int) $counts->{$tab}]),
        ]);
    }

    // Back to pending
    public function processCustomDomainRequests(Request $request)
    {
        return $this->update($request, CustomDomainService::PENDING, __('Request moved back to pending.'));
    }

    // Approve
    public function approvedCustomDomainRequests(Request $request)
    {
        return $this->update($request, CustomDomainService::APPROVED, __('Domain approved. The card is now served on it.'));
    }

    // Reject
    public function rejectedCustomDomainRequests(Request $request)
    {
        return $this->update($request, CustomDomainService::REJECTED, __('Domain request rejected.'));
    }

    private function update(Request $request, int $status, string $message)
    {
        $data = $request->validate(['id' => 'required|string']);

        $domainRequest = CustomDomainRequest::where('custom_domain_request_id', $data['id'])->first();

        if (! $domainRequest) {
            return back()->with('failed', __('Not Found!'));
        }

        $emailed = $this->domains->setStatus($domainRequest, $status);

        return $emailed
            ? back()->with('success', $message)
            : back()->with('failed', $message . ' ' . __('The email to the customer could not be sent.'));
    }
}
