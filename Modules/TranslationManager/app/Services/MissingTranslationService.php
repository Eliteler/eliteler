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

namespace Modules\TranslationManager\app\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TranslationManager\App\Providers\TranslationPlaceholderProtector;

class MissingTranslationService
{
    protected TranslationReaderService $reader;
    protected TranslationWriterService $writer;

    public function __construct(TranslationReaderService $reader, TranslationWriterService $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public function detectMissingKeys(string $sourceLocale, string $targetLocale): array
    {
        $missing = [];
        $sourceFiles = $this->reader->getFiles($sourceLocale);

        foreach ($sourceFiles as $sourceFile) {
            $group = $sourceFile['name'];
            $type = $sourceFile['type'];

            $sourceTranslations = $this->reader->readTranslations($sourceLocale, $group, $type);
            $targetTranslations = $this->reader->readTranslations($targetLocale, $group, $type);

            $diffKeys = array_diff_key($sourceTranslations, $targetTranslations);

            if (!empty($diffKeys)) {
                $missing[$group] = [
                    'type' => $type,
                    'keys' => []
                ];
                foreach ($diffKeys as $key => $value) {
                    $missing[$group]['keys'][$key] = [
                        'source_value' => $value,
                    ];
                }
            }
        }

        return $missing;
    }

    /**
     * Sync missing keys into the target locale, translating each
     * source value from $sourceLocale into $targetLocale before writing.
     *
     * This can take a long time (one HTTP call + 300ms sleep per key), so
     * it's designed to be run from a queued job rather than inline during
     * a web request. Pass $onProgress to receive (done, total) updates,
     * e.g. to write status into cache for a polling endpoint.
     */
    public function syncMissingKeys(
        string $sourceLocale,
        string $targetLocale,
        ?callable $onProgress = null,
        int $maxExecutionSeconds = 50
    ): bool {
        $startTime = microtime(true);
        $missing = $this->detectMissingKeys($sourceLocale, $targetLocale);

        $total = 0;
        foreach ($missing as $details) {
            $total += count($details['keys']);
        }

        if ($total === 0) {
            return true;
        }

        $done = 0;

        foreach ($missing as $group => $details) {
            $type = $details['type'];
            $keysToSync = $details['keys'];

            $targetTranslations = $this->reader->readTranslations($targetLocale, $group, $type);
            $hasUnsavedChanges = false;

            foreach ($keysToSync as $key => $val) {
                // Check if execution time has reached the threshold
                if ((microtime(true) - $startTime) >= $maxExecutionSeconds) {
                    if ($hasUnsavedChanges) {
                        $this->writer->writeTranslations($targetLocale, $group, $type, $targetTranslations);
                    }
                    // Return false to notify the job that work is pending
                    return false;
                }

                $targetTranslations[$key] = $this->translate(
                    $val['source_value'],
                    $sourceLocale,
                    $targetLocale
                );

                $hasUnsavedChanges = true;
                $done++;

                if ($onProgress) {
                    $onProgress($done, $total);
                }

                // Prevent hitting rate limits on free translation endpoints
                usleep(300000);
            }

            if ($hasUnsavedChanges) {
                $this->writer->writeTranslations($targetLocale, $group, $type, $targetTranslations);
            }
        }

        return true;
    }

    /**
     * Public entry point so callers outside this service (e.g. controllers)
     * can translate a single string on demand, using the same
     * Google -> LibreTranslate -> Lingva fallback chain.
     */
    public function translateValue(string $text, string $sourceLocale, string $targetLocale): string
    {
        return $this->translate($text, $sourceLocale, $targetLocale);
    }

    /*
    |--------------------------------------------------------------------------
    | Translate Text
    |--------------------------------------------------------------------------
    |
    | Tries Google Translate first, then LibreTranslate, then Lingva.
    | Falls back to returning the original text untranslated if every
    | provider fails, so the key is still populated rather than blank.
    |
    */
    protected function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        // Nothing worth translating once placeholders/HTML/numbers are
        // stripped out (e.g. a value that's just "{count}") - skip the API
        // call entirely and return the original untouched.
        if (!TranslationPlaceholderProtector::hasTranslatableContent($text)) {
            return $text;
        }

        [$protectedText, $tokens] = TranslationPlaceholderProtector::protect($text);

        /*
        |--------------------------------------------------------------------------
        | Google Translate
        |--------------------------------------------------------------------------
        */
        try {
            $response = Http::timeout(30)->get(
                'https://translate.googleapis.com/translate_a/single',
                [
                    'client' => 'gtx',
                    'sl' => $sourceLocale,
                    'tl' => $targetLocale,
                    'dt' => 't',
                    'q' => $protectedText,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $translated = $data[0][0][0] ?? null;

                if (!empty($translated)) {
                    return TranslationPlaceholderProtector::restore($translated, $tokens);
                }
            }
        } catch (\Throwable $e) {
            Log::info('Google Translate API error: ' . $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | LibreTranslate
        |--------------------------------------------------------------------------
        */
        try {
            $response = Http::timeout(30)->post(
                'https://libretranslate.com/translate',
                [
                    'q' => $protectedText,
                    'source' => $sourceLocale,
                    'target' => $targetLocale,
                    'format' => 'text',
                ]
            );

            if ($response->successful()) {
                $translated = $response->json()['translatedText'] ?? null;

                if (!empty($translated)) {
                    return TranslationPlaceholderProtector::restore($translated, $tokens);
                }
            }
        } catch (\Throwable $e) {
            Log::info('LibreTranslate API error: ' . $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Lingva Translate
        |--------------------------------------------------------------------------
        */
        try {
            $url = "https://lingva.ml/api/v1/{$sourceLocale}/{$targetLocale}/" . urlencode($protectedText);

            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $translated = $response->json()['translation'] ?? null;

                if (!empty($translated)) {
                    return TranslationPlaceholderProtector::restore($translated, $tokens);
                }
            }
        } catch (\Throwable $e) {
            Log::info('Lingva Translate API error: ' . $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback: return original text untranslated
        |--------------------------------------------------------------------------
        */
        return $text;
    }
}
