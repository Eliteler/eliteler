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

namespace Modules\TranslationManager\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\TranslationManager\app\Services\MissingTranslationService;

class SyncMissingTranslationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Give the job plenty of room — this is no longer bound by the
     * web server's 30s request timeout since it runs on the queue worker.
     * Adjusted to 120 seconds to prevent shared hosting process freezes.
     */
    public int $timeout = 120;

    public int $tries = 1;

    protected string $sourceLocale;
    protected string $targetLocale;
    protected string $statusKey;

    public function __construct(string $sourceLocale, string $targetLocale)
    {
        $this->sourceLocale = $sourceLocale;
        $this->targetLocale = $targetLocale;
        $this->statusKey = self::statusCacheKey($targetLocale);

        // Log::info("SyncMissingTranslationsJob constructed: {$sourceLocale} -> {$targetLocale}");
    }

    public static function statusCacheKey(string $targetLocale): string
    {
        return "translation-manager:sync-status:{$targetLocale}";
    }

    protected static function jobsFilePath(): string
    {
        return storage_path('app/translation-jobs.json');
    }

    public function handle(MissingTranslationService $missingService): void
    {
        // Log::info("SyncMissingTranslationsJob STARTED: {$this->sourceLocale} -> {$this->targetLocale}");

        $startedAt = microtime(true);

        $this->setStatus([
            'state' => 'running',
            'message' => trans('Translating missing keys…'),
        ]);

        try {
            // Log::info("Detecting missing keys for {$this->targetLocale}…");

            // Execute translation batch with a 50-second cutoff for shared hosting
            $isCompleted = $missingService->syncMissingKeys(
                $this->sourceLocale,
                $this->targetLocale,
                function (int $done, int $total) use ($startedAt) {
                    $elapsed = microtime(true) - $startedAt;
                    $avgPerKey = $done > 0 ? $elapsed / $done : 0;
                    $remaining = max($total - $done, 0);
                    $estimatedSecondsLeft = (int) round($avgPerKey * $remaining);
                    $etaLabel = $this->formatDuration($estimatedSecondsLeft);

                    // Log::info("SyncMissingTranslationsJob progress [{$this->targetLocale}]: {$done}/{$total} done, ETA {$etaLabel}");

                    $this->setStatus([
                        'state' => 'running',
                        'message' => "Translating {$done}/{$total} keys (est. {$etaLabel} remaining)",
                        'done' => $done,
                        'total' => $total,
                        'estimated_seconds_remaining' => $estimatedSecondsLeft,
                    ]);
                },
                50 // Run max 50 seconds before yielding
            );

            if ($isCompleted) {
                // Log::info("SyncMissingTranslationsJob COMPLETED successfully for {$this->targetLocale}");

                $this->setStatus([
                    'state' => 'completed',
                    'message' => __('Missing translations synced successfully.'),
                ]);
            } else {
                // Log::info("SyncMissingTranslationsJob batch paused due to time limit. Re-dispatching next batch for {$this->targetLocale}");

                $this->setStatus([
                    'state' => 'running',
                    'message' => __('Batch limit reached. Continuing remaining keys in a new job…'),
                ]);

                // Dispatch a new job to continue remaining missing translations
                self::dispatch($this->sourceLocale, $this->targetLocale);
            }
        } catch (\Throwable $e) {
            // Log::error("SyncMissingTranslationsJob FAILED for {$this->targetLocale}: " . $e->getMessage(), [
            //     'exception' => $e,
            // ]);

            $this->setStatus([
                'state' => 'failed',
                'message' => __('Sync failed: ' . $e->getMessage()),
            ]);
        }
    }

    /**
     * Called automatically by Laravel if the job fails outside the
     * try/catch above — e.g. it exceeds $timeout and gets killed.
     */
    public function failed(\Throwable $exception): void
    {
        // Log::error("SyncMissingTranslationsJob failed() callback for {$this->targetLocale}: " . $exception->getMessage());

        $status = [
            'state' => 'failed',
            'message' => __('Sync failed: ' . $exception->getMessage()),
        ];

        Cache::put($this->statusKey, $status, now()->addMinutes(10));

        try {
            $this->writeStatusToJsonFile($status);
        } catch (\Throwable $e) {
            // Log::warning('Failed to write translation-jobs.json in failed() callback: ' . $e->getMessage());
        }
    }

    /**
     * Writes status to Cache (source of truth for the UI/polling endpoint)
     * and to a JSON file on disk (durable history). The JSON write is
     * never allowed to fail the job itself.
     */
    protected function setStatus(array $status): void
    {
        // Log::info('SyncMissingTranslationsJob status update', array_merge(
        //     ['target_locale' => $this->targetLocale, 'source_locale' => $this->sourceLocale],
        //     $status
        // ));

        Cache::put($this->statusKey, $status, now()->addHour());

        try {
            $this->writeStatusToJsonFile($status);
        } catch (\Throwable $e) {
            // Log::warning('Failed to write translation-jobs.json: ' . $e->getMessage());
        }
    }

    protected function writeStatusToJsonFile(array $status): void
    {
        $file = self::jobsFilePath();
        $dir = dirname($file);

        if (!is_dir($dir)) {
            // Log::info("Creating missing directory for job file: {$dir}");
            mkdir($dir, 0777, true);
        }

        $handle = fopen($file, 'c+');
        if (!$handle) {
            throw new \RuntimeException("Unable to open {$file} for writing.");
        }

        flock($handle, LOCK_EX);

        $contents = stream_get_contents($handle);
        $jobs = json_decode($contents ?: '', true);
        $jobs = is_array($jobs) ? $jobs : [];

        $jobs[$this->targetLocale] = array_merge($status, [
            'source_locale' => $this->sourceLocale,
            'target_locale' => $this->targetLocale,
            'updated_at' => now()->toDateTimeString(),
        ]);

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($jobs, JSON_PRETTY_PRINT));
        fflush($handle);

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Formats a second count into a short human-readable string,
     * e.g. 95 -> "1m 35s", 40 -> "40s".
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'a few seconds';
        }

        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        return $minutes > 0 ? "{$minutes}m {$secs}s" : "{$secs}s";
    }
}
