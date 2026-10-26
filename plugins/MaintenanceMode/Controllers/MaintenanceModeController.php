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

namespace Plugins\MaintenanceMode\Controllers;

use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Maintenance mode (php artisan down/up with a secret bypass URL in
 * MAINTENANCE_SECRET_CODE). Page: plugins/MaintenanceMode/resources/js/settings.tsx.
 */
class MaintenanceModeController extends Controller
{
    // Index
    public function index()
    {
        $secret = (string) env('MAINTENANCE_SECRET_CODE', '');

        return PluginPage::render('MaintenanceMode', 'settings', [
            'enabled'   => app()->isDownForMaintenance(),
            // Opening this link lets you use the site while it's in maintenance
            'bypassUrl' => app()->isDownForMaintenance() && $secret !== '' ? url('/' . $secret) : null,
        ]);
    }

    public function update(Request $request)
    {
        // === Maintenance Mode ===
        $request->validate(['maintenance_mode' => 'required|boolean']);

        $envFile = base_path('.env');

        if (! $request->boolean('maintenance_mode')) {
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
                $message = __('Maintenance mode is off. Your website is live.');
            } catch (\Exception $e) {
                // Error message
                $status = 'failed';
                $message = __('Couldn’t turn off maintenance mode.');
            }

            return back()->with($status, $message);
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
                return back()->with('failed', __('Couldn’t turn on maintenance mode.'));
            }

            // Redirect to the home page with the secret key
            // Full page load: the secret URL sets your bypass cookie, then you're sent to the site
            return Inertia::location(url('/' . $secret));
        }
    }
}
