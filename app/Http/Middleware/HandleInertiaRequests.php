<?php

namespace App\Http\Middleware;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (is_dir(base_path('plugins/ModernDashboard'))) {
            // Settings
            $settings = Setting::first();

            // Config
            $config = DB::table('config')->pluck('config_value', 'config_key');

            // google wallet module
            if (is_dir(base_path('plugins/GoogleWallet'))) {
                $google_wallet_module = true;
            } else {
                $google_wallet_module = false;
            }

            // config
            $config = DB::table('config')->pluck('config_value', 'config_key')->toArray();

            // nfc module
            if (is_dir(base_path('plugins/OrderNFCSystem'))) {
                $nfc_module = $config['nfc_order_system'] == '1' ? true : false;
            } else {
                $nfc_module = false;
            }

            // referral module
            if (is_dir(base_path('plugins/ReferralSystem'))) {
                $referral_module = $config['referral_system'] == '1' ? true : false;
            } else {
                $referral_module = false;
            }

            // directory plugin
            if (is_dir(base_path('plugins/Directory'))) {
                // fetch directory settings
                $directory_settings = DB::table('directory_settings')->first();

                // check directory_settings is available
                if ($directory_settings) {
                    // set directory listing
                    $directory_module = $directory_settings->directory == 1 ? true : false;
                    // if default, set false to the directory listing
                    if ($directory_settings->default_enable_directory_customers == 1) {
                        $directory_module = false;
                    }
                }
            } else {
                $directory_module = false;
            }

            // ai builder module
            if (is_dir(base_path('plugins/AiBuilder'))) {
                // ai builder settings
                $aibuilder_settings = DB::table('aibuilder_settings')->first();

                // check aibuilder_settings is available
                if ($aibuilder_settings) {
                    // set ai builder module
                    $aibuilder_module = $aibuilder_settings->aibuilder == 1 ? true : false;
                } else {
                    $aibuilder_module = false;
                }
            } else {
                $aibuilder_module = false;
            }

            return [
                ...parent::share($request),
                'app_data' => [
                    'app_name' => $config['site_name'],
                    'app_version' => $config['app_version'],
                    'app_logo' => url($settings->site_logo),
                    'app_logo_dark' => url('img/logo-light.png'),
                    'favicon' => url($settings->favicon),
                    'base_url' => url('/'),
                    'google_wallet_module' => $google_wallet_module,
                    'nfc_module' => $nfc_module,
                    'referral_module' => $referral_module,
                    'directory_module' => $directory_module,
                    'aibuilder_module' => $aibuilder_module,
                    'email_verification' => $config['disable_user_email_verification'] == '1' ? true : false,
                ],
                'flash' => [
                    'success' => fn() => $request->session()->get('success'),
                    'failed'  => fn() => $request->session()->get('failed'),
                ],
                'auth'  => [
                    'user' => $request->user()
                        ? [
                            'name' => $request->user()->name,
                            'email' => $request->user()->email,
                            'profile_image' => $request->user()->profile_image,
                            'mobile_number' => $request->user()->mobile_number,
                            'trial' => $request->user()->trial,
                            'plan_details' => $request->user()->plan_details,
                            'lang' => $request->user()->lang,
                            'email_verified_at' => $request->user()->email_verified_at,
                        ]
                        : null,
                ],
                'ziggy' => fn() => [
                    ...(new Ziggy)->toArray(),
                    'location' => $request->url(),
                ],
            ];
        } else {
            return [];
        }
    }
}
