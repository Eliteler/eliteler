<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageHelper
{
    /**
     * Dangerous executable and script extensions that must never be stored.
     */
    private const DISALLOWED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'pht',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'bin', 'dll', 'cgi', 'pl', 'py',
        'asp', 'aspx', 'jsp', 'jspx', 'htaccess', 'htpasswd', 'vbs', 'scr', 'msi',
    ];

    /**
     * Allowed upload extensions for public assets (images, audio, json, pdf).
     */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'heic', 'bmp', 'avif',
        'mp3', 'wav', 'ogg', 'json', 'pdf',
    ];

    /**
     * Upload an uploaded file or save raw string content and return relative path.
     *
     * @param  \Illuminate\Http\UploadedFile|string|null  $fileOrContent
     * @param  string  $path
     * @param  string|null  $fileName
     * @param  string  $disk
     * @return string|null
     */
    public static function uploadImage($fileOrContent, string $path = 'uploads', ?string $fileName = null, string $disk = 'public'): ?string
    {
        if (!$fileOrContent) {
            return null;
        }

        $cleanPath = trim($path, '/');

        // Case 1: If input is an UploadedFile instance ($request->file)
        if ($fileOrContent instanceof UploadedFile) {
            // Determine real extension from binary MIME type first, then fallback
            $detected = strtolower($fileOrContent->guessExtension() ?: $fileOrContent->extension() ?: $fileOrContent->getClientOriginalExtension());

            if ($detected === 'jpeg') {
                $detected = 'jpg';
            }

            // Immediately reject disallowed or non-whitelisted extensions
            if (in_array($detected, self::DISALLOWED_EXTENSIONS, true) || !in_array($detected, self::ALLOWED_EXTENSIONS, true)) {
                return null;
            }

            $extension = $detected;

            if ($fileName) {
                $cleanFileName = basename($fileName);
                $specifiedExt = strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION));

                if ($specifiedExt && in_array($specifiedExt, self::DISALLOWED_EXTENSIONS, true)) {
                    return null;
                }

                $finalName = $specifiedExt ? $cleanFileName : $cleanFileName . '.' . $extension;
            } else {
                $originalName = pathinfo($fileOrContent->getClientOriginalName(), PATHINFO_FILENAME);
                $finalName = Str::slug($originalName ?: 'image') . '_' . uniqid() . '.' . $extension;
            }

            // Store file
            $storedPath = $fileOrContent->storeAs($cleanPath, $finalName, $disk);

            if (!$storedPath) {
                return null;
            }

            return $disk === 'public' ? 'storage/' . $storedPath : $storedPath;
        }

        // Case 2: If input is raw string / base64 content
        if (is_string($fileOrContent)) {
            $finalName = $fileName ? basename($fileName) : 'file_' . uniqid() . '.png';
            $specifiedExt = strtolower(pathinfo($finalName, PATHINFO_EXTENSION));

            if ($specifiedExt && in_array($specifiedExt, self::DISALLOWED_EXTENSIONS, true)) {
                return null;
            }

            $fullPath = $cleanPath ? $cleanPath . '/' . ltrim($finalName, '/') : ltrim($finalName, '/');

            Storage::disk($disk)->put($fullPath, $fileOrContent);

            return $disk === 'public' ? 'storage/' . $fullPath : $fullPath;
        }

        return null;
    }

    /**
     * Get the public URL for a stored asset path.
     *
     * @param  string|null  $path
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim(preg_replace('/^storage\//', '', $path), '/');
        $driver = config('filesystems.disks.public.driver') ?: config('filesystems.default', 'local');

        if (in_array($driver, ['s3', 'r2'], true)) {
            $base = rtrim((string) config('filesystems.disks.public.url'), '/');
            if ($base !== '') {
                return "{$base}/{$clean}";
            }
        }

        return asset($path);
    }

    /**
     * Delete a file from any storage disk using relative path or full URL.
     *
     * @param  string|null  $path
     * @param  string  $disk
     * @return bool
     */
    public static function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        $normalizedPath = $path;

        // If a full CDN or site URL was passed, extract just the path portion
        if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
            $parsed = parse_url($normalizedPath, PHP_URL_PATH);
            $normalizedPath = $parsed !== false ? $parsed : $normalizedPath;
        }

        $normalizedPath = ltrim($normalizedPath, '/');

        if ($disk === 'public') {
            $normalizedPath = preg_replace('/^storage\//', '', $normalizedPath);
        }

        if (str_ends_with($normalizedPath, '/')) {
            return false;
        }

        if (Storage::disk($disk)->exists($normalizedPath)) {
            return Storage::disk($disk)->delete($normalizedPath);
        }

        return false;
    }
}
