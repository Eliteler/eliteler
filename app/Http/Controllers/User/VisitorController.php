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

namespace App\Http\Controllers\User;

use App\Services\Analytics\AnalyticsQueryService;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class VisitorController extends Controller
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

    // Analytics page: charts and tables for one card or store
    public function index(Request $request, AnalyticsQueryService $analytics, $id)
    {
        $card = $analytics->cardFor(Auth::user()->user_id, $id);
        if (!$card) {
            abort(404);
        }

        $back = $card->card_type == 'store' ? 'user.stores' : 'user.cards';
        if (!plan_has_analytics()) {
            return redirect()->route($back)->with('failed', trans('Visitor analytics is not included in your plan. Please upgrade your plan.'));
        }

        $settings = Setting::where('status', 1)->first();
        $summary = $analytics->summary($card->card_id, (string) $request->query('period'));

        return view('user.pages.cards.visitors', compact('settings', 'card', 'summary', 'back'));
    }

    // Recent visits table (DataTables)
    public function visitorsData(Request $request, AnalyticsQueryService $analytics, $id)
    {
        $card = $analytics->cardFor(Auth::user()->user_id, $id);
        if (!$card || !plan_has_analytics()) {
            abort(404);
        }

        $query = $analytics->visits($card->card_id)
            ->select(['id', 'created_at', 'ip_address', 'country', 'country_code', 'city', 'browser', 'platform', 'device_type', 'source', 'referrer_domain', 'language'])
            ->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return '<span title="' . e(formatDateForUser($row->created_at)) . '">' . e($row->created_at->diffForHumans()) . '</span>';
            })
            ->addColumn('location', function ($row) {
                $flag = $row->country_code ? country_flag($row->country_code) . ' ' : '';
                $place = $row->city !== 'Unknown' ? $row->city . ', ' . $row->country : $row->country;

                return $flag . e(__($place));
            })
            ->editColumn('browser', fn ($row) => e(__($row->browser)))
            ->editColumn('platform', fn ($row) => e(__(str_replace('"', '', $row->platform))))
            ->editColumn('device_type', fn ($row) => e(__($row->device_type)))
            ->editColumn('source', function ($row) {
                $label = e(__($row->source));

                return $row->referrer_domain ? $label . '<div class="text-muted small">' . e($row->referrer_domain) . '</div>' : $label;
            })
            ->filterColumn('location', function ($query, $keyword) {
                $query->where(fn ($q) => $q->where('country', 'like', "%{$keyword}%")->orWhere('city', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['created_at', 'location', 'source'])
            ->make(true);
    }
}
