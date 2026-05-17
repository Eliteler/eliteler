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
use App\Setting;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Setting
    public function settings()
    {
        // Queries
        $timezonelist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $currencies   = Currency::where('status', 1)->get();
        $settings     = Setting::first();
        $config       = DB::table('config')->get();    

        $image_limit = [
            'SIZE_LIMIT' => env('SIZE_LIMIT', ''),
        ];
        $settings['image_limit'] = $image_limit;

        // Get all languages from the config
        $languages = config('app.languages');

        // Define all languages as selected (or you can replace this with any subset of languages)
        $selectedLanguages = array_keys($languages); // This will make all languages selected

        // Get the default language 
        $defaultLanguage = config('app.locale');

        return view('admin.pages.settings.index', compact('settings', 'timezonelist', 'currencies', 'config', 'languages', 'selectedLanguages', 'defaultLanguage'));
    }

    // Update General Setting
    public function changeGeneralSettings(Request $request)
    {
        // === General Config Updates ===
        $configUpdates = [
            'show_website'           => $request->show_website,
            'registration_page'      => $request->registration_page,
            'timezone'               => $request->timezone,
            'default_language'       => $request->default_language,
            'date_time_format'       => $request->date_time_format,
            'currency'               => $request->currency,
            'currency_format_type'   => $request->currency_format,
            'currency_decimals_place' => $request->currency_decimals_place,
            'share_content'          => $request->share_content,
            'site_name'              => $request->site_name,
            'activate_plan_during_registeration' => $request->activate_plan_during_registeration,
        ];

        foreach ($configUpdates as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }
 
        // === Language Updates ===
        $this->updateLanguages($request->languages, $request->default_language);
        app()->setLocale($request->default_language);

        // === Env Updates ===
        $this->updateEnvFile('TIMEZONE', $request->timezone);
        $this->updateEnvFile('APP_NAME', '"' . str_replace(["'", '"'], '', $request->app_name) . '"');
        $this->updateEnvFile('COOKIE_CONSENT_ENABLED', $request->cookie);
        $this->updateEnvFile('SIZE_LIMIT', $request->image_limit ?? '5120');

        // === Settings Update ===
        Setting::where('id', 1)->update([
            'site_name' => $request->site_name,
        ]);

        // === File Uploads ===
        // Check if logo file is uploaded
        if ($request->hasFile('site_logo')) {
            // Validate image
            $validator = Validator::make($request->all(), [
                'site_logo' => 'mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT'),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Generate filename and path
            $file = $request->file('site_logo');
            $fileName = uniqid('logo_') . '.' . $file->getClientOriginalExtension();
            $path = 'images/web/elements/' . $fileName;

            // Store the file in public disk
            Storage::disk('public')->put($path, file_get_contents($file));

            // Public URL to access the logo (if needed)
            $siteLogoUrl = 'storage/' . $path;

            // Update settings
            Setting::where('id', 1)->update([
                'site_logo' => $siteLogoUrl,
            ]);
        }

        // logo light
        if ($request->hasFile('site_logo_light')) {
            $validator = Validator::make($request->all(), [
                'site_logo_light' => 'mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT'),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Store as a fixed filename, overwrite if exists
            $site_logo_light = '/img/logo-light.png';
            $request->site_logo_light->move(public_path('img'), $site_logo_light);
        }

        // === Favicon ===
        if ($request->hasFile('favi_icon')) {
            $validator = Validator::make($request->all(), [
                'favi_icon' => 'mimes:jpeg,png,jpg,gif,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $favi_icon = $this->uploadImage($request->file('favi_icon'));

            Setting::where('id', 1)->update([
                'favicon' => $favi_icon,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', __('Updated!'));
    }

    // Update Custom CSS & Scripts
    public function updateCustomScript(Request $request)
    {
        // Queries
        Setting::where('id', '1')->update([
            'custom_css'     => $request->header,
            'custom_scripts' => $request->footer,
        ]);

        // Page redirect
        return redirect()->route('admin.settings')->with('success', trans('Updated!'));
    }

    // Clear cache
    public function clearCache()
    {
        try {
            // Clear application cache
            Cache::flush();

            // Clear caches using Artisan
            Artisan::call('cache:clear');  // Clear application cache
            Artisan::call('route:clear');  // Clear route cache
            Artisan::call('config:clear'); // Clear configuration cache
            Artisan::call('view:clear');   // Clear compiled view files

            // Delete all files in bootstrap/cache except .gitignore
            $cachePath  = base_path('bootstrap/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) { 
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/cache except .gitignore
            $cachePath  = base_path('storage/framework/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/views except .gitignore
            $cachePath  = base_path('storage/framework/views');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            return redirect()->route('admin.dashboard')->with('success', trans('Application Cache Cleared Successfully!'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('failed', trans('Failed to Clear Cache. Due to the following error: ') . ' ' . $e->getMessage());
        }
    }

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

    /**
     * This will update the languages array in config/app.php file
     *
     * @param array $languages
     * @return void
     */
    private function updateLanguages(array $languageCodes, string $defaultLanguage)
    {
        // Get all languages from config/app.php
        $languageMap = config('app.availableLanguages');

        // Convert indexed array to associative array using the map
        $languagesArray = [];
        foreach ($languageCodes as $code) {
            if (isset($languageMap[$code])) {
                $languagesArray[$code] = $languageMap[$code];
            }
        }

        // Set the first language as the default locale
        $defaultLocale = $defaultLanguage ?? 'en';

        // Update the languages array in config/app.php
        $this->updateConfigFile($languagesArray, $defaultLocale);
    }

    /**
     * Function to update config/app.php file
     */
    private function updateConfigFile(array $languagesArray, string $defaultLocale)
    {
        $configPath = config_path('app.php');

        // Read the config file
        $configContent = file_get_contents($configPath);

        // Convert the array to a PHP string format with short array syntax
        $newLanguagesArray = var_export($languagesArray, true);
        $newLanguagesArray = str_replace("array (", "[", $newLanguagesArray);
        $newLanguagesArray = str_replace(")", "]", $newLanguagesArray);

        // Replace the existing 'languages' array
        $configContent = preg_replace(
            "/'languages'\s*=>\s*\[[^\]]*\]/",
            "'languages' => " . $newLanguagesArray,
            $configContent
        );

        // Update 'locale' and 'fallback_locale' values
        $configContent = preg_replace(
            "/'locale'\s*=>\s*'[^']*'/",
            "'locale' => '$defaultLocale'",
            $configContent
        );

        $configContent = preg_replace(
            "/'fallback_locale'\s*=>\s*'[^']*'/",
            "'fallback_locale' => '$defaultLocale'",
            $configContent
        );

        // Save the updated content back to config/app.php
        file_put_contents($configPath, $configContent);

        try {
            // Clear application cache
            Cache::flush(); // Clear caches using Artisan
            Artisan::call('cache:clear');  // Clear application cache
            Artisan::call('route:clear');  // Clear route cache
            Artisan::call('config:clear'); // Clear configuration cache
            Artisan::call('view:clear');   // Clear compiled view files

            // Delete all files in bootstrap/cache except .gitignore
            $cachePath  = base_path('bootstrap/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/cache except .gitignore
            $cachePath  = base_path('storage/framework/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/views except .gitignore
            $cachePath  = base_path('storage/framework/views');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }
        } catch (\Exception $e) {
        }
    }

    // Helper function to handle file uploads
    function uploadImage($file, $folder = 'images/web/elements')
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, file_get_contents($file));
        return 'storage/' . $path; // return public URL path
    }
}
