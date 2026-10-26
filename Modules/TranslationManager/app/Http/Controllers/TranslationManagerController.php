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

namespace Modules\TranslationManager\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\TranslationManager\app\Http\Requests\ImportLanguageRequest;
use Modules\TranslationManager\app\Http\Requests\StoreLanguageRequest;
use Modules\TranslationManager\app\Http\Requests\UpdateTranslationsRequest;
use Modules\TranslationManager\app\Jobs\SyncMissingTranslationsJob;
use Modules\TranslationManager\app\Services\LanguageExportService;
use Modules\TranslationManager\app\Services\LanguageImportService;
use Modules\TranslationManager\app\Services\LanguageService;
use Modules\TranslationManager\app\Services\MissingTranslationService;
use Modules\TranslationManager\app\Services\TranslationReaderService;
use Modules\TranslationManager\app\Services\TranslationWriterService;
use Symfony\Component\Process\Process;

/**
 * Languages and their translation files under resources/lang.
 * The source language (config translation-manager.default_locale) is the
 * reference every other language is compared with; it can't be deleted.
 */
class TranslationManagerController extends Controller
{
    private const PER_PAGE = 50;

    public function __construct(
        protected LanguageService $languageService,
        protected TranslationReaderService $reader,
        protected TranslationWriterService $writer,
        protected MissingTranslationService $missingService,
        protected LanguageExportService $exportService,
        protected LanguageImportService $importService,
    ) {}

