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

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class WebsiteSettingController extends Controller
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

    // Update Website Setting
    public function index(Request $request)
    {
        // App name
        $appName = str_replace('"', "", $request->app_name);
        $appName = str_replace("'", "", $appName);

        Setting::where('id', '1')->update([
            'site_name' => $appName
        ]);

        // Set new values using putenv
        $this->updateEnvFile('APP_NAME', '"' . $appName . '"');

        DB::table('config')->where('config_key', 'site_name')->update([
            'config_value' => $appName
        ]);

        DB::table('config')->where('config_key', 'app_theme')->update([
            'config_value' => $request->app_theme,
        ]);

        // Theme slider enabled
        DB::table('config')->where('config_key', 'show_home_slider')->update([
            'config_value' => $request->show_home_slider,
        ]);

        // Custom CSS
        Setting::where('id', 1)->update([
            'custom_css' => Purifier::clean($request->custom_css),
            'custom_scripts' => $request->custom_js, // Trusted field (script)
        ]);

        // 1. Site Logo
        if ($request->hasFile('site_logo')) {
            $validator = Validator::make($request->all(), [
                'site_logo' => 'mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT'),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Delete old site logo from storage
            $setting = Setting::find(1);
            if ($setting) {
                StorageHelper::deleteFile($setting->site_logo);
            }

            // Upload new logo and get relative path
            $siteLogoUrl = StorageHelper::uploadImage($request->file('site_logo'), 'images/web/elements');

            // Update settings in DB
            Setting::where('id', 1)->update([
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $appName,
                'site_logo' => $siteLogoUrl,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
        }

        // 2. Site Logo Light (Fixed name 'logo-light')
        if ($request->hasFile('site_logo_light')) {
            $validator = Validator::make($request->all(), [
                'site_logo_light' => [
                    'required',
                    'file',
                    'mimes:jpeg,jpg,png,gif,svg',
                    'mimetypes:image/jpeg,image/png,image/gif,image/svg+xml',
                    'max:' . env('SIZE_LIMIT'),
                ],
            ]);

            if ($validator->fails()) {
                return back()
                    ->with('failed', $validator->messages()->first())
                    ->withInput();
            }

            $file = $request->file('site_logo_light');

            // Verify uploaded file
            if (!$file->isValid()) {
                return back()
                    ->with('failed', 'Invalid image file.')
                    ->withInput();
            }

            // Get and verify extension
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'svg'];

            if (!in_array($extension, $allowedExtensions, true)) {
                return back()
                    ->with('failed', 'Invalid image format.')
                    ->withInput();
            }

            // Delete existing logo-light files
            foreach ($allowedExtensions as $ext) {
                $oldFile = public_path('img/logo-light.' . $ext);

                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // Upload to public/img with fixed filename
            $filename = 'logo-light.' . $extension;
            $file->move(public_path('img'), $filename);

            // Update settings in DB
            Setting::where('id', 1)->update([
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $appName,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
        }

        // 3. Favicon
        if ($request->hasFile('favi_icon')) {
            $validator = Validator::make($request->all(), [
                'favi_icon' => 'mimes:jpeg,png,jpg,gif,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Delete old favicon
            $setting = Setting::find(1);
            if ($setting) {
                StorageHelper::deleteFile($setting->favicon);
            }

            // Upload new favicon using StorageHelper
            $favi_icon = StorageHelper::uploadImage($request->file('favi_icon'), 'images/web/elements');

            // Update settings in DB
            Setting::where('id', 1)->update([
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $appName,
                'favicon' => $favi_icon,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
        }

        // 4. Primary Image
        if ($request->hasFile('primary_image')) {
            $validator = Validator::make($request->all(), [
                'primary_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Delete old primary image
            $oldPrimaryImage = DB::table('config')->where('config_key', 'primary_image')->value('config_value');
            StorageHelper::deleteFile($oldPrimaryImage);

            // Upload new primary image
            $primary_image = StorageHelper::uploadImage($request->file('primary_image'), 'images/web/elements');

            // Update config in DB
            DB::table('config')->where('config_key', 'primary_image')->update([
                'config_value' => $primary_image,
            ]);
        }

        // 5. Secondary Image
        if ($request->hasFile('secondary_image')) {
            $validator = Validator::make($request->all(), [
                'secondary_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Delete old secondary image
            $oldSecondaryImage = DB::table('config')->where('config_key', 'secondary_image')->value('config_value');
            StorageHelper::deleteFile($oldSecondaryImage);

            // Upload new secondary image
            $secondary_image = StorageHelper::uploadImage($request->file('secondary_image'), 'images/web/elements');

            // Update config in DB
            DB::table('config')->where('config_key', 'secondary_image')->update([
                'config_value' => $secondary_image,
            ]);
        }

        // Page redirect
        return redirect()->route('admin.settings')->with('success', trans('Updated!'));
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
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    // Helper function to handle file uploads
    function uploadImage($file, $folder = 'images/web/elements')
    {
        // Upload file and return relative path using StorageHelper
        return StorageHelper::uploadImage($file, $folder);
    }
}
