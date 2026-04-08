<?php

namespace App\Providers;

use App\Blog;
use App\Plan;
use App\Theme;
use App\Setting;
use App\Currency;
use App\NfcCardDesign;
use App\Services\PluginManager;
use Spatie\Health\Facades\Health;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use App\Observers\CacheClearObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Health::checks([
            // Used disk space check
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),

            // CPu load check
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),

            // Environment check
            EnvironmentCheck::new(),

            // Debug mode check
            DebugModeCheck::new(),
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('currency', function ($expression) {
            return "<?php echo currency($expression); ?>";
        });
        if (App::environment('production')) {
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);

        DB::listen(function ($query) {

            static $flushed = false;

            if ($flushed) {
                return;
            }

            $sql = strtolower($query->sql);

            if (
                str_starts_with($sql, 'save') ||
                str_starts_with($sql, 'insert') ||
                str_starts_with($sql, 'update') ||
                str_starts_with($sql, 'delete')
            ) {
                Cache::flush();
                $flushed = true;
            }
        });
    }
}
