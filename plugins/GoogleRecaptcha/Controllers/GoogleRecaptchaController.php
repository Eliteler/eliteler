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

namespace Plugins\GoogleRecaptcha\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;

/**
 * Google reCAPTCHA v2 on sign-in/sign-up forms. Settings live in .env:
 * RECAPTCHA_ENABLE (on/off), RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY.
 * Page: plugins/GoogleRecaptcha/resources/js/settings.tsx.
 */
class GoogleRecaptchaController extends Controller
{
    public function googleRecaptchaSettings()
    {
        return PluginPage::render('GoogleRecaptcha', 'settings', [
            'values'      => [
                'recaptcha_enable'   => env('RECAPTCHA_ENABLE', 'off') === 'on',
                'recaptcha_site_key' => (string) env('RECAPTCHA_SITE_KEY', ''),
            ],
            // The secret key never leaves the server
            'savedSecret' => filled(env('RECAPTCHA_SECRET_KEY')),
        ]);
    }

    public function googleRecaptchaSettingsUpdate(Request $request)
    {
        $enabled = $request->boolean('recaptcha_enable');

        $data = $request->validate([
            'recaptcha_enable'     => 'required|boolean',
            'recaptcha_site_key'   => [$enabled ? 'required' : 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
            'recaptcha_secret_key' => [$enabled && blank(env('RECAPTCHA_SECRET_KEY')) ? 'required' : 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
        ], [
            'recaptcha_site_key.required'   => __('Add the site key to turn on reCAPTCHA.'),
            'recaptcha_secret_key.required' => __('Add the secret key to turn on reCAPTCHA.'),
            'recaptcha_site_key.regex'      => __('This doesn’t look like a reCAPTCHA key.'),
            'recaptcha_secret_key.regex'    => __('This doesn’t look like a reCAPTCHA key.'),
        ], [
            'recaptcha_site_key'   => __('site key'),
            'recaptcha_secret_key' => __('secret key'),
        ]);

        $this->updateEnv('RECAPTCHA_ENABLE', $enabled ? 'on' : 'off');
        $this->updateEnv('RECAPTCHA_SITE_KEY', trim((string) ($data['recaptcha_site_key'] ?? '')));
        // A blank secret keeps the saved one
        if (filled($data['recaptcha_secret_key'] ?? null)) {
            $this->updateEnv('RECAPTCHA_SECRET_KEY', trim($data['recaptcha_secret_key']));
        }

        return back()->with('success', $enabled ? __('reCAPTCHA is on.') : __('reCAPTCHA is off.'));
    }

    public function updateEnv($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            // Read the file contents
            $envContent = file_get_contents($path);

            // Create a new key-value pair
            $pattern     = "/^" . preg_quote($key) . "=.*/m";
            $replacement = $key . '=' . $value;

            // Check if the key exists in .env
            if (preg_match($pattern, $envContent)) {
                // Replace existing key
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // Append new key-value pair
                $envContent .= "\n" . $replacement;
            }

            // Write back to .env file
            file_put_contents($path, $envContent);
        }
    }

}
