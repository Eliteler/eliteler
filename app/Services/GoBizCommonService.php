<?php

namespace App\Services;

use App\Blog;
use App\Plan;
use App\Theme;
use App\Setting;
use App\Currency;
use App\NfcCardDesign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class GoBizCommonService
{
    // All Config
    public static function config()
    {
        return Cache::remember(
            'config_all',
            60,
            fn() =>
            DB::table('config')->get()
        );
    }

    // All Plans
    public static function plans()
    {
        return Cache::remember(
            'public_plans',
            60,
            fn() =>
            Plan::where('status', 1)->where('is_private', '0')->get()
        );
    }

    // Settings
    public static function settings()
    {
        return Cache::remember('settings_main', 60, function () {
            $settings = Setting::where('status', 1)->first();

            if (!$settings) {
                return null;
            }

            $settings['google_configuration'] = [
                'GOOGLE_ENABLE'        => env('GOOGLE_ENABLE'),
                'GOOGLE_CLIENT_ID'     => env('GOOGLE_CLIENT_ID'),
                'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET'),
                'GOOGLE_REDIRECT'      => env('GOOGLE_REDIRECT'),
            ];

            $settings['recaptcha_configuration'] = [
                'RECAPTCHA_ENABLE'     => env('RECAPTCHA_ENABLE'),
                'RECAPTCHA_SITE_KEY'   => env('RECAPTCHA_SITE_KEY'),
                'RECAPTCHA_SECRET_KEY' => env('RECAPTCHA_SECRET_KEY'),
            ];

            return $settings;
        });
    }

    // Latest Blogs
    public static function blogs()
    {
        return Blog::where('status', 1)
            ->with('blogCategory')
            ->latest()
            ->limit(3)
            ->get();
    }

    // All Blogs
    public static function allBlogs()
    {
        return Blog::where('status', 1)
            ->with('blogCategory')
            ->latest()
            ->paginate(6);
    }

    public static function nfcCards()
    {
        return Cache::remember(
            'nfc_cards',
            60,
            fn() =>
            NfcCardDesign::where('status', 1)->get()
        );
    }

    // All Themes
    public static function themes()
    {
        return Cache::remember(
            'themes_preview',
            60,
            fn() =>
            Theme::whereBetween('theme_id', ['588969111125', '588969111148'])
                ->where('theme_id', '!=', '588969111147')
                ->where('status', 1)
                ->get()
        );
    }

    // Referral Cookie
    public static function referralCookie($request)
    {
        if ($request->has('ref')) {
            Cookie::queue('referral_code', $request->ref, 10);
        }
    }

    // Directory
    public static function directorySettings()
    {
        if (is_dir(base_path('plugins/Directory'))) {
            if (! DB::table('information_schema.tables')
                ->where('table_schema', config('database.connections.mysql.database'))
                ->where('table_name', 'directory_settings')
                ->exists()) {

                DB::statement("CREATE TABLE `directory_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `directory` BOOLEAN DEFAULT 1,
            `default_enable_directory_customers` BOOLEAN DEFAULT 1,
            `status` BOOLEAN DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");

                // Insert default values
                DB::table('directory_settings')->insert([
                    'directory'        => 1,
                    'default_enable_directory_customers' => 1,
                ]);
            }

            // Return directory settings
            return Cache::remember(
                'directory_settings',
                60,
                fn() =>
                DB::table('directory_settings')->first()
            );
        }
    }
 
    // Directory business cards
    public static function directoryBusinessCards($type, $location, $search, $forceEnableDirectory)
    {
        return DB::table('business_cards')
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->leftJoin('business_fields as bf', function ($join) {
                $join->on('business_cards.card_id', '=', 'bf.card_id');
            })
            ->leftJoin('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
            ->select(
                'business_cards.*',
                'users.user_id',
                'users.plan_validity',
                'themes.theme_code',
                DB::raw('(SELECT COUNT(*) FROM visitors WHERE visitors.card_id = business_cards.card_url) as views_count')
            )
            ->where('business_cards.status', 1)
            ->whereIn('business_cards.card_type', $type)
            ->whereIn('business_cards.directory_listing', $forceEnableDirectory)
            ->whereNotIn('business_cards.card_status', ['deleted', 'inactive'])
            ->where(function ($q) {
                $q->where('users.plan_validity', '>=', now())
                    ->orWhereNull('users.plan_validity');
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('business_cards.title', 'LIKE', "%{$search}%")
                        ->orWhere('business_cards.sub_title', 'LIKE', "%{$search}%")
                        ->orWhere('business_cards.card_type', 'LIKE', "%{$search}%");
                });
            })
            ->when($location, function ($q) use ($location) {
                $q->where(function ($q) use ($location) {
                    $q->where('bf.content', 'LIKE', "%{$location}%");
                });
            })
            ->distinct('business_cards.card_id')
            ->orderBy('business_cards.id', 'desc')
            ->paginate(9)
            ->appends(request()->query());
    }

    // Get currencies
    public static function currencies()
    {
        return Cache::remember(
            'currencies',
            60,
            fn() =>
            Currency::all()
        );
    }

    // Get Single config value
    public static function singleConfig($key)
    {
        return Cache::remember(
            'config_' . $key,
            60,
            fn() =>
            DB::table('config')->where('config_key', $key)->value('config_value')
        );
    }

    // Get gobiz modern config values
    public static function gobizModernConfig()
    {
        return Cache::remember(
            'gobiz_modern_config',
            60,
            fn() =>
            DB::table('gobiz_modern_config')->first()
        );
    }

    // Get gobiz original config values
    public static function gobizOriginalConfig()
    {
        return Cache::remember(
            'gobiz_original_config',
            60,
            fn() =>
            DB::table('gobiz_original_config')->first()
        );
    }

    // Get template wise page content (all)
    public static function templatePageContentAll($templateId)
    {
        return Cache::remember(
            'template_page_content_all' . $templateId,
            60,
            fn() =>
            DB::table('pages')
                ->where('template_id', $templateId)
                ->get()
        );
    }

    // Get template wise page content
    public static function templatePageContentGet($pageName, $templateId)
    {
        return Cache::remember(
            'template_page_content_get_' . $pageName . '_' . $templateId,
            60,
            fn() =>
            DB::table('pages')
                ->where('page_name', $pageName)
                ->where('template_id', $templateId)
                ->where('status', 'active')
                ->get()
        );
    }


    // Get template wise page content
    public static function templatePageContentFirst($pageName, $templateId)
    {
        return Cache::remember(
            'template_page_content_first_' . $pageName . '_' . $templateId,
            60,
            fn() =>
            DB::table('pages')
                ->where('page_name', $pageName)
                ->where('template_id', $templateId)
                ->where('status', 'active')
                ->first()
        );
    }

    // Custom page content
    public static function customPageContent($pageName, $templateId)
    {
        return Cache::remember(
            'custom_page_' . $pageName . '_' . $templateId,
            60,
            fn() =>
            DB::table('pages')
                ->where('section_title', $pageName)
                ->where('template_id', $templateId)
                ->where('status', 'active')
                ->first()
        );
    }

    // Home directory
    public static function homeDirectoryQuery($forceEnableDirectory)
    {
        return
            DB::table('business_cards')
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->select('users.user_id', 'users.plan_validity', 'business_cards.*')
            ->where('business_cards.status', 1)
            ->whereIn('business_cards.directory_listing', $forceEnableDirectory)
            ->whereNotIn('business_cards.card_status', ['deleted', 'inactive'])
            ->where(function ($q) {
                $q->where('users.plan_validity', '>=', now())
                    ->orWhereNull('users.plan_validity');
            })
            ->inRandomOrder()
            ->paginate(3)
            ->appends(request()->query());
    }
}
