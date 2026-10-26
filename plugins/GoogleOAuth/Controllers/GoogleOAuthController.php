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

namespace Plugins\GoogleOAuth\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;

/**
 * "Sign in with Google" for customers. Settings live in .env:
 * GOOGLE_ENABLE (on/off), GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT.
 * Page: plugins/GoogleOAuth/resources/js/settings.tsx.
 */
class GoogleOAuthController extends Controller
{
    public function googleOAuthSettings()
    {
        return PluginPage::render('GoogleOAuth', 'settings', [
            'values'      => [
                'google_auth_enable' => env('GOOGLE_ENABLE', 'off') === 'on',
                'google_client_id'   => (string) env('GOOGLE_CLIENT_ID', ''),
            ],
            // The secret never leaves the server
            'savedSecret' => filled(env('GOOGLE_CLIENT_SECRET')),
            'redirectUrl' => $this->redirectUrl(),
        ]);
    }

    public function googleOAuthSettingsUpdate(Request $request)
    {
        $enabled = $request->boolean('google_auth_enable');

        $data = $request->validate([
            'google_auth_enable'   => 'required|boolean',
            'google_client_id'     => [$enabled ? 'required' : 'nullable', 'string', 'max:255'],
            'google_client_secret' => [$enabled && blank(env('GOOGLE_CLIENT_SECRET')) ? 'required' : 'nullable', 'string', 'max:255'],
        ], [
            'google_client_id.required'     => __('Add the client ID to turn on Sign in with Google.'),
            'google_client_secret.required' => __('Add the client secret to turn on Sign in with Google.'),
        ], [
            'google_client_id'     => __('client ID'),
            'google_client_secret' => __('client secret'),
        ]);

        $quote = fn (?string $value) => '"' . str_replace('"', "'", trim((string) $value)) . '"';

        $this->updateEnv('GOOGLE_ENABLE', $enabled ? 'on' : 'off');
        $this->updateEnv('GOOGLE_CLIENT_ID', $quote($data['google_client_id'] ?? ''));
        // A blank secret keeps the saved one
        if (filled($data['google_client_secret'] ?? null)) {
            $this->updateEnv('GOOGLE_CLIENT_SECRET', $quote($data['google_client_secret']));
        }
        $this->updateEnv('GOOGLE_REDIRECT', $quote($this->redirectUrl()));

        return back()->with('success', $enabled ? __('Sign in with Google is on.') : __('Sign in with Google is off.'));
    }

    private function redirectUrl(): string
    {
        return url('/sign-in-with-google');
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
