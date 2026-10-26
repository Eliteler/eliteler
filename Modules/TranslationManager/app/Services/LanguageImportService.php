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

use Illuminate\Support\Facades\File;
use ZipArchive;
use RuntimeException;

class LanguageImportService
{
    protected string $langPath;

    public function __construct()
    {
        $this->langPath = config('translation-manager.lang_path', base_path('resources/lang'));
    }

    public function importLocale(string $locale, string $zipFilePath): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath) !== true) {
            throw new RuntimeException("Could not open ZIP package.");
        }

        $tempExtractPath = storage_path('app/temp_imports/' . uniqid('import_', true));
        if (!File::exists($tempExtractPath)) {
            File::makeDirectory($tempExtractPath, 0755, true);
        }

        $zip->extractTo($tempExtractPath);
        $zip->close();

        $extractedDirs = File::directories($tempExtractPath);
        foreach ($extractedDirs as $dir) {
            $dirName = basename($dir);
            if ($dirName === $locale) {
                $targetDir = $this->langPath . DIRECTORY_SEPARATOR . $locale;
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                File::copyDirectory($dir, $targetDir);
            }
        }

        $extractedFiles = File::files($tempExtractPath);
        foreach ($extractedFiles as $file) {
            if ($file->getExtension() === 'json' && $file->getFilenameWithoutExtension() === $locale) {
                $targetJson = $this->langPath . DIRECTORY_SEPARATOR . "{$locale}.json";
                File::copy($file->getRealPath(), $targetJson);
            }
        }

        File::deleteDirectory($tempExtractPath);

        return true;
    }
}
