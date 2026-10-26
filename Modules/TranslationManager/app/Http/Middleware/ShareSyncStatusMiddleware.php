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

namespace Modules\TranslationManager\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\TranslationManager\app\Jobs\SyncMissingTranslationsJob;
use Modules\TranslationManager\app\Services\LanguageService;

class ShareSyncStatusMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->flashActiveSyncStatus();
        } catch (\Throwable $e) {
            Log::warning('ShareSyncStatusMiddleware failed: ' . $e->getMessage());
        }

        return $next($request);
    }

    protected function flashActiveSyncStatus(): void
    {
        $languageService = app(LanguageService::class);
        $languages = $languageService->getLanguages();

        $activeMessages = [];

        foreach ($languages as $language) {
            $locale = is_array($language) ? $language['code'] : $language->code;

            $status = Cache::get(SyncMissingTranslationsJob::statusCacheKey($locale));

            if (!$status) {
                continue;
            }

            $state = $status['state'] ?? '';

            if (in_array($state, ['queued', 'running'], true)) {
                $message = $status['message'] ?? 'Sync in progress…';
                $activeMessages[] = strtoupper($locale) . ': ' . $message;
            }
        }

        if (!empty($activeMessages)) {
            Log::info('Active sync status flashed to session', ['messages' => $activeMessages]);
            session()->flash('info', implode(' | ', $activeMessages));
        }
    }
}
