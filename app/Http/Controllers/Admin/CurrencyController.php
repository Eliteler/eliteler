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

use App\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Currencies customers can be charged in. currencies.status: 1 active,
 * 0 deleted. symbol_first is the string "true"/"false". The default currency
 * is config "currency" (an ISO code) and can't be deleted.
 */
class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function currencies(Request $request)
    {
        $search          = trim((string) $request->query('search', ''));
        $defaultCurrency = $this->defaultCode();

        $query = Currency::where('status', 1);

        // Filter currencies by search query
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('iso_code', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        // Place default currency first, then sort by ISO code
        $query->orderByRaw("CASE WHEN iso_code = ? THEN 0 ELSE 1 END", [$defaultCurrency])
            ->orderBy('iso_code');

        $perPage = max(1, (int) $request->query('per_page', 10));

        // Paginate and format currency data
        $currencies = $query->paginate($perPage)->withQueryString()->through(fn(Currency $currency) => [
            'id'                  => $currency->id,
            'name'                => $currency->name,
            'iso_code'            => $currency->iso_code,
            'symbol'              => $currency->symbol,
            'symbol_first'        => $currency->symbol_first !== 'false',
            'priority'            => (string) $currency->priority,
            'subunit'             => (string) $currency->subunit,
            'subunit_to_unit'     => (string) $currency->subunit_to_unit,
            'decimal_mark'        => (string) $currency->decimal_mark,
            'thousand_separator'  => (string) $currency->thousands_separator,
            'html_entity'         => (string) $currency->html_entity,
            'iso_numeric'         => (string) $currency->iso_numeric,
        ]);

        return Inertia::render('settings/currencies', [
            'currencies'      => $currencies,
            'defaultCurrency' => $defaultCurrency,
            'open'            => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
            'search'          => $search,
        ]);
    }

    // Old sub-pages now open the dialog on the list page
    public function createCurrency()
    {
        return redirect()->route('admin.currencies', ['new' => 1]);
    }

    public function editCurrency($id)
    {
        return redirect()->route('admin.currencies', ['edit' => $id]);
    }

    public function saveCurrency(Request $request)
    {
        $data = $this->validated($request);

        // A deleted currency with the same code is brought back instead of duplicated
        $currency = Currency::where('iso_code', $data['iso_code'])->first() ?? new Currency();
        $this->fill($currency, $data)->status = 1;
        $currency->save();

        return back()->with('success', __(':code added.', ['code' => $currency->iso_code]));
    }

    public function updateCurrency(Request $request)
    {
        $currency = Currency::where('id', $request->input('id'))->where('status', 1)->firstOrFail();
        $data     = $this->validated($request, $currency);
        $wasDefault = $currency->iso_code === $this->defaultCode();

        $this->fill($currency, $data)->save();

        // Keep the default pointing at this currency if its code changed
        if ($wasDefault && $currency->iso_code !== $this->defaultCode()) {
            DB::table('config')->where('config_key', 'currency')->update(['config_value' => $currency->iso_code]);
        }

        return back()->with('success', __(':code saved.', ['code' => $currency->iso_code]));
    }

    public function deleteCurrency(Request $request)
    {
        $currency = Currency::where('id', $request->input('id'))->where('status', 1)->firstOrFail();

        if ($currency->iso_code === $this->defaultCode()) {
            return back()->with('failed', __(':code is your default currency. Choose another default in General settings first.', ['code' => $currency->iso_code]));
        }

        if (Currency::where('status', 1)->count() <= 1) {
            return back()->with('failed', __('Keep at least one currency.'));
        }

        $currency->forceFill(['status' => 0])->save();

        return back()->with('success', __(':code deleted.', ['code' => $currency->iso_code]));
    }

    private function validated(Request $request, ?Currency $current = null): array
    {
        $request->merge(['iso_code' => strtoupper(trim((string) $request->input('iso_code')))]);

        return $request->validate([
            'name'               => 'required|string|max:100',
            'iso_code'           => ['required', 'alpha', 'size:3', Rule::unique('currencies', 'iso_code')->where('status', 1)->ignore($current?->id)],
            'symbol'             => 'required|string|max:10',
            'symbol_first'       => 'required|boolean',
            'priority'           => 'nullable|integer|min:0|max:1000',
            'subunit'            => 'nullable|string|max:50',
            'subunit_to_unit'    => 'nullable|integer|min:0|max:100000',
            'decimal_mark'       => 'nullable|string|max:2',
            'thousand_separator' => 'nullable|string|max:2',
            'html_entity'        => 'nullable|string|max:50',
            'iso_numeric'        => 'nullable|integer|min:0|max:999',
        ], [
            'iso_code.unique' => __('A currency with this code already exists.'),
            'iso_code.size'   => __('Use the 3-letter ISO code, e.g. USD.'),
            'iso_code.alpha'  => __('Use the 3-letter ISO code, e.g. USD.'),
        ], [
            'iso_code'           => __('code'),
            'symbol_first'       => __('symbol position'),
            'subunit_to_unit'    => __('subunits per unit'),
            'thousand_separator' => __('thousands separator'),
            'iso_numeric'        => __('numeric code'),
        ]);
    }

    private function fill(Currency $currency, array $data): Currency
    {
        $currency->name                = $data['name'];
        $currency->iso_code            = $data['iso_code'];
        $currency->symbol              = $data['symbol'];
        $currency->symbol_first        = $data['symbol_first'] ? 'true' : 'false';
        $currency->priority            = $data['priority'] ?? 100;
        $currency->subunit             = $data['subunit'] ?? '';
        $currency->subunit_to_unit     = $data['subunit_to_unit'] ?? 100;
        $currency->decimal_mark        = $data['decimal_mark'] ?? '.';
        $currency->thousands_separator = $data['thousand_separator'] ?? ',';
        $currency->html_entity         = $data['html_entity'] ?? '';
        $currency->iso_numeric         = $data['iso_numeric'] ?? 0;

        return $currency;
    }

    private function defaultCode(): string
    {
        return (string) DB::table('config')->where('config_key', 'currency')->value('config_value');
    }
}
