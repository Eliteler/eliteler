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

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\NfcCardDesign;
use App\Support\Admin\AdminSharedProps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * NFC card designs customers can order. status: 1 active, 0 inactive, 2 deleted.
 */
class NfcCardDesignController extends Controller
{
    public const LOW_STOCK = 10;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(AdminSharedProps::features()['nfc'], 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // Cards sold per design (paid orders)
        $sold = DB::table('nfc_card_orders')
            ->join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
            ->where('nfc_card_order_transactions.payment_status', 'success')
            ->selectRaw("nfc_card_orders.nfc_card_id, COUNT(*) as orders, SUM(GREATEST(COALESCE(JSON_EXTRACT(nfc_card_orders.order_details, '$.order_quantity'), 1), 1)) as cards")
            ->groupBy('nfc_card_orders.nfc_card_id')
            ->get()
            ->keyBy('nfc_card_id');

        $designs = NfcCardDesign::where('status', '!=', 2)->orderByDesc('status')->orderBy('id')->get()
            ->map(fn (NfcCardDesign $design) => [
                'id'          => $design->nfc_card_id,
                'name'        => $design->nfc_card_name,
                'description' => $design->nfc_card_description,
                'front'       => $design->nfc_card_front_image ? asset($design->nfc_card_front_image) : null,
                'back'        => $design->nfc_card_back_image ? asset($design->nfc_card_back_image) : null,
                'price'       => (float) $design->nfc_card_price,
                'stock'       => (int) $design->available_stocks,
                'active'      => (int) $design->status === 1,
                'orders'      => (int) ($sold[$design->nfc_card_id]->orders ?? 0),
                'sold'        => (int) ($sold[$design->nfc_card_id]->cards ?? 0),
            ]);

        return Inertia::render('nfc/designs', [
            'designs'     => $designs,
            'lowStock'    => self::LOW_STOCK,
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
            'open'        => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
        ]);
    }

    // Old create page: open the create dialog
    public function create()
    {
        return redirect()->route('admin.designs', ['new' => 1]);
    }

    // Old edit page: open the edit dialog
    public function edit($cardId)
    {
        return redirect()->route('admin.designs', ['edit' => $cardId]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(true), [], $this->attributes());

        NfcCardDesign::create([
            'nfc_card_id'          => uniqid(),
            'nfc_card_name'        => ucfirst($data['nfc_card_name']),
            'nfc_card_description' => ucfirst($data['nfc_card_description'] ?? ''),
            // uploadImage() already returns "storage/..." for the public disk
            'nfc_card_front_image' => StorageHelper::uploadImage($request->file('nfc_card_front_image'), 'nfc-card-designs', Str::random(20)),
            'nfc_card_back_image'  => StorageHelper::uploadImage($request->file('nfc_card_back_image'), 'nfc-card-designs', Str::random(20)),
            'nfc_card_price'       => $data['nfc_card_price'],
            'available_stocks'     => $data['nfc_card_available_stocks'],
        ]);

        return redirect()->route('admin.designs')->with('success', __('NFC card added.'));
    }

    public function update(Request $request)
    {
        $design = NfcCardDesign::where('nfc_card_id', (string) $request->input('nfc_card_id'))->firstOrFail();
        $data   = $request->validate($this->rules(false), [], $this->attributes());

        $design->nfc_card_name        = $data['nfc_card_name'];
        $design->nfc_card_description = $data['nfc_card_description'] ?? '';
        $design->nfc_card_price       = $data['nfc_card_price'];
        $design->available_stocks     = $data['nfc_card_available_stocks'];

        foreach (['nfc_card_front_image', 'nfc_card_back_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($design->{$field}) {
                    StorageHelper::deleteFile($design->{$field});
                }
                $design->{$field} = StorageHelper::uploadImage($request->file($field), 'nfc-card-designs', Str::random(20));
            }
        }

        $design->save();

        return back()->with('success', __('NFC card updated.'));
    }

    // Set or adjust the stock
    public function updateStock(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'mode'   => 'required|in:set,add,remove',
            'amount' => 'required|integer|min:0|max:1000000',
        ]);

        $design = NfcCardDesign::where('nfc_card_id', $data['id'])->firstOrFail();
        $design->available_stocks = max(0, match ($data['mode']) {
            'add'    => $design->available_stocks + $data['amount'],
            'remove' => $design->available_stocks - $data['amount'],
            default  => $data['amount'],
        });
        $design->save();

        return back()->with('success', __('Stock updated to :count.', ['count' => $design->available_stocks]));
    }

    // Activate / deactivate / delete
    public function action(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'status' => 'required|in:activate,deactivate,delete',
        ]);

        $updated = NfcCardDesign::where('nfc_card_id', $data['id'])->update([
            'status' => ['activate' => 1, 'deactivate' => 0, 'delete' => 2][$data['status']],
        ]);

        if (! $updated) {
            return back()->with('failed', __('Not Found!'));
        }

        return back()->with('success', match ($data['status']) {
            'activate'   => __('NFC card is now available to order.'),
            'deactivate' => __('NFC card hidden from customers.'),
            'delete'     => __('NFC card deleted.'),
        });
    }

    private function rules(bool $creating): array
    {
        $image = ($creating ? 'required' : 'nullable') . '|file|mimes:jpeg,png,jpg,webp|max:' . env('SIZE_LIMIT', 2048);

        return [
            'nfc_card_name'             => 'required|string|max:255',
            'nfc_card_description'      => 'nullable|string|max:4999',
            'nfc_card_price'            => 'required|numeric|min:0',
            'nfc_card_available_stocks' => 'required|integer|min:0',
            'nfc_card_front_image'      => $image,
            'nfc_card_back_image'       => $image,
        ];
    }

    private function attributes(): array
    {
        return [
            'nfc_card_name'             => __('name'),
            'nfc_card_description'      => __('description'),
            'nfc_card_price'            => __('price'),
            'nfc_card_available_stocks' => __('stock'),
            'nfc_card_front_image'      => __('front image'),
            'nfc_card_back_image'       => __('back image'),
        ];
    }
}
