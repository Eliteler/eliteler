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
    public const LOCALE_REGEX = '/^[a-z]{2,3}(_[A-Za-z]{2,4})?$/';

    protected string $langPath;
    protected string $configAppPath;

    public function __construct()
    {
        $this->langPath = config('translation-manager.lang_path', base_path('resources/lang'));
        $this->configAppPath = config_path('app.php');
    }

    public function isValidLocale(string $locale): bool
    {
        return (bool) preg_match(self::LOCALE_REGEX, $locale);
    }

    public function getLanguages(): array
    {
        if (!File::exists($this->langPath)) {
            return [];
        }

        // Auto-purge any invalid rogue files or directories in resources/lang
        $this->purgeInvalidLanguageFiles();

        $locales = [];

        $directories = File::directories($this->langPath);
        foreach ($directories as $dir) {
            $locale = basename($dir);

            // EXCLUDE: Ignore Laravel's published vendor translations directory and non-locale directories
            if ($locale === 'vendor' || !$this->isValidLocale($locale)) {
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

                // EXCLUDE: Ignore vendor configurations and non-locale json files
                if ($locale === 'vendor' || !$this->isValidLocale($locale)) {
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

    /**
     * Purge rogue non-locale files or folders from resources/lang if any exist
     */
    public function purgeInvalidLanguageFiles(): array
    {
        if (!File::exists($this->langPath)) {
            return [];
        }

        $purged = [];

        try {
            $jsonFiles = File::files($this->langPath);
            foreach ($jsonFiles as $file) {
                if ($file->getExtension() === 'json') {
                    $locale = $file->getFilenameWithoutExtension();
                    if ($locale !== 'vendor' && !$this->isValidLocale($locale)) {
                        File::delete($file->getRealPath());
                        $purged[] = $file->getFilename();
                    }
                }
            }

            $directories = File::directories($this->langPath);
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                if ($dirName !== 'vendor' && !str_starts_with($dirName, '.') && !$this->isValidLocale($dirName)) {
                    File::deleteDirectory($dir);
                    $purged[] = $dirName;
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore permission errors
        }

        return $purged;
    }

    public function createLanguage(string $name, string $code, string $copyFromLocale = 'none', string $flagCode = ''): bool
    {
        $targetDir = $this->langPath . DIRECTORY_SEPARATOR . $code;

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $targetJson = $this->langPath . DIRECTORY_SEPARATOR . "{$code}.json";
        $defaultLocale = config('translation-manager.default_locale', 'en');

        if ($copyFromLocale === 'none' || empty($copyFromLocale)) {
            // "Do not duplicate" selected:
            // Extract keys from default locale (en) with empty string "" values for translation.
            $sourceDir = $this->langPath . DIRECTORY_SEPARATOR . $defaultLocale;
            if (File::exists($sourceDir)) {
                $files = File::allFiles($sourceDir);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $relativePath = $file->getRelativePathname();
                        $targetFilePath = $targetDir . DIRECTORY_SEPARATOR . $relativePath;
                        $targetFileDir = dirname($targetFilePath);

                        if (!File::exists($targetFileDir)) {
                            File::makeDirectory($targetFileDir, 0755, true);
                        }

                        $data = File::getRequire($file->getPathname());
                        $blankData = is_array($data) ? $this->blankArrayValues($data) : [];

                        $formatted = $this->formatPhpLangArray($blankData);
                        File::put($targetFilePath, "<?php\n\nreturn [\n{$formatted}\n];\n");
                    }
                }
            }

            // For JSON translations:
            $sourceJson = $this->langPath . DIRECTORY_SEPARATOR . "{$defaultLocale}.json";
            if (File::exists($sourceJson)) {
                $jsonData = json_decode(File::get($sourceJson), true);
                if (is_array($jsonData)) {
                    $blankJson = [];
                    foreach ($jsonData as $key => $val) {
                        $blankJson[$key] = '';
                    }
                    File::put($targetJson, json_encode($blankJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
                } else {
                    File::put($targetJson, json_encode([], JSON_PRETTY_PRINT) . "\n");
                }
            } else {
                File::put($targetJson, json_encode([], JSON_PRETTY_PRINT) . "\n");
            }
        } else {
            // Duplicate from specified language:
            $sourceDir = $this->langPath . DIRECTORY_SEPARATOR . $copyFromLocale;

            if (File::exists($sourceDir)) {
                File::copyDirectory($sourceDir, $targetDir);
            }

            $sourceJson = $this->langPath . DIRECTORY_SEPARATOR . "{$copyFromLocale}.json";

            if (File::exists($sourceJson)) {
                File::copy($sourceJson, $targetJson);
            } else {
                File::put($targetJson, json_encode([], JSON_PRETTY_PRINT));
            }
        }

        // Default flag code to language code if not provided
        $flag = !empty($flagCode) ? strtolower($flagCode) : strtolower($code);

        // Add language to config/app.php
        $this->addLanguageToAppConfig($code, $name, $flag);

        return true;
    }

    /**
     * Recursively replace all scalar values with empty strings while preserving keys and array structure.
     */
    public function blankArrayValues(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->blankArrayValues($value);
            } else {
                $result[$key] = '';
            }
        }
        return $result;
    }

    /**
     * Format a PHP array into clean PHP code string matching standard Laravel lang files.
     */
    public function formatPhpLangArray(array $array, int $indent = 1): string
    {
        $spaces = str_repeat('    ', $indent);
        $lines = [];
        foreach ($array as $key => $value) {
            $formattedKey = is_int($key) ? $key : "'" . addcslashes($key, "'\\") . "'";
            if (is_array($value)) {
                if (empty($value)) {
                    $lines[] = "{$spaces}{$formattedKey} => [],";
                } else {
                    $lines[] = "{$spaces}{$formattedKey} => [";
                    $lines[] = $this->formatPhpLangArray($value, $indent + 1);
                    $lines[] = "{$spaces}],";
                }
            } elseif (is_bool($value)) {
                $lines[] = "{$spaces}{$formattedKey} => " . ($value ? 'true' : 'false') . ",";
            } elseif (is_null($value)) {
                $lines[] = "{$spaces}{$formattedKey} => null,";
            } elseif (is_numeric($value)) {
                $lines[] = "{$spaces}{$formattedKey} => {$value},";
            } else {
                $formattedValue = "'" . addcslashes((string) $value, "'\\") . "'";
                $lines[] = "{$spaces}{$formattedKey} => {$formattedValue},";
            }
        }
        return implode("\n", $lines);
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
