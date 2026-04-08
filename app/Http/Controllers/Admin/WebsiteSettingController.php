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

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        Setting::where('id', '1')->update([
            'site_name' => $request->site_name
        ]);

        // App name
        $appName = str_replace('"', "", $request->app_name);
        $appName = str_replace("'", "", $appName);

        // Set new values using putenv
        $this->updateEnvFile('APP_NAME', '"' . $appName . '"');

        DB::table('config')->where('config_key', 'site_name')->update([
            'config_value' => $request->site_name
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
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $request->site_name,
                'site_logo' => $siteLogoUrl,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
        }

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

            // Update only the other settings (logo already uploaded)
            Setting::where('id', 1)->update([
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $request->site_name,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
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
                'google_analytics_id' => $request->google_analytics_id,
                'site_name' => $request->site_name,
                'favicon' => $favi_icon,
                'tawk_chat_bot_key' => $request->tawk_chat_bot_key,
            ]);
        }

        // === Primary Image ===
        if ($request->hasFile('primary_image')) {
            $validator = Validator::make($request->all(), [
                'primary_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $primary_image = $this->uploadImage($request->file('primary_image'));

            DB::table('config')->where('config_key', 'primary_image')->update([
                'config_value' => $primary_image,
            ]);
        }

        // === Secondary Image ===
        if ($request->hasFile('secondary_image')) {
            $validator = Validator::make($request->all(), [
                'secondary_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $secondary_image = $this->uploadImage($request->file('secondary_image'));

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
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, file_get_contents($file));
        return 'storage/' . $path; // return public URL path
    }
}
