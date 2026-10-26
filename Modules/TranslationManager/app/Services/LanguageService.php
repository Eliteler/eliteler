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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class LanguageService
{
    protected string $langPath;
    protected string $configAppPath;

    public function __construct()
    {
        $this->langPath = config('translation-manager.lang_path', base_path('resources/lang'));
        $this->configAppPath = config_path('app.php');
    }

    public function getLanguages(): array
    {
        if (!File::exists($this->langPath)) {
            return [];
        }

        $locales = [];

        $directories = File::directories($this->langPath);
        foreach ($directories as $dir) {
            $locale = basename($dir);

            // EXCLUDE: Ignore Laravel's published vendor translations directory
            if ($locale === 'vendor') {
                continue;
            }

            $locales[$locale] = [
                'code' => $locale,
                'name' => $this->getLanguageName($locale),
                'type' => 'directory',
            ];
        }

        $jsonFiles = File::files($this->langPath);
        foreach ($jsonFiles as $file) {
            if ($file->getExtension() === 'json') {
                $locale = $file->getFilenameWithoutExtension();

                // EXCLUDE: Ignore vendor configurations if any
                if ($locale === 'vendor') {
                    continue;
                }

                if (!isset($locales[$locale])) {
                    $locales[$locale] = [
                        'code' => $locale,
                        'name' => $this->getLanguageName($locale),
                        'type' => 'json-only',
                    ];
                } else {
                    $locales[$locale]['type'] = 'both';
                }
            }
        }

        return $locales;
    }

    public function createLanguage(string $name, string $code, string $copyFromLocale, string $flagCode = ''): bool
    {
        $targetDir = $this->langPath . DIRECTORY_SEPARATOR . $code;
        $sourceDir = $this->langPath . DIRECTORY_SEPARATOR . $copyFromLocale;

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            File::copyDirectory($sourceDir, $targetDir);
        }

        $sourceJson = $this->langPath . DIRECTORY_SEPARATOR . "{$copyFromLocale}.json";
        $targetJson = $this->langPath . DIRECTORY_SEPARATOR . "{$code}.json";

        if (File::exists($sourceJson)) {
            File::copy($sourceJson, $targetJson);
        } else {
            File::put($targetJson, json_encode([], JSON_PRETTY_PRINT));
        }

        // Default flag code to language code if not provided
        $flag = !empty($flagCode) ? strtolower($flagCode) : strtolower($code);

        // Add language to config/app.php
        $this->addLanguageToAppConfig($code, $name, $flag);

        return true;
    }

    public function deleteLanguage(string $code): bool
    {
        $dir = $this->langPath . DIRECTORY_SEPARATOR . $code;
        $json = $this->langPath . DIRECTORY_SEPARATOR . "{$code}.json";

        if (File::exists($dir)) {
            File::deleteDirectory($dir);
        }
        if (File::exists($json)) {
            File::delete($json);
        }

        // Remove language from config/app.php
        $this->removeLanguageFromAppConfig($code);

        return true;
    }

    public function getLanguageName(string $code): string
    {
        $locales = config('translation-manager.supported_locales', []);
        return $locales[$code] ?? strtoupper($code);
    }

    /**
     * Add language details into config/app.php arrays
     */
    protected function addLanguageToAppConfig(string $code, string $name, string $flag): void
    {
        if (!File::exists($this->configAppPath)) {
            return;
        }

        $content = File::get($this->configAppPath);

        // Add to 'languages' array
        $content = $this->insertIntoArray($content, 'languages', $code, $name);

        // Add to 'availableLanguages' array
        $content = $this->insertIntoArray($content, 'availableLanguages', $code, $name);

        // Add to 'flag-icons' array
        $content = $this->insertIntoArray($content, 'flag-icons', $code, $flag);

        File::put($this->configAppPath, $content);

        // Clear config cache to reflect changes immediately
        Artisan::call('config:clear');
    }

    /**
     * Remove language details from config/app.php arrays
     */
    protected function removeLanguageFromAppConfig(string $code): void
    {
        if (!File::exists($this->configAppPath)) {
            return;
        }

        $content = File::get($this->configAppPath);

        // Remove from 'languages' array
        $content = $this->deleteFromArray($content, 'languages', $code);

        // Remove from 'availableLanguages' array
        $content = $this->deleteFromArray($content, 'availableLanguages', $code);

        // Remove from 'flag-icons' array
        $content = $this->deleteFromArray($content, 'flag-icons', $code);

        File::put($this->configAppPath, $content);

        // Clear config cache to reflect changes immediately
        Artisan::call('config:clear');
    }

    /**
     * Helper to insert a key-value pair into a specific configuration array
     */
    protected function insertIntoArray(string $content, string $arrayKey, string $key, string $value): string
    {
        $pattern = "/('" . preg_quote($arrayKey, '/') . "'\s*=>\s*\[)([\s\S]*?)(\])/";

        return preg_replace_callback($pattern, function ($matches) use ($key, $value) {
            // Check if key already exists inside this array
            if (preg_match("/'" . preg_quote($key, '/') . "'\s*=>/", $matches[2])) {
                return $matches[0];
            }

            $escapedValue = addslashes($value);
            $newLine = "\n        '{$key}' => '{$escapedValue}',";

            return $matches[1] . $newLine . $matches[2] . $matches[3];
        }, $content);
    }

    /**
     * Helper to remove a key-value pair from a specific configuration array
     */
    protected function deleteFromArray(string $content, string $arrayKey, string $key): string
    {
        $pattern = "/('" . preg_quote($arrayKey, '/') . "'\s*=>\s*\[)([\s\S]*?)(\])/";

        return preg_replace_callback($pattern, function ($matches) use ($key) {
            // Remove the line containing the specific key
            $updatedInner = preg_replace("/\r?\n\s*'" . preg_quote($key, '/') . "'\s*=>\s*[^,\n]+,?/", '', $matches[2]);
            return $matches[1] . $updatedInner . $matches[3];
        }, $content);
    }
}