    public function index(Request $request)
    {
        $source     = $this->sourceLocale();
        $sourceKeys = $this->reader->readTranslations($source, "{$source}.json", 'json');
        $enabled    = array_keys(config('app.languages', []));
        $search     = trim((string) $request->query('search', ''));

        // Retrieve and format all languages
        $languages = collect($this->languageService->getLanguages())->map(function ($language) use ($source, $sourceKeys, $enabled) {
            $code   = $language['code'];
            $target = $code === $source ? $sourceKeys : $this->reader->readTranslations($code, "{$code}.json", 'json');
            $done   = count(array_filter(array_intersect_key($target, $sourceKeys), fn($value) => trim((string) $value) !== ''));

            return [
                'code'     => $code,
                'name'     => __($language['name']),
                'isSource' => $code === $source,
                'enabled'  => in_array($code, $enabled, true),
                'total'    => count($sourceKeys),
                'done'     => $done,
                'sync'     => $this->syncState($code),
            ];
        })->sortBy([['isSource', 'desc'], ['name', 'asc']])->values();

        // Filter results by search keyword
        if ($search !== '') {
            $needle    = mb_strtolower($search);
            $languages = $languages->filter(function ($item) use ($needle) {
                return str_contains(mb_strtolower($item['name']), $needle)
                    || str_contains(mb_strtolower($item['code']), $needle);
            })->values();
        }

        // Server-side pagination parameters
        $perPage   = max(1, (int) $request->query('per_page', 10));
        $page      = max(1, (int) $request->query('page', 1));

        $paginated = new LengthAwarePaginator(
            $languages->slice(($page - 1) * $perPage, $perPage)->values(),
            $languages->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('settings/translations/index', [
            'languages' => $paginated,
            'source'    => $source,
            'search'    => $search,
        ]);
    }

    // Old create page now opens the dialog on the list
    public function create()
    {
        return redirect()->route('translation-manager.index', ['new' => 1]);
    }

    public function store(StoreLanguageRequest $request)
    {
        $data = $request->validated();

        if (array_key_exists($data['code'], $this->languageService->getLanguages())) {
            return back()->withErrors(['code' => __('This language already exists.')]);
        }
        $this->ensureLanguage($data['copy_from']);

        $this->languageService->createLanguage($data['name'], $data['code'], $data['copy_from']);

        return redirect()->route('translation-manager.edit', $data['code'])
            ->with('success', __(':name added. Its text starts as a copy of the language you chose.', ['name' => $data['name']]));
    }

    public function edit(Request $request, string $locale)
    {
        $this->ensureLanguage($locale);

        $source = $this->sourceLocale();
        // Main website/app text (the JSON file) first, then Laravel's PHP files
        $files  = collect($this->reader->getFiles($source))->sortBy(fn($file) => $file['type'] === 'json' ? 0 : 1)->map(fn($file) => [
            'value' => $file['type'] . ':' . $file['name'],
            'label' => $file['type'] === 'json' ? __('Website and app text') : ucfirst($file['name']) . '.php',
        ])->values();

        $selected = (string) $request->query('file', $files->first()['value'] ?? '');
        [$type, $group] = array_pad(explode(':', $selected, 2), 2, '');
        if (! $files->contains('value', $selected)) {
            [$type, $group] = explode(':', $files->first()['value'] ?? 'json:' . $source . '.json', 2);
            $selected = "{$type}:{$group}";
        }

        // JSON files are named after the locale (en.json → fr.json)
        $targetGroup = $type === 'json' ? "{$locale}.json" : $group;
        $sourceRows  = $this->reader->readTranslations($source, $group, $type);
        $targetRows  = $this->reader->readTranslations($locale, $targetGroup, $type);
        $search      = trim((string) $request->query('search', ''));
        $filter      = $request->query('filter') === 'untranslated' ? 'untranslated' : 'all';

        $rows = collect($sourceRows)->map(fn($text, $key) => [
            'key'    => (string) $key,
            'source' => (string) $text,
            'value'  => (string) ($targetRows[$key] ?? ''),
        ]);

        $untranslated = $rows->filter(fn($row) => trim($row['value']) === '')->count();

        if ($filter === 'untranslated') {
            $rows = $rows->filter(fn($row) => trim($row['value']) === '');
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows   = $rows->filter(fn($row) => str_contains(mb_strtolower($row['key'] . ' ' . $row['source'] . ' ' . $row['value']), $needle));
        }

        $page      = max(1, (int) $request->query('page', 1));
        $paginated = new LengthAwarePaginator(
            $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $languages = $this->languageService->getLanguages();

        return Inertia::render('settings/translations/edit', [
            'language'     => ['code' => $locale, 'name' => __($languages[$locale]['name'] ?? strtoupper($locale)), 'isSource' => $locale === $source],
            'source'       => ['code' => $source, 'name' => __($languages[$source]['name'] ?? strtoupper($source))],
            'files'        => $files,
            'file'         => $selected,
            'filter'       => $filter,
            'search'       => $search,
            'rows'         => $paginated,
            'untranslated' => $untranslated,
            'total'        => count($sourceRows),
            'sync'         => $this->syncState($locale),
        ]);
    }

    /** Save the rows changed on the current page. */
    public function update(UpdateTranslationsRequest $request, string $locale)
    {
        $this->ensureLanguage($locale);

        // Only files that exist for the source language; never a path from the request
        $file = (string) $request->input('file');
        abort_unless(in_array($file, $this->fileOptions(), true), 422);
        [$type, $group] = explode(':', $file, 2);

        $targetGroup = $type === 'json' ? "{$locale}.json" : $group;
        $sourceKeys  = $this->reader->readTranslations($this->sourceLocale(), $group, $type);
        $changes     = array_intersect_key($request->input('translations', []), $sourceKeys);
        $existing    = $this->reader->readTranslations($locale, $targetGroup, $type);

        $this->writer->writeTranslations($locale, $targetGroup, $type, array_merge($existing, array_map(fn($value) => (string) $value, $changes)));

        return back()->with('success', __(':count translations saved.', ['count' => count($changes)]));
    }

    /** Add a new text to the source language (and a translation for this one). */
    public function addKey(Request $request)
    {
        $data = $request->validate([
            'source_value' => 'required|string|max:2000',
            'target_value' => 'nullable|string|max:2000',
            'locale'       => ['required', 'string', 'regex:/^[a-z]{2,3}(_[A-Za-z]{2,4})?$/'],
        ], [], ['source_value' => __('text')]);

        $this->ensureLanguage($data['locale']);

        $source = $this->sourceLocale();
        $key    = trim($data['source_value']);

        try {
            $sourceRows       = $this->reader->readTranslations($source, "{$source}.json", 'json');
            $sourceRows[$key] = $key;
            $this->writer->writeTranslations($source, "{$source}.json", 'json', $sourceRows);

            if ($data['locale'] !== $source) {
                $target = trim((string) ($data['target_value'] ?? ''));
                if ($target === '') {
                    $target = $this->missingService->translateValue($key, $source, $data['locale']);
                }
                $targetRows       = $this->reader->readTranslations($data['locale'], "{$data['locale']}.json", 'json');
                $targetRows[$key] = $target;
                $this->writer->writeTranslations($data['locale'], "{$data['locale']}.json", 'json', $targetRows);
            }
        } catch (\Throwable $e) {
            Log::error("addKey failed for '{$key}': " . $e->getMessage());

            return back()->with('failed', __('Couldn’t add the text: :error', ['error' => $e->getMessage()]));
        }

        return back()->with('success', __('Text added.'));
    }

    // Old "missing" page is now the Untranslated filter on the editor
    public function missing(string $locale)
    {
        return redirect()->route('translation-manager.edit', ['locale' => $locale, 'filter' => 'untranslated']);
    }

    /** Machine-translate every missing text in the background. */
    public function syncMissing(Request $request, string $locale)
    {
        $this->ensureLanguage($locale);
        abort_if($locale === $this->sourceLocale(), 422);

        $source = $this->sourceLocale();
        Cache::put(SyncMissingTranslationsJob::statusCacheKey($locale), ['state' => 'queued', 'message' => __('Waiting to start…')], now()->addHour());
        SyncMissingTranslationsJob::dispatch($source, $locale);

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->startQueueWorker();
        }

        return back()->with('success', __('Translating missing text in the background. You can keep working; this page updates when it finishes.'));
    }

    protected function startQueueWorker(): void
    {
        $process = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'queue:work',
            '--queue=default',
            '--stop-when-empty',
            '--tries=1',
            '--timeout=1800',
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);
        $process->disableOutput();

        try {
            $process->start();
            Log::info('queue:work background process started, PID: ' . $process->getPid());
        } catch (\Throwable $e) {
            Log::error('Failed to start queue:work: ' . $e->getMessage());
        }
    }

