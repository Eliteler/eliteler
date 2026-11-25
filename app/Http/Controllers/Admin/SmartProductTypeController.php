<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\NfcCardDesign;
use App\SmartProductType;
use App\Support\Admin\AdminSharedProps;
use App\Support\SmartProducts\SmartProductCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SmartProductTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(AdminSharedProps::features()['nfc'], 404);

            return $next($request);
        });
    }

    public function index()
    {
        $types = $this->managedTypes()->orderBy('name')->paginate(10)->withQueryString();
        $counts = NfcCardDesign::query()
            ->whereIn('product_type', $types->getCollection()->pluck('code'))
            ->where('status', '!=', 2)
            ->selectRaw('product_type, COUNT(*) AS total')
            ->groupBy('product_type')
            ->pluck('total', 'product_type');

        return Inertia::render('nfc/product-types', [
            'productTypes' => $types->through(fn (SmartProductType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'productsCount' => (int) $counts->get($type->code, 0),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $name = $this->validatedName($request);
        $type = new SmartProductType(['code' => 'type_' . strtolower((string) Str::ulid()), 'name' => $name]);
        $this->save($type);

        return back()->with('success', __('Product type added.'));
    }

    public function update(Request $request, int $type)
    {
        DB::transaction(function () use ($request, $type) {
            $record = $this->managedTypes()->lockForUpdate()->findOrFail($type);
            $record->name = $this->validatedName($request, $record);
            $this->save($record);
        });

        return back()->with('success', __('Product type updated.'));
    }

    public function destroy(Request $request, int $type)
    {
        $data = $request->validate(['expected_product_count' => 'required|integer|min:0']);

        $count = DB::transaction(function () use ($type, $data) {
            $record = $this->managedTypes()->lockForUpdate()->findOrFail($type);
            $products = NfcCardDesign::where('product_type', $record->code);
            $count = (clone $products)->lockForUpdate()->pluck('status')->filter(fn ($status) => (int) $status !== 2)->count();

            if ($count !== (int) $data['expected_product_count']) {
                throw ValidationException::withMessages([
                    'product_usage' => __('Product usage changed. Review the current count and confirm again.'),
                ]);
            }

            SmartProductType::where('code', SmartProductCatalog::GENERIC_TYPE)->firstOrFail(['id']);
            $products->update(['product_type' => SmartProductCatalog::GENERIC_TYPE]);
            $record->delete();

            return $count;
        }, 3);

        $message = $count > 0
            ? trans_choice('{1} Product type deleted. One product moved to Generic.|[2,*] Product type deleted. :count products moved to Generic.', $count)
            : __('Product type deleted.');

        return back()->with('success', $message);
    }

    private function managedTypes(): Builder
    {
        return SmartProductType::where('code', '!=', SmartProductCatalog::GENERIC_TYPE);
    }

    private function validatedName(Request $request, ?SmartProductType $type = null): string
    {
        $unique = Rule::unique('smart_product_types', 'name');
        if ($type !== null) {
            $unique->ignore($type);
        }

        return $request->validate(
            ['name' => ['required', 'string', 'max:100', $unique]],
            ['name.unique' => __('A product type with this name already exists.')],
        )['name'];
    }

    private function save(SmartProductType $type): void
    {
        try {
            $type->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => __('A product type with this name already exists.')]);
        }
    }
}
