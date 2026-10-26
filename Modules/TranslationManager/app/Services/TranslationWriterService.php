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
use Illuminate\Support\Arr;

class TranslationWriterService
{
    protected string $langPath;

    public function __construct()
    {
        $this->langPath = config('translation-manager.lang_path', base_path('resources/lang'));
    }

    public function writeTranslations(string $locale, string $group, string $type, array $translations): bool
    {
        if ($type === 'json') {
            $path = $this->langPath . DIRECTORY_SEPARATOR . "{$locale}.json";
            $this->ensureDirectoryExists(dirname($path));

            // Keep the existing key order (new keys go at the end) so a save only
            // changes the edited lines instead of re-sorting the whole file
            $content = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

            // Match the file's existing indentation (some locales use 2 spaces)
            if (File::exists($path) && preg_match('/^\{\R( +)"/', (string) File::get($path, false), $match) && strlen($match[1]) !== 4) {
                $indent  = strlen($match[1]);
                $content = preg_replace_callback('/^( {4})+/m', fn ($m) => str_repeat(' ', intdiv(strlen($m[0]), 4) * $indent), $content);
            }
            return File::put($path, $content) !== false;
        }

        $path = $this->langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "{$group}.php";
        $this->ensureDirectoryExists(dirname($path));

        $unflattened = $this->unflatten($translations);
        ksort($unflattened);

        $formattedArray = $this->formatArray($unflattened);
        $content = "<?php\n\nreturn [\n{$formattedArray}\n];\n";

        return File::put($path, $content) !== false;
    }

    protected function ensureDirectoryExists(string $dir): void
    {
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    public function unflatten(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            Arr::set($result, $key, $value);
        }
        return $result;
    }

    protected function formatArray(array $array, int $indent = 1): string
    {
        $spaces = str_repeat('    ', $indent);
        $lines = [];
        foreach ($array as $key => $value) {
            $formattedKey = is_int($key) ? $key : "'" . addcslashes($key, "'\\") . "'";
            if (is_array($value)) {
                $lines[] = "{$spaces}{$formattedKey} => [";
                $lines[] = $this->formatArray($value, $indent + 1);
                $lines[] = "{$spaces}],";
            } elseif (is_bool($value)) {
                $lines[] = "{$spaces}{$formattedKey} => " . ($value ? 'true' : 'false') . ",";
            } elseif (is_null($value)) {
                $lines[] = "{$spaces}{$formattedKey} => null,";
            } elseif (is_numeric($value)) {
                $lines[] = "{$spaces}{$formattedKey} => {$value},";
            } else {
                $formattedValue = "'" . addcslashes($value, "'\\") . "'";
                $lines[] = "{$spaces}{$formattedKey} => {$formattedValue},";
            }
        }
        return implode("\n", $lines);
    }
}
