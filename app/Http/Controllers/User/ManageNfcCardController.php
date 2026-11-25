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

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\NfcCardKey;
use App\Services\SmartProducts\ChipLinks;
use App\Setting;
use App\Support\Plugins\UserPluginPage;
use App\Support\SmartProducts\SmartProductCatalog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/**
 * "My Smart Products": the customer's NFC keys and where each one opens
 * (business card, store or link). Logic lives in App\Services\SmartProducts\ChipLinks.
 */
class ManageNfcCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (! $request->ajax() && UserPluginPage::reactPanel()) {
            session()->reflash();

            return redirect()->route('user.dashboard.nfc-cards');
        }

        if ($guard = $this->guard()) {
            return $guard;
        }

        $user = Auth::user();

        if ($request->ajax()) {
            $orders = ChipLinks::orderKeys($user);
            $keys   = ChipLinks::ownedQuery($user, array_keys($orders))
                ->select('nfc_card_keys.*')
                ->orderByDesc('nfc_card_keys.id');

            $titles = BusinessCard::where('user_id', $user->user_id)->pluck('title', 'card_id')->all();
            $types  = BusinessCard::where('user_id', $user->user_id)->pluck('card_type', 'card_id')->all();

            return DataTables::of($keys)
                ->addIndexColumn()
                ->editColumn('created_at', fn ($key) => '<span class="text-secondary">' . formatDateForUser($key->created_at) . '</span>')
                ->addColumn('product', function ($key) use ($orders) {
                    $order = $orders[$key->unqiue_key] ?? null;

                    $name = $order
                        ? '<a href="' . route('user.order.nfc.card.view', $order['order']) . '" class="fw-bold text-dark text-decoration-none">' . e(__($order['product'])) . '</a>'
                        : '<span class="fw-bold">' . e(__('Activated with a code')) . '</span>';

                    $label = $key->label ? '<div class="text-secondary small">' . e($key->label) . '</div>' : '';

                    return $name . $label . '<div class="text-secondary small font-monospace">' . e(route('read.nfc.card', $key->unqiue_key)) . '</div>';
                })
                ->addColumn('destination', function ($key) use ($titles, $types) {
                    if ($key->link_status !== 'linked') {
                        return '<span class="badge bg-red-100 text-red-700 fw-semibold">' . e(__('Not set')) . '</span>';
                    }

                    $type  = $key->link_type === 'url' ? 'url' : ($types[$key->card_id] ?? $key->link_type ?? 'vcard');
                    $label = e(__(SmartProductCatalog::LINK_TYPES[$type] ?? $type));
                    $text  = e(ChipLinks::describe($key, $titles) ?? '-');
                    $link  = ChipLinks::destination($key);

                    return '<span class="badge bg-green-100 text-green-700 fw-semibold me-1">' . $label . '</span>'
                        . ($link ? '<a href="' . e($link) . '" target="_blank" rel="noopener" class="text-decoration-none text-break">' . $text . '</a>' : $text);
                })
                ->addColumn('scans', fn ($key) => '<span class="fw-bold">' . number_format((int) $key->scan_count) . '</span>'
                    . ($key->last_scanned_at ? '<div class="text-secondary small">' . e(formatDateForUser($key->last_scanned_at)) . '</div>' : ''))
                ->addColumn('action', function ($key) {
                    $set = '<a href="' . route('user.link.nfc.card', $key->nfc_card_key_id) . '" class="dropdown-item">' . e(__('Set destination')) . '</a>';
                    $off = $key->link_status === 'linked'
                        ? '<a href="#" onclick="updateStatus(\'' . e($key->nfc_card_key_id) . '\', \'unlink\'); return false;" class="dropdown-item text-danger">' . e(__('Unlink')) . '</a>'
                        : '';

                    return '<a class="btn-action text-decoration-none" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-dots"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">' . $set . $off . '</div>';
                })
                ->rawColumns(['created_at', 'product', 'destination', 'scans', 'action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $config   = DB::table('config')->get();

        return view('user.pages.manage-nfc-cards.index', compact('settings', 'config'));
    }

    // Choose where a key opens
    public function linkNfcCard(Request $request, $id)
    {
        if ($guard = $this->guard()) {
            return $guard;
        }

        $user       = Auth::user();
        $nfcCardKey = ChipLinks::findOwned($user, $id);

        if (! $nfcCardKey) {
            return redirect()->route('user.manage.nfc.cards')->with('failed', __('NFC Card not found!'));
        }

        $settings = Setting::where('status', 1)->first();
        $config   = DB::table('config')->get();
        $vcards   = ChipLinks::cards($user, 'vcard');
        $stores   = ChipLinks::cards($user, 'store');

        // Old keys: link_type defaults to vcard, the card may really be a store
        $currentType = $nfcCardKey->link_type === 'url'
            ? 'url'
            : (BusinessCard::where('card_id', $nfcCardKey->card_id)->value('card_type') ?? ($nfcCardKey->link_type ?: 'vcard'));

        $linkTypes = SmartProductCatalog::LINK_TYPES;

        return view('user.pages.manage-nfc-cards.link', compact('settings', 'config', 'nfcCardKey', 'vcards', 'stores', 'currentType', 'linkTypes'));
    }

    public function updateCardLink(Request $request)
    {
        $user       = Auth::user();
        $nfcCardKey = ChipLinks::findOwned($user, (string) $request->input('key_id'));

        if (! $nfcCardKey) {
            return redirect()->route('user.manage.nfc.cards')->with('failed', __('NFC Card not found!'));
        }

        $type   = (string) $request->input('link_type', 'vcard');
        $target = match ($type) {
            'url'   => $request->input('link_target'),
            'store' => $request->input('store_id'),
            default => $request->input('card_id'),
        };

        try {
            ChipLinks::setDestination($nfcCardKey, $user, $type, $target, $request->input('label'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('failed', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('user.manage.nfc.cards')->with('success', __('NFC destination saved.'));
    }

    // Unlink (POST; only the owner's keys)
    public function action(Request $request)
    {
        $user       = Auth::user();
        $nfcCardKey = ChipLinks::findOwned($user, (string) $request->query('id'));

        if (! $nfcCardKey) {
            return redirect()->route('user.manage.nfc.cards')->with('failed', __('NFC Card not found!'));
        }

        ChipLinks::unlink($nfcCardKey, $user);

        return redirect()->route('user.manage.nfc.cards')->with('success', __('NFC destination removed.'));
    }

    /** Module on and a plan present. Owning a key is enough; the plan feature is only for ordering. */
    private function guard()
    {
        if (DB::table('config')->where('config_key', 'nfc_order_system')->value('config_value') == '0') {
            abort(404);
        }

        $planDetails = json_decode((string) User::where('id', Auth::id())->value('plan_details'));

        return $planDetails ? null : redirect()->route('user.plans');
    }
}
