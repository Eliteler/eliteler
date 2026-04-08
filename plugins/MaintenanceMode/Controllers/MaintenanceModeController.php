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

namespace Plugins\MaintenanceMode\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MaintenanceModeController extends Controller
{
    // Index
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // return view
        return view()->file(base_path('plugins/MaintenanceMode/Views/index.blade.php'), compact('settings', 'config'));
    }

    // Update Maintenance Mode Settings
    public function update(Request $request)
    {
        // === Maintenance Mode ===
        $envFile = base_path('.env');

        if ($request->maintenance_mode == "0" || $request->maintenance_mode == null) {
            // The app is in maintenance mode
            try {
                // Clear the MAINTENANCE_SECRET_CODE in the .env file
                $env = file_get_contents($envFile);

                // Remove existing MAINTENANCE_SECRET_CODE
                $env = preg_replace('/MAINTENANCE_SECRET_CODE=.*/', 'MAINTENANCE_SECRET_CODE=', $env);
                file_put_contents($envFile, $env);

                // Bring the application up
                Artisan::call('up');

                // Clear the cache
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');

                // Success message
                $status = 'success';
                $message = trans('Maintenance Mode Disabled');
            } catch (\Exception $e) {
                // Error message
                $status = 'failed';
                $message = trans('Failed to Disable Maintenance Mode');
            }

            return redirect()->route('admin.plugin.maintenance-mode')->with($status, trans($message));
        } else {
            // Generate a new secret key
            $secret = Str::uuid();

            try {
                $env = file_get_contents($envFile);

                // Check if MAINTENANCE_SECRET_CODE already exists
                if (strpos($env, 'MAINTENANCE_SECRET_CODE=') !== false) {
                    // Update existing MAINTENANCE_SECRET_CODE
                    $env = preg_replace('/MAINTENANCE_SECRET_CODE=.*/', 'MAINTENANCE_SECRET_CODE=' . $secret, $env);
                } elseif (strpos($env, 'PURCHASE_CODE=') !== false) {
                    // Insert MAINTENANCE_SECRET_CODE after PURCHASE_CODE
                    $env = preg_replace('/(PURCHASE_CODE=.*\n)/', "$1MAINTENANCE_SECRET_CODE=" . $secret . "\n", $env);
                } else {
                    // Add PURCHASE_CODE and MAINTENANCE_SECRET_CODE at the end if PURCHASE_CODE is missing
                    $env .= "\nPURCHASE_CODE=\nMAINTENANCE_SECRET_CODE=" . $secret;
                }
                file_put_contents($envFile, $env);

                // Bring the application down with a maintenance page and the secret
                Artisan::call('down', [
                    '--render' => 'maintenance',
                    '--secret' => $secret
                ]);

                // Clear the cache
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
            } catch (\Exception $e) {
                return redirect()->route('admin.plugin.maintenance-mode')->with('failed', trans('Failed to Enable Maintenance Mode'));
            }

            // Redirect to the home page with the secret key
            return redirect()->to('/' . $secret);
        }
    }
}