    /**
     * Returns active sync messages across all locales, for the global
     * info alert to poll and update live (used by the site-wide layout).
     */
    public function activeSyncStatuses()
    {
        $languages = $this->languageService->getLanguages();
        $activeMessages = [];

        foreach ($languages as $language) {
            $locale = is_array($language) ? $language['code'] : $language->code;

            $status = Cache::get(SyncMissingTranslationsJob::statusCacheKey($locale));

            if (!$status) {
                continue;
            }

            $state = $status['state'] ?? '';

            if (in_array($state, ['queued', 'running'], true)) {
                $activeMessages[] = strtoupper($locale) . ': ' . ($status['message'] ?? 'Sync in progress…');
            }
        }

        return response()->json([
            'active' => !empty($activeMessages),
            'message' => implode(' | ', $activeMessages),
        ]);
    }

    /**
     * Poll endpoint for the frontend to check on a background sync's progress.
     * e.g. GET /translation-manager/{locale}/sync-status
     */
    public function syncStatus(string $locale)
    {
        $status = Cache::get(
            SyncMissingTranslationsJob::statusCacheKey($locale),
            ['state' => 'idle', 'message' => 'No sync in progress.']
        );

        return response()->json($status);
    }

    // Export language pack
    public function export(string $locale)
    {
        $this->ensureLanguage($locale);

        try {
            return response()->download($this->exportService->exportLocale($locale))->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('Couldn’t export: :error', ['error' => $e->getMessage()]));
        }
    }

    public function import(ImportLanguageRequest $request)
    {
        $data = $request->validated();
        $this->ensureLanguage($data['locale']);

        try {
            $this->importService->importLocale($data['locale'], $request->file('zip_file')->getRealPath());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('Couldn’t import the file: :error', ['error' => $e->getMessage()]));
        }

        return back()->with('success', __('Translations imported.'));
    }

    public function destroy(string $locale)
    {
        $this->ensureLanguage($locale);

        if ($locale === $this->sourceLocale()) {
            return back()->with('failed', __('The source language can’t be deleted.'));
        }
        if ($locale === config('app.locale')) {
            return back()->with('failed', __('This is the default language. Choose another default in General settings first.'));
        }

        $this->languageService->deleteLanguage($locale);

        return redirect()->route('translation-manager.index')->with('success', __(':code deleted.', ['code' => strtoupper($locale)]));
    }

    /** "type:name" for each translation file of the source language. */
    private function fileOptions(): array
    {
        return collect($this->reader->getFiles($this->sourceLocale()))->map(fn($file) => $file['type'] . ':' . $file['name'])->all();
    }

    private function sourceLocale(): string
    {
        return (string) config('translation-manager.default_locale', 'en');
    }

    /** 404 unless the locale exists in resources/lang. */
    private function ensureLanguage(string $locale): void
    {
        abort_unless(array_key_exists($locale, $this->languageService->getLanguages()), 404);
    }

    private function syncState(string $locale): ?array
    {
        $status = Cache::get(SyncMissingTranslationsJob::statusCacheKey($locale));

        return $status && in_array($status['state'] ?? '', ['queued', 'running', 'completed', 'failed'], true) ? $status : null;
    }
}
