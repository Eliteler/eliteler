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

namespace Templates\GoBizOriginal;

use App\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\GoBizCommonService;

class GoBizOriginalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'GoBizOriginal');

        $target = base_path('templates/GoBizOriginal/gobiz_original_assets');
        $link   = public_path('gobiz_original_assets');

        if (! file_exists($link)) {
            app('files')->link($target, $link);
        }

        $defaultConfig = (object) [
            'template_color' => 'gray',
            'theme_slider'   => 0,
            'banner_image'   => null,
            'auth_image'     => null,
            'app_action'     => 0,
            'app_heading'    => 'Your Business, In Your Pocket',
            'app_description'=> 'Control your business cards, store, and NFC tools from a single mobile app. Stay connected and never miss an opportunity.',
            'google_play_store_link' => null,
            'apple_app_store_link'   => null,
            'custom_css'     => null,
            'custom_js'      => null,
        ];

        $tableExists = Schema::hasTable('gobiz_original_config');

        if (! $tableExists) {
            Schema::create('gobiz_original_config', function (Blueprint $table) {
                $table->id();
                $table->string('template_color');
                $table->boolean('theme_slider');
                $table->string('banner_image');
                $table->string('auth_image');
                $table->boolean('app_action')->default(false);
                $table->text('app_heading')->nullable();
                $table->text('app_description')->nullable();
                $table->text('google_play_store_link')->nullable();
                $table->text('apple_app_store_link')->nullable();
                $table->text('custom_css')->nullable();
                $table->text('custom_js')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });

            $settings = GoBizCommonService::settings();

            DB::table('gobiz_original_config')->insert([
                'template_color' => getConfigData('app_theme'),
                'theme_slider'   => getConfigData('show_home_slider'),
                'banner_image'   => getConfigData('primary_image'),
                'auth_image'     => getConfigData('secondary_image'),
                'app_action'     => getConfigData('app_action'),
                'app_heading'    => getConfigData('app_heading'),
                'app_description'=> getConfigData('app_description'),
                'google_play_store_link' => getConfigData('google_play_store_link'),
                'apple_app_store_link'   => getConfigData('apple_app_store_link'),
                'custom_css'     => $settings->custom_css ?? null,
                'custom_js'      => $settings->custom_scripts ?? null,
            ]);

            DB::table('config')->insertOrIgnore([
                'config_key'   => 'web_template',
                'config_value' => 'GoBizOriginal',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $tableExists = true;
        }

        if (getConfigData('web_template') !== 'GoBizOriginal') {
            return;
        }

        view()->composer('GoBizOriginal::*', function ($view) use ($defaultConfig, $tableExists) {
            try {
                $config = $tableExists
                    ? GoBizCommonService::gobizOriginalConfig()
                    : null;

                $view->with('template_config', $config ?: $defaultConfig);
            } catch (\Throwable $e) {
                $view->with('template_config', $defaultConfig);
            }
        });
    }

    public function register() {}
}
