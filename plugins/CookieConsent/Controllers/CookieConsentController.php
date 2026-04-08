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

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CookieConsentController extends Controller
{
    // Index
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // return view
        return view()->file(base_path('plugins/CookieConsent/Views/index.blade.php'), compact('settings', 'config'));
    }

    // Update Cookie Consent
    public function update(Request $request)
    {
        // update to env
        $this->updateEnvFile('COOKIE_CONSENT_ENABLED', $request->cookie_consent);

        // return redirect to index
        return redirect()->route('admin.plugin.cookie-consent')->with('success', trans('Cookie Consent Settings Updated Successfully!'));
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
