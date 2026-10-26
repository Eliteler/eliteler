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
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Setting;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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

    /** Config keys saved by the general settings form. */
    private const GENERAL_KEYS = [
        'show_website',
        'website_redirect_url',
        'registration_page',
        'timezone',
        'default_language',
        'date_time_format',
        'currency',
        'currency_format_type',
        'currency_decimals_place',
        'share_content',
        'site_name',
        'activate_plan_during_registeration',
    ];

    private const CURRENCY_FORMATS = ['1,234,567.89', '12,34,567.89', '1.234.567,89', '1 234 567,89', "1'234'567.89"];

    private const LOGO_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'];

    // General settings
    public function settings()
    {
        $config   = DB::table('config')->whereIn('config_key', self::GENERAL_KEYS)->pluck('config_value', 'config_key');
        $settings = Setting::first();

        return Inertia::render('settings/general', [
            'values' => [
                'site_name'               => (string) ($config['site_name'] ?? ''),
                'show_website'            => ($config['show_website'] ?? 'yes') === 'yes',
                'website_redirect_url'    => (string) ($config['website_redirect_url'] ?? ''),
                // config value "0" means sign-ups are open
                'allow_registration'      => ($config['registration_page'] ?? '0') === '0',
                'free_plan_on_signup'     => ($config['activate_plan_during_registeration'] ?? '0') === '1',
                'timezone'                => (string) ($config['timezone'] ?? config('app.timezone')),
                'date_time_format'        => (string) ($config['date_time_format'] ?? ''),
                'default_language'        => (string) ($config['default_language'] ?? config('app.locale')),
                'languages'               => array_keys(config('app.languages', [])),
                'currency'                => (string) ($config['currency'] ?? ''),
                'currency_format'         => (string) ($config['currency_format_type'] ?? self::CURRENCY_FORMATS[0]),
                'currency_decimals_place' => (string) ($config['currency_decimals_place'] ?? '2'),
                'image_limit'             => (string) env('SIZE_LIMIT', 2048),
                'share_content'           => (string) ($config['share_content'] ?? ''),
            ],
            'images' => [
                'logo'      => $settings?->site_logo ? asset($settings->site_logo) : null,
                'logoLight' => ($light = $this->lightLogoPath()) ? asset('img/' . basename($light)) . '?v=' . filemtime($light) : null,
                'favicon'   => $settings?->favicon ? asset($settings->favicon) : null,
            ],
            'options' => [
                'timezones'       => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
                'currencies'      => Currency::where('status', 1)->orderBy('iso_code')->get(['iso_code', 'name', 'symbol'])->map(fn($c) => [
                    'value' => $c->iso_code,
                    'label' => $c->iso_code . ' · ' . $c->name,
                    'hint'  => $c->symbol,
                ]),
                'currencyFormats' => self::CURRENCY_FORMATS,
                'dateFormats'     => collect(getDateTimeFormats())->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])->values(),
                'languages'       => collect(config('app.availableLanguages', []))->map(fn($name, $code) => ['value' => (string) $code, 'label' => $name])->values(),
            ],
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    // Update general settings
    public function changeGeneralSettings(Request $request)
    {
        $image = 'nullable|file|mimes:' . implode(',', self::LOGO_EXTENSIONS) . '|max:' . env('SIZE_LIMIT', 2048);
        $languages = array_keys(config('app.availableLanguages', []));

        $data = $request->validate([
            'site_name'               => 'required|string|max:100',
            'show_website'            => 'required|boolean',
            'website_redirect_url'    => 'nullable|url|max:500',
            'allow_registration'      => 'required|boolean',
            'free_plan_on_signup'     => 'required|boolean',
            'timezone'                => 'required|timezone',
            'date_time_format'        => ['required', Rule::in(array_keys(getDateTimeFormats()))],
            'languages'               => 'required|array|min:1',
            'languages.*'             => [Rule::in($languages)],
            'default_language'        => ['required', Rule::in($request->input('languages', []))],
            'currency'                => ['required', Rule::exists('currencies', 'iso_code')->where('status', 1)],
            'currency_format'         => ['required', Rule::in(self::CURRENCY_FORMATS)],
            'currency_decimals_place' => 'required|integer|between:0,4',
            'image_limit'             => 'required|integer|min:1024|max:1048576',
            'share_content'           => 'required|string|max:2000',
            'site_logo'               => $image,
            'site_logo_light'         => $image,
            'favi_icon'               => $image,
        ], [
            'default_language.in'              => __('The default language must be one of the enabled languages.'),
        ], [
            'site_name'               => __('website name'),
            'website_redirect_url'    => __('redirect URL'),
            'date_time_format'        => __('date format'),
            'default_language'        => __('default language'),
            'currency_format'         => __('number format'),
            'currency_decimals_place' => __('decimal places'),
            'image_limit'             => __('upload limit'),
            'share_content'           => __('share message'),
            'site_logo'               => __('logo'),
            'site_logo_light'         => __('logo for dark backgrounds'),
            'favi_icon'               => __('favicon'),
        ]);

        $showWebsite = $request->boolean('show_website');

        // The redirect must leave this site, otherwise visitors loop back here
        if (! $showWebsite && ! empty($data['website_redirect_url']) && $this->isSameSite($data['website_redirect_url'])) {
            return back()->withErrors(['website_redirect_url' => __('Use an address on another website. Pointing to this site would send visitors in a loop.')]);
        }

        $updates = [
            'show_website'                       => $showWebsite ? 'yes' : 'no',
            'website_redirect_url'               => $data['website_redirect_url'] ?? '',
            'registration_page'                  => $request->boolean('allow_registration') ? '0' : '1',
            'activate_plan_during_registeration' => $request->boolean('free_plan_on_signup') ? '1' : '0',
            'timezone'                           => $data['timezone'],
            'default_language'                   => $data['default_language'],
            'date_time_format'                   => $data['date_time_format'],
            'currency'                           => $data['currency'],
            'currency_format_type'               => $data['currency_format'],
            'currency_decimals_place'            => (string) $data['currency_decimals_place'],
            'share_content'                      => $data['share_content'],
            'site_name'                          => $data['site_name'],
        ];

        foreach ($updates as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }

        $this->updateLanguages($data['languages'], $data['default_language']);

        $this->updateEnvFile('TIMEZONE', $data['timezone']);
        $this->updateEnvFile('APP_NAME', '"' . str_replace(["'", '"'], '', $data['site_name']) . '"');
        $this->updateEnvFile('SIZE_LIMIT', (string) $data['image_limit']);

        $setting = Setting::find(1);
        $changes = ['site_name' => $data['site_name']];

        if ($request->hasFile('site_logo')) {
            StorageHelper::deleteFile($setting?->site_logo);
            $changes['site_logo'] = StorageHelper::uploadImage($request->file('site_logo'), 'images/web/elements');
        }

        if ($request->hasFile('favi_icon')) {
            StorageHelper::deleteFile($setting?->favicon);
            $changes['favicon'] = StorageHelper::uploadImage($request->file('favi_icon'), 'images/web/elements');
        }

        Setting::where('id', 1)->update($changes);

        // The logo for dark backgrounds always lives at public/img/logo-light.<ext>
        if ($request->hasFile('site_logo_light')) {
            foreach (self::LOGO_EXTENSIONS as $ext) {
                File::delete(public_path('img/logo-light.' . $ext));
            }
            $file = $request->file('site_logo_light');
            $file->move(public_path('img'), 'logo-light.' . strtolower($file->getClientOriginalExtension()));
        }

        return back()->with('success', __('Settings saved.'));
    }

    private function lightLogoPath(): ?string
    {
        foreach (self::LOGO_EXTENSIONS as $ext) {
            if (file_exists($path = public_path('img/logo-light.' . $ext))) {
                return $path;
            }
        }

        return null;
    }

    private function isSameSite(?string $url): bool
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        $hosts = [strtolower(request()->getHost()), strtolower((string) parse_url(url('/'), PHP_URL_HOST))];

        if ($host === '' || in_array($host, $hosts, true)) {
            return true;
        }

        // On a local server, localhost and 127.0.0.1 are this site too
        $local = ['127.0.0.1', 'localhost'];

        return in_array($hosts[0], $local, true) && in_array($host, $local, true);
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
    /**
     * server:   every Laravel cache in one go: application cache, compiled
     *           services/packages, config, events, routes and compiled views.
     *           Caches are not rebuilt because the app reads env() outside config files.
     * browser:  Clear-Site-Data tells this browser to drop its HTTP cache for the site.
     * sessions: sign everyone out, including the admin who asked.
     * A plain POST (old Blade sidebar) clears the server cache only.
     */
    public function clearCache(Request $request)
    {
        $browser = $request->boolean('browser');
        $sessions = $request->boolean('sessions');
        $server = $request->boolean('server') || (! $browser && ! $sessions);

        if ($server) {
            try {
                Artisan::call('optimize:clear');
            } catch (\Throwable $e) {
                report($e);

                return back()->with('failed', __('Couldn’t clear the server cache: :error', ['error' => $e->getMessage()]));
            }
        }

        if ($sessions) {
            if (! $this->clearSessions()) {
                return back()->with('failed', __('Sessions are stored with the “:driver” driver, which can’t be cleared from here.', ['driver' => config('session.driver')]));
            }

            // Everyone is signed out now: start a clean session and open the login page with a full page load
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('success', __('Cache cleared and everyone has been signed out.'));

            return $this->withClearSiteData(Inertia::location(route('login')), $browser);
        }

        $message = match (true) {
            $server && $browser => __('Server and browser cache cleared.'),
            $browser            => __('Browser cache cleared.'),
            default             => __('Server cache cleared.'),
        };

        return $this->withClearSiteData(back()->with('success', $message), $browser);
    }

    // Delete every stored session (file and database drivers)
    private function clearSessions(): bool
    {
        switch (config('session.driver')) {
            case 'file':
                foreach (File::files(config('session.files')) as $file) {
                    if ($file->getFilename() !== '.gitignore') {
                        File::delete($file->getPathname());
                    }
                }

                return true;
            case 'database':
                DB::connection(config('session.connection'))->table(config('session.table'))->delete();

                return true;
            default:
                return false;
        }
    }

    private function withClearSiteData($response, bool $browser)
    {
        if ($browser) {
            $response->headers->set('Clear-Site-Data', '"cache"');
        }

        return $response;
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

        // config/app.php changed, so drop cached config (and the rest, as before)
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // Helper function to handle file uploads
    function uploadImage($file, $folder = 'images/web/elements')
    {
        // Delegate upload to centralized StorageHelper
        return StorageHelper::uploadImage($file, $folder);
    }
}
