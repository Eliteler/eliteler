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
use App\SmartProductType;
use App\Support\Admin\AdminSharedProps;
use App\Services\SmartProducts\OrderAssets;
use App\Support\SmartProducts\SmartProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Smart Products (table nfc_card_designs) customers can order.
 * status: 1 active, 0 inactive, 2 deleted.
 */
class NfcCardDesignController extends Controller
{
    public const LOW_STOCK = 10;
    public const PER_PAGE = 12;
    public const MAX_PHOTOS = 8;

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
        // Items sold per product (paid orders)
        $sold = DB::table('nfc_card_orders')
            ->join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
            ->where('nfc_card_order_transactions.payment_status', 'success')
            ->selectRaw("nfc_card_orders.nfc_card_id, COUNT(*) as orders, SUM(GREATEST(COALESCE(JSON_EXTRACT(nfc_card_orders.order_details, '$.order_quantity'), 1), 1)) as cards")
            ->groupBy('nfc_card_orders.nfc_card_id')
            ->get()
            ->keyBy('nfc_card_id');

        $types = SmartProductCatalog::typeLabels();
        $type   = $types->has((string) $request->query('type')) ? (string) $request->query('type') : 'all';
        $tab    = in_array($request->query('tab'), ['active', 'hidden'], true) ? (string) $request->query('tab') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn () => NfcCardDesign::where('status', '!=', 2)
            ->when($type !== 'all', fn ($q) => $q->where('product_type', $type))
            ->when($search !== '', fn ($q) => $q->where('nfc_card_name', 'like', "%{$search}%"));

        $counts = $base()->selectRaw('COUNT(*) as `all`, SUM(status = 1) as active, SUM(status = 0) as hidden')->first();

