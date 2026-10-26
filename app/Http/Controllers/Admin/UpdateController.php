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

use App\Classes\AvailableVersion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Software updates through the NativeCode license server
 * (verify.nativecode.in): check the license and latest version, then
 * download and unpack the update into the project.
 */
class UpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function check()
    {
        $ref = urlencode(config('app.url'));

        return Inertia::render('settings/updates', [
            'currentVersion' => (string) $this->config('app_version'),
            'purchaseCode'   => (string) config('app.code'),
            'email'          => (string) $this->config('activation_email_address'),
            // Loaded after the page shows, so a slow license server doesn't block it
            'result'         => Inertia::defer(fn () => $this->lookup(config('app.code'), $this->config('activation_email_address'))),
            'links'          => [
                'purchaseCodeHelp' => 'https://gobizsaas.com/downloads',
                'featureRequest'   => "https://gobiz.feednote.io?ref={$ref}&size=source",
                'renewSupport'     => 'https://gobizsaas.com/renew-support',
                'product'          => "https://gobizsaas.com/?ref={$ref}#pricing",
            ],
        ]);
    }

    /** Check again with the license key from .env (PURCHASE_CODE) and the email from the form (JSON for the page). */
    public function checkUpdate(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:191']);

        DB::table('config')->where('config_key', 'activation_email_address')->update(['config_value' => $data['email']]);

        return response()->json($this->lookup(config('app.code'), $data['email']));
    }

    private function lookup(?string $purchaseCode, ?string $email): array
    {
        $versions = new AvailableVersion;
        $result = $versions->lookup($purchaseCode, $email);

        // Keep the dashboard notice in step with what this page shows
        if ($result['ok']) {
            $versions->remember($result);
        }

        return $result;
    }

    private function config(string $key): ?string
    {
        return DB::table('config')->where('config_key', $key)->value('config_value');
    }

    // Update code
    public function updateCode(Request $request)
    {
        // Increase the maximum execution time to 9000 seconds
        set_time_limit(9000);
        ini_set('max_execution_time', 9000);

        // Optionally increase memory limit to prevent memory issues during unzipping
        ini_set('memory_limit', '512M');

        $resp_data = [];
        $errorMessage = trans('Something went wrong! Please contact author support team. URL: https://support.nativecode.in');

        $server_name = AvailableVersion::serverName();

        $request->validate(['email' => 'required|email|max:191', 'app_version' => 'required|string|max:20']);
        $email = $request->email;

        DB::table('config')->where('config_key', 'activation_email_address')->update([
            'config_value' => $email,
        ]);

        try {
            // Updated Guzzle timeout to prevent request timeout during download
            $client = new \GuzzleHttp\Client([
                'timeout' => 9000,
            ]);
            $res = $client->post('https://verify.nativecode.in/update-code', [
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (compatible; UpdateChecker/1.0)',
                ],
                'form_params' => [
                    'purchase_code' => config('app.code'),
                    'server_name' => $server_name,
                    'version' => $this->config('app_version'),
                    'email' => $email
                ]
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => trans('The system is already in the latest version.')
            ]);
        }

        if ($res->getStatusCode() == 200) {

            $download = uniqid();
            $zipPath = public_path($download . '.zip');

            file_put_contents($zipPath, $res->getBody());

            $unzip = new \ZipArchive();
            $out = $unzip->open($zipPath);

            if ($out === TRUE) {

                $unzip->extractTo(base_path());
                $unzip->close();

                unlink($zipPath);

                // The new files are in place: drop caches built from the old code before the updater runs
                $this->clearCaches();

                DB::table('config')->where('config_key', 'app_version')->update([
                    'config_value' => $request->app_version,
                ]);

                $filecode = str_replace(".", "", $request->app_version);

                if (file_exists(app_path("./Classes/GoBizUpdater$filecode.php"))) {

                    $baseClassName = "\App\Classes\GoBizUpdater";
                    $dynamicClassName = $baseClassName . $filecode;

                    if (class_exists($dynamicClassName)) {
                        $dynamicClass = new $dynamicClassName();
                        $dynamicClass->runUpdate();
                    }

                    unlink(app_path("./Classes/GoBizUpdater$filecode.php"));
                }

                // Again, for anything the updater cached (settings, config) on the way
                $this->clearCaches();

                // Also drop this browser's cached files, so the admin gets the new scripts and styles
                return response()->json([
                    'success' => true,
                    'message' => trans('Hurray! The latest version was updated successfully.')
                ])->header('Clear-Site-Data', '"cache"');
            } else {

                return response()->json([
                    'success' => false,
                    'message' => trans('Installation failed.')
                ]);
            }
        } else {

            $resp_data = json_decode($res->getBody(), true);

            return response()->json([
                'success' => false,
                'message' => $resp_data['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => trans('License key verification failed!')
        ]);
    }

    /**
     * Same as Clear App Cache (server): application cache, compiled
     * services/packages, config, events, routes and compiled views, plus
     * OPcache so PHP stops serving the replaced files from memory.
     */
    private function clearCaches(): void
    {
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            report($e);
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }
}
