<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageHelper
{
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
            // Get valid extension with fallback for cropped blobs
            $extension = $fileOrContent->getClientOriginalExtension()
                ?: $fileOrContent->extension()
                ?: 'png';

            if ($fileName) {
                // Ensure extension is attached to the custom name
                $finalName = pathinfo($fileName, PATHINFO_EXTENSION) ? $fileName : $fileName . '.' . $extension;
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
            $finalName = $fileName ?: 'file_' . uniqid() . '.png';
            $fullPath = $cleanPath ? $cleanPath . '/' . ltrim($finalName, '/') : ltrim($finalName, '/');

            Storage::disk($disk)->put($fullPath, $fileOrContent);

            return $disk === 'public' ? 'storage/' . $fullPath : $fullPath;
        }

        return null;
    }

    /**
     * Delete a file from any storage disk using relative path.
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

        $normalizedPath = ltrim($path, '/');

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
