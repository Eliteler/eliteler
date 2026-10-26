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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Invoice details (seller address, tax, numbering) and the text around
 * invoice emails. All values live in the config table.
 */
class TaxSettingController extends Controller
{
    private const INVOICE_KEYS = [
        'invoice_prefix', 'invoice_name', 'invoice_email', 'invoice_phone', 'invoice_address', 'invoice_city',
        'invoice_state', 'invoice_zipcode', 'invoice_country', 'tax_name', 'tax_number', 'tax_value', 'invoice_footer',
    ];

    private const EMAIL_KEYS = ['email_heading', 'email_footer'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function taxSetting()
    {
        $config = DB::table('config')->whereIn('config_key', [...self::INVOICE_KEYS, ...self::EMAIL_KEYS])->pluck('config_value', 'config_key');

        return Inertia::render('settings/invoice', [
            'values' => collect([...self::INVOICE_KEYS, ...self::EMAIL_KEYS])->mapWithKeys(fn ($key) => [$key => (string) ($config[$key] ?? '')]),
        ]);
    }

    /** Saves the whole page: invoice details, tax and email text. */
    public function updateTaxSetting(Request $request)
    {
        $data = $request->validate([
            'invoice_prefix'  => 'nullable|string|max:20',
            'invoice_name'    => 'required|string|max:191',
            'invoice_email'   => 'required|email|max:191',
            'invoice_phone'   => 'nullable|string|max:50',
            'invoice_address' => 'nullable|string|max:255',
            'invoice_city'    => 'nullable|string|max:100',
            'invoice_state'   => 'nullable|string|max:100',
            'invoice_zipcode' => 'nullable|string|max:20',
            'invoice_country' => 'nullable|string|max:100',
            'tax_name'        => 'nullable|string|max:100',
            'tax_number'      => 'nullable|string|max:100',
            'tax_value'       => 'nullable|numeric|min:0|max:100',
            'invoice_footer'  => 'nullable|string|max:1000',
            'email_heading'   => 'nullable|string|max:1000',
            'email_footer'    => 'nullable|string|max:1000',
        ], [], [
            'invoice_prefix'  => __('invoice prefix'),
            'invoice_name'    => __('business name'),
            'invoice_email'   => __('email'),
            'invoice_phone'   => __('phone'),
            'invoice_address' => __('address'),
            'invoice_city'    => __('city'),
            'invoice_state'   => __('state'),
            'invoice_zipcode' => __('ZIP code'),
            'invoice_country' => __('country'),
            'tax_name'        => __('tax name'),
            'tax_number'      => __('tax ID'),
            'tax_value'       => __('tax rate'),
            'invoice_footer'  => __('invoice footer'),
            'email_heading'   => __('email heading'),
            'email_footer'    => __('email footer'),
        ]);

        if ((float) ($data['tax_value'] ?? 0) > 0 && blank($data['tax_name'] ?? null)) {
            return back()->withErrors(['tax_name' => __('Add a tax name, e.g. VAT or GST, so customers know what the tax is.')]);
        }

        // A tax rate of 0 means no tax; keep the stored value numeric as before
        $data['tax_value'] = (string) (($data['tax_value'] ?? '') === '' ? 0 : $data['tax_value'] + 0);

        foreach ($data as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => (string) ($value ?? '')]);
        }

        return back()->with('success', __('Invoice settings saved.'));
    }

    // Kept for old forms; the page now saves email text with the invoice details
    public function updateEmailSetting(Request $request)
    {
        $data = $request->validate([
            'email_heading' => 'nullable|string|max:1000',
            'email_footer'  => 'nullable|string|max:1000',
        ]);

        foreach (self::EMAIL_KEYS as $key) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => (string) ($data[$key] ?? '')]);
        }

        return back()->with('success', __('Invoice settings saved.'));
    }
}