        $designs = $base()
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab === 'active' ? 1 : 0))
            ->orderByDesc('status')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->through(fn (NfcCardDesign $design) => [
                'id'               => $design->nfc_card_id,
                'name'             => $design->nfc_card_name,
                'type'             => SmartProductCatalog::typeCode($design->product_type),
                'short'            => $design->short_description,
                'description'      => $design->nfc_card_description,
                'photos'           => collect($design->photos())->map(fn ($path) => ['path' => $path, 'url' => asset($path)])->values(),
                'price'            => (float) $design->nfc_card_price,
                'compareAt'        => $design->compare_at_price === null ? null : (float) $design->compare_at_price,
                'stock'            => (int) $design->available_stocks,
                'generatesKeys'    => $design->generates_keys !== false,
                'allowLogo'        => $design->allow_logo_upload !== false,
                'imageAssets'      => array_values((array) ($design->image_assets ?? [])),
                'linkType'         => $design->default_link_type ?: 'vcard',
                'sortOrder'        => (int) $design->sort_order,
                'active'           => (int) $design->status === 1,
                'orders'           => (int) ($sold[$design->nfc_card_id]->orders ?? 0),
                'sold'             => (int) ($sold[$design->nfc_card_id]->cards ?? 0),
            ])
            ->withQueryString();

        return Inertia::render('nfc/designs', [
            'designs'     => $designs,
            'filters'     => ['type' => $type, 'tab' => $tab, 'search' => $search],
            'counts'      => ['all' => (int) $counts->all, 'active' => (int) $counts->active, 'hidden' => (int) $counts->hidden],
            'types'       => $types,
            'linkTypes'   => collect(SmartProductCatalog::LINK_TYPES)->map(fn ($label) => __($label)),
            'lowStock'    => self::LOW_STOCK,
            'maxPhotos'   => self::MAX_PHOTOS,
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
        $data = $request->validate($this->rules(), [], $this->attributes());

        $design = new NfcCardDesign(['nfc_card_id' => uniqid(), 'status' => 1]);
        $this->fill($design, $data);
        $this->saveDesign($design, $request, $data['photos']);

        return redirect()->route('admin.designs')->with('success', __('Product added.'));
    }

    public function update(Request $request)
    {
        $design = NfcCardDesign::where('nfc_card_id', (string) $request->input('nfc_card_id'))->where('status', '!=', 2)->firstOrFail();
        $data   = $request->validate($this->rules(), [], $this->attributes());

        $this->fill($design, $data);
        $this->saveDesign($design, $request, $data['photos']);

        return back()->with('success', __('Product updated.'));
    }

    private function fill(NfcCardDesign $design, array $data): void
    {
        $design->nfc_card_name        = ucfirst($data['nfc_card_name']);
        $design->product_type         = $data['product_type'];
        $design->short_description    = $data['short_description'] ?? null;
        $design->nfc_card_description = $data['nfc_card_description'] ?? '';
        $design->nfc_card_price       = $data['nfc_card_price'];
        $design->compare_at_price     = ($data['compare_at_price'] ?? '') === '' ? null : $data['compare_at_price'];
        $design->available_stocks     = $data['nfc_card_available_stocks'];
        $design->generates_keys       = (bool) ($data['generates_keys'] ?? true);
        $design->allow_logo_upload    = (bool) ($data['allow_logo_upload'] ?? true);
        // Kept even when switched off, so turning it back on restores the list
        $design->image_assets         = OrderAssets::fromInput($data['image_assets'] ?? []);

        if ($design->allow_logo_upload && ! $design->image_assets) {
            throw ValidationException::withMessages(['image_assets' => __('Add at least one image asset, or turn Image assets off.')]);
        }
        $design->default_link_type    = $data['default_link_type'];
        $design->sort_order           = (int) ($data['sort_order'] ?? 0);
    }

    private function saveDesign(NfcCardDesign $design, Request $request, array $photos): void
    {
        DB::transaction(function () use ($design, $request, $photos) {
            // Type deletion takes the same lock before it moves any products.
            $type = SmartProductType::where('code', $design->product_type)->lockForUpdate()->first(['code']);

            if ($type === null) {
                throw ValidationException::withMessages(['product_type' => __('Select an available product type.')]);
            }

            $design->product_type = $type->code;
            $this->savePhotos($design, $request, $photos);
            $design->save();
        });
    }

    /**
     * photos[] is the gallery in display order; each item is either an
     * existing photo (path) or a new upload (file). The first two photos
     * are also kept in the old front/back image columns, which the customer
     * pages, website, emails and app still read.
     */
    private function savePhotos(NfcCardDesign $design, Request $request, array $items): void
    {
        $current = $design->exists ? array_values(array_unique(array_filter([...$design->photos(), $design->nfc_card_front_image, $design->nfc_card_back_image]))) : [];
        $paths   = [];

        foreach ($items as $i => $item) {
            if ($request->hasFile("photos.{$i}.file")) {
                $paths[] = StorageHelper::uploadImage($request->file("photos.{$i}.file"), 'nfc-card-designs', Str::random(20));
            } elseif (! empty($item['path']) && in_array($item['path'], $current, true)) {
                $paths[] = $item['path'];
            }
        }

        $paths = array_values(array_unique(array_filter($paths)));

        if (! $paths) {
            throw ValidationException::withMessages(['photos' => __('Add at least one photo.')]);
        }

        // Remove uploads that are no longer used (never the bundled defaults in public/img)
        foreach (array_diff($current, $paths) as $removed) {
            if (str_starts_with($removed, 'storage/')) {
                StorageHelper::deleteFile($removed);
            }
        }

        $design->gallery_images       = $paths;
        $design->nfc_card_front_image = $paths[0];
        $design->nfc_card_back_image  = $paths[1] ?? $paths[0];
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
            'activate'   => __('Product is now available to order.'),
            'deactivate' => __('Product hidden from customers.'),
            'delete'     => __('Product deleted.'),
        });
    }

    private function rules(): array
    {
        return [
            'nfc_card_name'             => 'required|string|max:255',
            'product_type'              => ['required', 'string', 'max:50', Rule::exists('smart_product_types', 'code')],
            'short_description'         => 'nullable|string|max:255',
            'nfc_card_description'      => 'nullable|string|max:4999',
            'nfc_card_price'            => 'required|numeric|min:0|max:9999999',
            'compare_at_price'          => 'nullable|numeric|min:0|max:9999999|gt:nfc_card_price',
            'nfc_card_available_stocks' => 'required|integer|min:0|max:1000000',
            'generates_keys'            => 'boolean',
            'allow_logo_upload'         => 'boolean',
            'image_assets'              => 'nullable|array|max:' . OrderAssets::MAX_ASSETS,
            'image_assets.*.key'        => 'nullable|string|max:20',
            'image_assets.*.name'       => 'required|string|max:80|distinct:ignore_case',
            'image_assets.*.resolution' => 'nullable|string|max:120',
            'image_assets.*.required'   => 'boolean',
            'default_link_type'         => ['required', Rule::in(array_keys(SmartProductCatalog::LINK_TYPES))],
            'sort_order'                => 'nullable|integer|min:0|max:100000',
            'photos'                    => 'required|array|min:1|max:' . self::MAX_PHOTOS,
            'photos.*.path'             => 'nullable|string|max:255',
            'photos.*.file'             => 'nullable|file|mimes:jpeg,png,jpg,webp|max:' . env('SIZE_LIMIT', 2048),
        ];
    }

    private function attributes(): array
    {
        return [
            'nfc_card_name'             => __('name'),
            'product_type'              => __('product type'),
            'short_description'         => __('short description'),
            'nfc_card_description'      => __('description'),
            'nfc_card_price'            => __('price'),
            'compare_at_price'          => __('compare-at price'),
            'nfc_card_available_stocks' => __('stock'),
            'default_link_type'         => __('default link'),
            'image_assets'              => __('image assets'),
            'image_assets.*.name'       => __('asset name'),
            'image_assets.*.resolution' => __('suggested resolution'),
            'sort_order'                => __('sort order'),
            'photos'                    => __('photos'),
            'photos.*.file'             => __('photo'),
        ];
    }
}
