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

namespace App\Services;

use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Reads a plugin / web template zip by its contents, not its file or folder
 * names: "Zapier-1.2.0.zip" holding "Zapier-1.2.0/plugin.json" (or the files
 * at the zip's root) still installs to plugins/Zapier.
 */
class PackageZip
{
    /** Shallowest $manifest (plugin.json / template.json) in the zip, skipping macOS metadata. */
    public static function manifestPath(ZipArchive $zip, string $manifest): ?string
    {
        $path = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (self::ignored($name) || strcasecmp(basename($name), $manifest) !== 0) {
                continue;
            }
            if ($path === null || substr_count($name, '/') < substr_count($path, '/')) {
                $path = $name;
            }
        }

        return $path;
    }

    /** The package's files, relative to $root (the manifest's folder inside the zip). */
    public static function files(ZipArchive $zip, string $root): array
    {
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! self::ignored($name) && str_starts_with($name, $root) && $name !== $root) {
                $files[$i] = substr($name, strlen($root));
            }
        }

        return $files;
    }

    /**
     * Folder to install into: the one its PHP namespace expects
     * (Plugins\<Folder>\... for $namespace "Plugins"), else $fallback.
     * Null when neither is a valid folder name.
     */
    public static function folder(ZipArchive $zip, string $root, string $namespace, ?string $fallback): ?string
    {
        $folder = $fallback;
        foreach (self::files($zip, $root) as $i => $file) {
            if (str_ends_with(strtolower($file), '.php')
                && preg_match('/^\s*namespace\s+' . $namespace . '\\\\([A-Za-z0-9_]+)/m', (string) $zip->getFromIndex($i), $match)) {
                $folder = $match[1];
                break;
            }
        }

        return is_string($folder) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $folder) ? $folder : null;
    }

    /** Extract the package's files into $target, overwriting what's there. */
    public static function extract(ZipArchive $zip, string $root, string $target): void
    {
        File::ensureDirectoryExists($target);

        foreach (self::files($zip, $root) as $i => $file) {
            $path = $target . '/' . $file;

            if (str_ends_with($file, '/')) {
                File::ensureDirectoryExists($path);
                continue;
            }

            File::ensureDirectoryExists(dirname($path));
            $in  = $zip->getStream($zip->getNameIndex($i));
            $out = fopen($path, 'wb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            fclose($out);
        }
    }

    private static function ignored(string $name): bool
    {
        return str_starts_with($name, '__MACOSX/') || basename($name) === '.DS_Store';
    }
}
