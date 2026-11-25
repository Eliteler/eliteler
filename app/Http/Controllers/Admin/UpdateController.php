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
use App\Services\EnvironmentService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use ZipArchive;

/**
 * Software updates through the NativeCode license server
 * (verify.nativecode.in): check the license and latest version, then
 * download, unpack, migrate, and deploy the update into the project.
 *
 * Dynamically scales from small patches (2 MB / 50 files) to major releases
 * (200 MB+ / 50,000+ files) via phased micro-batched AJAX execution.
 * Completely eliminates Nginx 60s fastcgi_read_timeout and Linux OOM killer crashes
 * on 1 Core, 2 GB RAM VPS and shared hosting environments.
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
            'result'         => Inertia::defer(fn() => $this->lookup(config('app.code'), $this->config('activation_email_address'))),
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

    /**
     * Update Code handler.
     * Supports phased micro-batch execution ('download', 'extract-init', 'extract-batch',
     * 'migrate', 'deploy-init', 'deploy-batch', 'deploy-finalize') to handle packages
     * from 2 MB up to 200 MB+ seamlessly without Nginx 60s timeouts or RAM crashes,
     * while preserving legacy sequential execution when no step is specified.
     */
    public function updateCode(Request $request)
    {
        @set_time_limit(9000);
        @ini_set('max_execution_time', '9000');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'email'       => 'required|email|max:191',
            'app_version' => 'required|string|max:20',
            'step'        => 'nullable|string|in:download,extract-init,extract-batch,extract,migrate,deploy-init,deploy-batch,deploy-finalize,deploy,all',
            'offset'      => 'nullable|integer|min:0',
            'batch_size'  => 'nullable|integer|min:10|max:10000',
        ]);

        $step = $request->input('step', 'all');
        $email = $request->email;
        $targetVersion = trim($request->app_version);
        $filecode = str_replace('.', '', $targetVersion);
        $offset = (int) $request->input('offset', 0);
        $batchSize = (int) $request->input('batch_size', 1000);

        DB::table('config')->where('config_key', 'activation_email_address')->update([
            'config_value' => $email,
        ]);

        $updatesDir = storage_path('app/updates');
        if (!is_dir($updatesDir)) {
            @mkdir($updatesDir, 0755, true);
        }

        $zipPath      = $updatesDir . DIRECTORY_SEPARATOR . "update_{$filecode}.zip";
        $stagingDir   = $updatesDir . DIRECTORY_SEPARATOR . "staging_{$filecode}";
        $manifestPath = $updatesDir . DIRECTORY_SEPARATOR . "manifest_{$filecode}.json";

        return match ($step) {
            'download'        => $this->stepDownload($request, $zipPath),
            'extract-init'    => $this->stepExtractInit($zipPath, $stagingDir),
            'extract-batch'   => $this->stepExtractBatch($zipPath, $stagingDir, $offset, $batchSize),
            'extract'         => $this->stepExtract($zipPath, $stagingDir),
            'migrate'         => $this->stepMigrate($stagingDir, $targetVersion, $filecode),
            'deploy-init'     => $this->stepDeployInit($stagingDir, $manifestPath, $zipPath, $targetVersion, $filecode),
            'deploy-batch'    => $this->stepDeployBatch($stagingDir, $manifestPath, $offset, $batchSize ?: 1500),
            'deploy-finalize' => $this->stepDeployFinalize($stagingDir, $manifestPath, $zipPath, $targetVersion, $filecode),
            'deploy'          => $this->stepDeploy($stagingDir, $zipPath, $targetVersion, $filecode),
            default           => $this->runSequentialUpdate($request, $zipPath, $stagingDir, $targetVersion, $filecode),
        };
    }

    /**
     * Step 1: Download update package directly to disk using Guzzle sink (low RAM).
     */
    private function stepDownload(Request $request, string $zipPath)
    {
        $server_name = AvailableVersion::serverName();

        try {
            $client = new Client([
                'timeout' => 9000,
            ]);
            $res = $client->post('https://verify.nativecode.in/update-code', [
                'headers' => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (compatible; UpdateChecker/1.0)',
                ],
                'form_params' => [
                    'purchase_code' => config('app.code'),
                    'server_name'   => $server_name,
                    'version'       => $this->config('app_version'),
                    'email'         => $request->email,
                ],
                'sink' => $zipPath,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => trans('The update server could not be reached. Please verify your connection or try again later.'),
            ], 500);
        }

        if ($res->getStatusCode() !== 200) {
            $resp_data = @json_decode(@file_get_contents($zipPath), true);
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => $resp_data['message'] ?? trans('License key verification failed!'),
            ], 422);
        }

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => trans('The downloaded update package is empty. Please retry.'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'step'    => 'download',
            'message' => trans('Update package downloaded successfully.'),
        ]);
    }

    /**
     * Step 2a: Initialize Extraction.
     * For small packages (e.g. 2 MB / <= 500 files), extracts immediately in 1 request.
     * For large packages (40 MB to 200 MB+), prepares iterative micro-batching.
     */
    private function stepExtractInit(string $zipPath, string $stagingDir)
    {
        if (!file_exists($zipPath)) {
            return response()->json([
                'success' => false,
                'message' => trans('Update package not found. Please restart download.'),
            ], 404);
        }

        if (!is_dir($stagingDir)) {
            @mkdir($stagingDir, 0755, true);
        }

        $zip = new ZipArchive();
        $out = $zip->open($zipPath);

        if ($out !== true) {
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => trans('The downloaded update archive is invalid or corrupted.'),
            ], 500);
        }

        $totalFiles = $zip->numFiles;

        if ($totalFiles === 0) {
            $zip->close();
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => trans('The downloaded update archive is empty.'),
            ], 500);
        }

        // Fast path for small packages (<= 500 files)
        if ($totalFiles <= 500) {
            $extracted = $zip->extractTo($stagingDir);
            $zip->close();
            @unlink($zipPath);

            if (!$extracted) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Installation failed during file extraction.'),
                ], 500);
            }

            return response()->json([
                'success'     => true,
                'step'        => 'extract',
                'done'        => true,
                'total_files' => $totalFiles,
                'extracted'   => $totalFiles,
                'percent'     => 100,
                'message'     => trans('Update files unpacked successfully.'),
            ]);
        }

        $zip->close();

        return response()->json([
            'success'     => true,
            'step'        => 'extract',
            'done'        => false,
            'total_files' => $totalFiles,
            'batch_size'  => 1000,
            'offset'      => 0,
            'percent'     => 0,
            'message'     => trans('Beginning batched extraction...'),
        ]);
    }

    /**
     * Step 2b: Extract a single micro-batch of entries from the ZIP archive.
     */
    private function stepExtractBatch(string $zipPath, string $stagingDir, int $offset, int $batchSize)
    {
        if (!file_exists($zipPath)) {
            return response()->json([
                'success' => false,
                'message' => trans('Update package not found. Please restart download.'),
            ], 404);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => trans('Failed to open update archive for extraction.'),
            ], 500);
        }

        $totalFiles = $zip->numFiles;
        $batchSize  = $batchSize > 0 ? $batchSize : 1000;
        $limit      = min($offset + $batchSize, $totalFiles);

        $entries = [];
        for ($i = $offset; $i < $limit; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat && isset($stat['name'])) {
                $entries[] = $stat['name'];
            }
        }

        if (!empty($entries)) {
            $zip->extractTo($stagingDir, $entries);
        }

        $isDone = ($limit >= $totalFiles);
        $zip->close();

        if ($isDone) {
            // Delete zip immediately to reclaim disk space before deployment
            @unlink($zipPath);
        }

        $pct = $totalFiles > 0 ? round(($limit / $totalFiles) * 100, 1) : 100;

        return response()->json([
            'success'     => true,
            'step'        => 'extract',
            'done'        => $isDone,
            'total_files' => $totalFiles,
            'extracted'   => $limit,
            'offset'      => $limit,
            'percent'     => $pct,
            'message'     => $isDone ? trans('Update files unpacked successfully.') : trans('Unpacking files...'),
        ]);
    }

    /**
     * Legacy Step 2: Unpack entire update archive at once (fallback).
     */
    private function stepExtract(string $zipPath, string $stagingDir)
    {
        if (!file_exists($zipPath)) {
            return response()->json([
                'success' => false,
                'message' => trans('Update package not found. Please restart download.'),
            ], 404);
        }

        if (!is_dir($stagingDir)) {
            @mkdir($stagingDir, 0755, true);
        }

        $unzip = new ZipArchive();
        $out = $unzip->open($zipPath);

        if ($out !== true) {
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => trans('The downloaded update archive is invalid or corrupted.'),
            ], 500);
        }

        $extracted = $unzip->extractTo($stagingDir);
        $unzip->close();
        @unlink($zipPath);

        if (!$extracted) {
            return response()->json([
                'success' => false,
                'message' => trans('Installation failed during file extraction.'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'step'    => 'extract',
            'done'    => true,
            'message' => trans('Update files unpacked successfully.'),
        ]);
    }

    /**
     * Step 3: Run Database Migrations and updater routine before deploying files.
     */
    private function stepMigrate(string $stagingDir, string $targetVersion, string $filecode)
    {
        $stagingUpdater = $stagingDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . "GoBizUpdater{$filecode}.php";
        $liveUpdater    = app_path("Classes/GoBizUpdater{$filecode}.php");

        $updaterFile = file_exists($stagingUpdater) ? $stagingUpdater : (file_exists($liveUpdater) ? $liveUpdater : null);

        if ($updaterFile) {
            require_once $updaterFile;
            $dynamicClassName = "\\App\\Classes\\GoBizUpdater{$filecode}";

            if (class_exists($dynamicClassName)) {
                try {
                    $dynamicClass = new $dynamicClassName();
                    if (method_exists($dynamicClass, 'runUpdate')) {
                        $dynamicClass->runUpdate();
                    }
                } catch (\Throwable $e) {
                    Log::error("GoBiz updater routine {$filecode} exception: " . $e->getMessage());
                }
            }
        }

        // Update app_version in database config table
        DB::table('config')->where('config_key', 'app_version')->update([
            'config_value' => $targetVersion,
            'updated_at'   => now(),
        ]);

        // Safely update APP_VERSION in .env without risking blank screen / syntax corruption
        $this->updateEnvAppVersion($targetVersion);

        return response()->json([
            'success' => true,
            'step'    => 'migrate',
            'message' => trans('Database schema and settings updated.'),
        ]);
    }

    /**
     * Step 4a: Initialize Deployment.
     * Scans staging folder and builds manifest.
     * For small packages (<= 300 files), copies immediately in 1 step.
     */
    private function stepDeployInit(string $stagingDir, string $manifestPath, string $zipPath, string $targetVersion, string $filecode)
    {
        if (!is_dir($stagingDir)) {
            return response()->json([
                'success' => false,
                'message' => trans('Staging directory not found. Please restart the update.'),
            ], 404);
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($stagingDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $stagingLen = strlen(rtrim($stagingDir, '/\\')) + 1;
        foreach ($iterator as $item) {
            $rel = substr($item->getPathname(), $stagingLen);
            $relNorm = str_replace('\\', '/', $rel);

            // Never overwrite .env, storage directory, or git metadata
            if ($relNorm === '.env' || str_starts_with($relNorm, 'storage/') || str_starts_with($relNorm, '.git/')) {
                continue;
            }

            $files[] = [
                'rel'    => $rel,
                'is_dir' => $item->isDir(),
            ];
        }

        $totalFiles = count($files);

        // Fast path for small packages (<= 300 files)
        if ($totalFiles <= 300) {
            $this->copyDirectory($stagingDir, base_path());
            $this->deleteDirectory($stagingDir);
            @unlink($zipPath);
            @unlink($manifestPath);

            $liveUpdater = app_path("Classes/GoBizUpdater{$filecode}.php");
            if (file_exists($liveUpdater)) {
                @unlink($liveUpdater);
            }

            $this->clearCaches();

            return response()->json([
                'success'     => true,
                'step'        => 'deploy',
                'done'        => true,
                'total_files' => $totalFiles,
                'deployed'    => $totalFiles,
                'percent'     => 100,
                'message'     => trans('Hurray! The latest version was updated successfully.'),
            ])->header('Clear-Site-Data', '"cache"');
        }

        // Save manifest to disk for large packages
        @file_put_contents($manifestPath, json_encode($files));

        return response()->json([
            'success'     => true,
            'step'        => 'deploy',
            'done'        => false,
            'total_files' => $totalFiles,
            'batch_size'  => 1500,
            'offset'      => 0,
            'percent'     => 0,
            'message'     => trans('Beginning batched file deployment...'),
        ]);
    }

    /**
     * Step 4b: Deploy a single micro-batch of files from staging to base_path.
     */
    private function stepDeployBatch(string $stagingDir, string $manifestPath, int $offset, int $batchSize)
    {
        if (!file_exists($manifestPath)) {
            return response()->json([
                'success' => false,
                'message' => trans('Deployment manifest not found. Please restart the update.'),
            ], 404);
        }

        $manifestData = json_decode(@file_get_contents($manifestPath), true) ?: [];
        $totalFiles   = count($manifestData);
        $batchSize    = $batchSize > 0 ? $batchSize : 1500;
        $limit        = min($offset + $batchSize, $totalFiles);

        for ($i = $offset; $i < $limit; $i++) {
            $item  = $manifestData[$i];
            $rel   = is_array($item) ? $item['rel'] : $item;
            $isDir = is_array($item) ? ($item['is_dir'] ?? false) : false;

            $src = $stagingDir . DIRECTORY_SEPARATOR . $rel;
            $dst = base_path($rel);

            if ($isDir || is_dir($src)) {
                if (!is_dir($dst)) {
                    @mkdir($dst, 0755, true);
                }
            } elseif (file_exists($src)) {
                $dir = dirname($dst);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                @copy($src, $dst);
            }
        }

        $isDone = ($limit >= $totalFiles);
        $pct    = $totalFiles > 0 ? round(($limit / $totalFiles) * 100, 1) : 100;

        return response()->json([
            'success'     => true,
            'step'        => 'deploy',
            'done'        => $isDone,
            'total_files' => $totalFiles,
            'deployed'    => $limit,
            'offset'      => $limit,
            'percent'     => $pct,
            'message'     => $isDone ? trans('All files deployed successfully.') : trans('Deploying files...'),
        ]);
    }

    /**
     * Step 4c: Finalize Deployment.
     * Cleans staging directory, removes updater routines, and flushes caches.
     */
    private function stepDeployFinalize(string $stagingDir, string $manifestPath, string $zipPath, string $targetVersion, string $filecode)
    {
        if (is_dir($stagingDir)) {
            $this->deleteDirectory($stagingDir);
        }

        @unlink($zipPath);
        @unlink($manifestPath);

        // Delete updater class if present in app/Classes
        $liveUpdater = app_path("Classes/GoBizUpdater{$filecode}.php");
        if (file_exists($liveUpdater)) {
            @unlink($liveUpdater);
        }

        // Ensure .env has latest version and flush caches
        $this->updateEnvAppVersion($targetVersion);

        // Flush caches
        $this->clearCaches();

        return response()->json([
            'success' => true,
            'step'    => 'deploy',
            'done'    => true,
            'message' => trans('Hurray! The latest version was updated successfully.'),
        ])->header('Clear-Site-Data', '"cache"');
    }

    /**
     * Legacy Step 4: Deploy files from staging to base_path at once (fallback).
     */
    private function stepDeploy(string $stagingDir, string $zipPath, string $targetVersion, string $filecode)
    {
        if (is_dir($stagingDir)) {
            $this->copyDirectory($stagingDir, base_path());
            $this->deleteDirectory($stagingDir);
        }

        @unlink($zipPath);

        // Delete updater class if present in app/Classes
        $liveUpdater = app_path("Classes/GoBizUpdater{$filecode}.php");
        if (file_exists($liveUpdater)) {
            @unlink($liveUpdater);
        }

        // Ensure .env has latest version and flush caches
        $this->updateEnvAppVersion($targetVersion);

        // Flush caches
        $this->clearCaches();

        return response()->json([
            'success' => true,
            'step'    => 'deploy',
            'message' => trans('Hurray! The latest version was updated successfully.'),
        ])->header('Clear-Site-Data', '"cache"');
    }

    /**
     * Fallback / Legacy sequential execution (if step is not specified).
     */
    private function runSequentialUpdate(Request $request, string $zipPath, string $stagingDir, string $targetVersion, string $filecode)
    {
        $dRes = $this->stepDownload($request, $zipPath);
        $dData = $dRes->getData();
        if (!$dData || empty($dData->success)) {
            return $dRes;
        }

        $eRes = $this->stepExtract($zipPath, $stagingDir);
        $eData = $eRes->getData();
        if (!$eData || empty($eData->success)) {
            return $eRes;
        }

        $mRes = $this->stepMigrate($stagingDir, $targetVersion, $filecode);
        $mData = $mRes->getData();
        if (!$mData || empty($mData->success)) {
            return $mRes;
        }

        return $this->stepDeploy($stagingDir, $zipPath, $targetVersion, $filecode);
    }

    /**
     * Recursively copy files without overwriting customer .env or storage directory.
     */
    private function copyDirectory(string $src, string $dst): void
    {
        $dir = @opendir($src);
        if (!$dir) {
            return;
        }

        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $src . DIRECTORY_SEPARATOR . $file;
            $dstPath = $dst . DIRECTORY_SEPARATOR . $file;

            // Never overwrite .env, storage files, or git metadata
            $rel = strtolower(str_replace('\\', '/', substr($dstPath, strlen(base_path()) + 1)));
            if ($rel === '.env' || $rel === 'storage' || str_starts_with($rel, 'storage/') || str_starts_with($rel, '.git/')) {
                continue;
            }

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                @copy($srcPath, $dstPath);
            }
        }

        closedir($dir);
    }

    /**
     * Recursively delete directory.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = @scandir($dir);
        if (!$items) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }

        @rmdir($dir);
    }

    /**
     * Safely updates APP_VERSION in .env via EnvironmentService.
     */
    private function updateEnvAppVersion(string $version): void
    {
        EnvironmentService::setAppVersion($version);
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
