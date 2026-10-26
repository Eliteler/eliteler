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

namespace Plugins\CookieConsent\Controllers;

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Cookie consent banner on/off (COOKIE_CONSENT_ENABLED in .env).
 * Page: plugins/CookieConsent/resources/js/settings.tsx (npm run plugin:build -- CookieConsent).
 */
class CookieConsentController extends Controller
{
    public function index()
    {
        return PluginPage::render('CookieConsent', 'settings', [
            'enabled' => filter_var(env('COOKIE_CONSENT_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['cookie_consent' => 'required|boolean']);

        $this->updateEnvFile('COOKIE_CONSENT_ENABLED', $request->boolean('cookie_consent') ? 'true' : 'false');

        return back()->with('success', $request->boolean('cookie_consent')
            ? __('The cookie consent banner is on.')
            : __('The cookie consent banner is off.'));
    }

    // update Env File
    public function updateEnvFile($key, $value)
    {
        $envPath = base_path('.env');

        // Check if the .env file exists
        if (file_exists($envPath)) {

            // Read the .env file
            $contentArray = file($envPath);

            // Loop through each line to find the key and update its value
            foreach ($contentArray as &$line) {

                // Split the line by '=' to get key and value
                $parts = explode('=', $line, 2);

                // Check if the key matches and update its value
                if (isset($parts[0]) && $parts[0] === $key) {
                    $line = $key . '=' . $value . PHP_EOL;
                }
            }

            // Implode the array back to a string and write it to the .env file
            $newContent = implode('', $contentArray);
            file_put_contents($envPath, $newContent);

            // Reload the environment variables
            putenv($key . '=' . $value);
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}
