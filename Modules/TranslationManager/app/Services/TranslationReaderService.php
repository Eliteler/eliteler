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

class TranslationReaderService
{
    protected string $langPath;

    public function __construct()
    {
        $this->langPath = config('translation-manager.lang_path', base_path('resources/lang'));
    }

    public function getFiles(string $locale): array
    {
        $files = [];
        $localeDir = $this->langPath . DIRECTORY_SEPARATOR . $locale;

        if (File::exists($localeDir)) {
            foreach (File::allFiles($localeDir) as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = [
                        'name' => $file->getFilenameWithoutExtension(),
                        'type' => 'php',
                        'relative' => $locale . DIRECTORY_SEPARATOR . $file->getFilename(),
                    ];
                }
            }
        }

        $jsonFile = $this->langPath . DIRECTORY_SEPARATOR . "{$locale}.json";
        if (File::exists($jsonFile)) {
            $files[] = [
                'name' => "{$locale}.json",
                'type' => 'json',
                'relative' => "{$locale}.json",
            ];
        }

        return $files;
    }

    public function readTranslations(string $locale, string $group, string $type): array
    {
        if ($type === 'json') {
            $path = $this->langPath . DIRECTORY_SEPARATOR . "{$locale}.json";
            if (!File::exists($path)) {
                return [];
            }
            $data = json_decode(File::get($path), true);
            return is_array($data) ? $data : [];
        }

        $path = $this->langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "{$group}.php";
        if (!File::exists($path)) {
            return [];
        }

        $data = File::getRequire($path);
        if (!is_array($data)) {
            return [];
        }

        return $this->flatten($data);
    }

    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flatten($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}
