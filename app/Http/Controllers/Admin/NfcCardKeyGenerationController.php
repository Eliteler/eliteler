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

namespace App\Http\Controllers\Admin;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\NfcCardKey;
use App\NfcCardOrder;
use App\Services\Admin\Nfc\NfcKeyService;
use App\Support\Admin\AdminSharedProps;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class NfcCardKeyGenerationController extends Controller
{
    private const TABS = ['all', 'linked', 'unlinked', 'vouchers'];

    public function __construct(private NfcKeyService $keys)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(AdminSharedProps::features()['nfc'], 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tab    = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'all';
        $type   = in_array($request->query('type'), ['order', 'manual'], true) ? $request->query('type') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn () => NfcCardKey::query()
            ->leftJoin('business_cards', 'business_cards.card_id', '=', 'nfc_card_keys.card_id')
            ->leftJoin('users', 'users.user_id', '=', 'business_cards.user_id')
            ->where('nfc_card_keys.status', '!=', 2)
            ->when($type === 'order', fn ($q) => $q->where('nfc_card_keys.key_type', 'online'))
            ->when($type === 'manual', fn ($q) => $q->where('nfc_card_keys.key_type', 'offline'))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nfc_card_keys.unqiue_key', 'like', "%{$search}%")
                    ->orWhere('business_cards.title', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }));

        $keys = $base()
            ->when($tab === 'linked', fn ($q) => $q->where('nfc_card_keys.link_status', 'linked'))
            ->when($tab === 'unlinked', fn ($q) => $q->where('nfc_card_keys.link_status', '!=', 'linked'))
            ->when($tab === 'vouchers', fn ($q) => $q->whereNotNull('nfc_card_keys.associated_plan_id'))
            ->leftJoin('plans', 'plans.plan_id', '=', 'nfc_card_keys.associated_plan_id')
            ->leftJoin('nfc_card_designs', 'nfc_card_designs.nfc_card_id', '=', 'nfc_card_keys.associated_nfc_card_id')
            ->select([
                'nfc_card_keys.nfc_card_key_id', 'nfc_card_keys.unqiue_key', 'nfc_card_keys.key_type', 'nfc_card_keys.link_status',
                'nfc_card_keys.key_status', 'nfc_card_keys.associated_plan_id', 'nfc_card_keys.created_at',
                'business_cards.title as card_title', 'business_cards.card_url',
                'users.user_id as owner_id', 'users.name as owner_name',
                'plans.plan_name', 'nfc_card_designs.nfc_card_name',
            ])
            ->orderByDesc('nfc_card_keys.id')
            ->paginate(25)
            ->withQueryString();

        // Orders the online keys came from (one query for the page)
        $pageKeys   = $keys->getCollection()->where('key_type', 'online')->pluck('unqiue_key');
        $orderByKey = [];
        if ($pageKeys->isNotEmpty()) {
            NfcCardOrder::where(function ($q) use ($pageKeys) {
                foreach ($pageKeys as $key) {
                    $q->orWhereJsonContains('order_details->unique_key', $key);
                }
            })->get(['nfc_card_order_id', 'order_details'])->each(function ($order) use (&$orderByKey) {
                $keys = json_decode($order->order_details ?? '', true)['unique_key'] ?? [];
                foreach ((array) $keys as $key) {
                    $orderByKey[$key] = $order->nfc_card_order_id;
                }
            });
        }

        $keys->through(fn ($row) => [
            'id'      => $row->nfc_card_key_id,
            'key'     => $row->unqiue_key,
            'url'     => $this->keys->readUrl($row->unqiue_key),
            'source'  => $row->key_type === 'online' ? 'order' : 'manual',
            'orderId' => $orderByKey[$row->unqiue_key] ?? null,
            'linked'  => $row->link_status === 'linked',
            'card'    => $row->card_url ? ['title' => $row->card_title, 'url' => url($row->card_url)] : null,
            'owner'   => $row->owner_id ? ['id' => $row->owner_id, 'name' => $row->owner_name] : null,
            'plan'    => $row->plan_name,
            'design'  => $row->nfc_card_name,
            'voucher' => $row->associated_plan_id ? ($row->key_status === 'active' ? 'unused' : 'redeemed') : null,
            'created' => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
        ]);

        $counts = $base()
            ->selectRaw("COUNT(*) as `all`, SUM(nfc_card_keys.link_status = 'linked') as linked, SUM(nfc_card_keys.link_status != 'linked') as unlinked, SUM(nfc_card_keys.associated_plan_id IS NOT NULL) as vouchers")
            ->first();

        return Inertia::render('nfc/keys', [
            'keys'    => $keys,
            'filters' => compact('tab', 'type', 'search'),
            'counts'  => collect(self::TABS)->mapWithKeys(fn ($key) => [$key => (int) $counts->{$key}]),
            'plans'   => DB::table('plans')->where('status', 1)->orderBy('plan_price')->get(['plan_id', 'plan_name'])
                ->map(fn ($p) => ['id' => $p->plan_id, 'name' => $p->plan_name]),
            'designs' => DB::table('nfc_card_designs')->where('status', 1)->orderBy('id')->get(['nfc_card_id', 'nfc_card_name'])
                ->map(fn ($d) => ['id' => $d->nfc_card_id, 'name' => $d->nfc_card_name]),
            'open'    => $request->boolean('generate') ? 'generate' : ($request->query('link') ? 'link:' . $request->query('link') : null),
        ]);
                    }

    // Old pages: open the matching dialog on the keys page
    public function create()
    {
        return redirect()->route('admin.key.generations', ['generate' => 1]);
        }

    public function link($id)
    {
        return redirect()->route('admin.key.generations', ['link' => $id]);
        }

    public function keyWriteToNfcCard($key)
    {
        return redirect()->route('admin.key.generations', ['search' => $key]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nfc_card_key_generations' => 'required|integer|min:1|max:999',
            'associated_plan_id'       => ['nullable', 'required_with:associated_nfc_card_id', Rule::exists('plans', 'plan_id')],
            'associated_nfc_card_id'   => ['nullable', 'required_with:associated_plan_id', Rule::exists('nfc_card_designs', 'nfc_card_id')],
        ], [
            'associated_plan_id.required_with'     => __('Choose a plan to go with the NFC card, or clear both.'),
            'associated_nfc_card_id.required_with' => __('Choose an NFC card to go with the plan, or clear both.'),
        ], [
            'nfc_card_key_generations' => __('number of keys'),
        ]);

        $created = $this->keys->generate((int) $data['nfc_card_key_generations'], $data['associated_plan_id'] ?? null, $data['associated_nfc_card_id'] ?? null);

        return back()->with('success', trans_choice(':count key generated.|:count keys generated.', count($created), ['count' => count($created)]));
    }

    public function updateLinkKey(Request $request)
    {
        $data = $request->validate([
            'key_id'  => 'required|string',
            'card_id' => ['required', Rule::exists('business_cards', 'card_id')],
        ], [], ['card_id' => __('business card')]);

        $key = NfcCardKey::where('nfc_card_key_id', $data['key_id'])->first();

        if (! $key) {
            return back()->with('failed', __('Key not found!'));
        }

        $this->keys->link($key, $data['card_id']);

        return back()->with('success', __('Key linked. Tapping the NFC card now opens this business card.'));
    }

    // Unlink
    public function action(Request $request)
    {
        $data = $request->validate(['id' => 'required|string']);
        $key  = NfcCardKey::where('nfc_card_key_id', $data['id'])->first();

        if (! $key) {
            return back()->with('failed', __('Key not found!'));
        }

        $this->keys->unlink($key);

        return back()->with('success', __('Key unlinked.'));
    }

    public function delete(Request $request)
    {
        $data = $request->validate(['id' => 'required|string']);
        $key  = NfcCardKey::where('nfc_card_key_id', $data['id'])->first();

        if (! $key) {
            return back()->with('failed', __('Key not found!'));
        }

        if ($key->card_id !== null) {
            return back()->with('failed', __('This key is linked to a card. Please unlink the card first.'));
        }

        $key->forceFill(['status' => 2])->save();

        return back()->with('success', __('Key deleted!'));
    }

    // Business cards of a customer, for the link dialog (JSON)
    public function getCustomerCards(Request $request)
    {
        return BusinessCard::where('user_id', (string) $request->input('customerId'))
            ->where('card_status', 'activated')
            ->orderBy('title')
            ->get(['card_id', 'title', 'card_url', 'card_type'])
            ->map(fn ($card) => [
                'id'    => $card->card_id,
                'title' => $card->title,
                'url'   => url($card->card_url),
                'type'  => $card->card_type,
        ]);
    }

    // Greeting letter editor for one key
    public function keyGreetingLetter($key)
    {
        $nfcKey = NfcCardKey::where('unqiue_key', $key)->where('status', '!=', 2)->first();

        if (! $nfcKey) {
            return redirect()->route('admin.key.generations')->with('failed', __('Key not found!'));
        }

        $owner = $this->keys->ownerName($nfcKey);

        return Inertia::render('nfc/greeting-letter', [
            'activationKey' => $nfcKey->unqiue_key,
            'template'    => $this->keys->template(),
            'values'      => $this->keys->shortCodeValues($nfcKey->unqiue_key, $owner),
            'qrUrl'       => $this->keys->activationUrl($nfcKey->unqiue_key),
            'owner'       => $owner,
            'shortCodes'  => NfcKeyService::SHORT_CODES,
        ]);
    }

    public function updateKeyGreetingLetter(Request $request, $key)
    {
        $data = $request->validate(['greeting_letter' => 'required|string|max:65000'], [], ['greeting_letter' => __('greeting letter')]);

        $this->keys->saveTemplate($data['greeting_letter']);

        return back()->with('success', __('Greeting letter saved. It is used for every NFC card.'));
    }
}
