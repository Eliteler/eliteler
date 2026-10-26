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

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MailgunController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $config = DB::table('config')->whereIn('config_key', ['mailgun_smtp_password', 'mailgun_from_address', 'mailgun_region'])->pluck('config_value', 'config_key');

        return Inertia::render('marketing/mailgun', [
            'settings' => [
                // The key itself is never sent to the browser
                'hasKey' => ! empty($config['mailgun_smtp_password']),
                'from'   => $config['mailgun_from_address'] ?? '',
                'region' => ($config['mailgun_region'] ?? 'us') === 'eu' ? 'eu' : 'us',
                'domain' => env('MAIN_DOMAIN'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mailgun_api_key'    => 'nullable|string|max:255',
            'mailgun_from_email' => 'required|email',
            'mailgun_region'     => 'required|in:us,eu',
        ], [], ['mailgun_api_key' => __('API key'), 'mailgun_from_email' => __('sender email')]);

        $values = ['mailgun_from_address' => $data['mailgun_from_email'], 'mailgun_region' => $data['mailgun_region']];
        if (! empty($data['mailgun_api_key'])) {
            $values['mailgun_smtp_password'] = $data['mailgun_api_key'];
        }

        foreach ($values as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }

        return back()->with('success', __('Mailgun settings saved.'));
    }
}
