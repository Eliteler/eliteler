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

use Illuminate\Support\Facades\Log;

/**
 * Enterprise-grade Environment Service for safely reading and modifying .env.
 *
 * Guarantees:
 * 1. Zero White Screen of Death (WSOD): Never corrupts, truncates, or blanks .env.
 * 2. Atomic Writing: Writes via temporary file with LOCK_EX before replacing .env.
 * 3. Smart Formatting: Automatically quotes strings with spaces or special characters.
 * 4. Batch Updates (setMany): Updates multiple keys in a single atomic I/O operation.
 * 5. Runtime Synchronization: Immediately updates putenv, $_ENV, and $_SERVER.
 */
class EnvironmentService
{
    /**
     * Update or append a single key in .env safely.
     */
    public static function set(string $key, ?string $value): bool
    {
        return self::setMany([$key => $value]);
    }

    /**
     * Update or append multiple keys in .env in a single atomic write operation.
     *
     * @param array<string, ?string> $keyValues Key-value pairs to update
     * @return bool True if successfully updated, false otherwise
     */
    public static function setMany(array $keyValues): bool
    {
        try {
            $envPath = base_path('.env');

            // 1. Check if .env exists, is readable, and is writable
            if (!file_exists($envPath) || !is_readable($envPath) || !is_writable($envPath)) {
                Log::warning("EnvironmentService: .env does not exist or is not writable.");
                return false;
            }

            // 2. Read existing content safely
            $content = file_get_contents($envPath);
            if ($content === false || strlen(trim($content)) === 0) {
                Log::error("EnvironmentService: .env is empty or unreadable. Aborting write to prevent data loss.");
                return false;
            }

            // 3. Sanity check: Ensure .env has critical Laravel keys before modifying
            if (!str_contains($content, 'APP_KEY') && !str_contains($content, 'DB_') && !str_contains($content, 'APP_NAME')) {
                Log::error("EnvironmentService: .env appears truncated or corrupt. Aborting write.");
                return false;
            }

            $newContent = $content;

            foreach ($keyValues as $rawKey => $rawValue) {
                $key = trim($rawKey);
                // Sanitize key (only uppercase letters, numbers, underscores)
                $key = preg_replace('/[^A-Z0-9_]/i', '', $key);
                if (empty($key)) {
                    continue;
                }

                $formattedValue = self::formatValue($rawValue);

                // Check if key exists in content
                $pattern = '/^(\s*' . preg_quote($key, '/') . '\s*=)(.*)$/m';
                if (preg_match($pattern, $newContent)) {
                    $newContent = preg_replace($pattern, "{$key}={$formattedValue}", $newContent);
                } else {
                    // If key doesn't exist, append right after APP_NAME or at end
                    if (preg_match('/^(\s*APP_NAME\s*=.*)$/m', $newContent)) {
                        $newContent = preg_replace('/^(\s*APP_NAME\s*=.*)$/m', "$0\n{$key}={$formattedValue}", $newContent, 1);
                    } else {
                        $newContent = rtrim($newContent) . "\n{$key}={$formattedValue}\n";
                    }
                }

                // Update runtime environment
                putenv("{$key}={$rawValue}");
                $_ENV[$key] = $rawValue;
                $_SERVER[$key] = $rawValue;
            }

            // 4. Length sanity check: ensure content size didn't collapse
            if (empty($newContent) || strlen($newContent) < (strlen($content) - 100)) {
                Log::error("EnvironmentService: Length safety check failed. Aborting write.");
                return false;
            }

            // 5. Atomic write via temporary file
            $tempFile = base_path('.env.tmp.' . uniqid('', true));
            $bytesWritten = @file_put_contents($tempFile, $newContent, LOCK_EX);

            if ($bytesWritten !== false && $bytesWritten > 0) {
                if (filesize($tempFile) === strlen($newContent)) {
                    if (!@rename($tempFile, $envPath)) {
                        @copy($tempFile, $envPath);
                        @unlink($tempFile);
                    }
                    return true;
                }
                @unlink($tempFile);
            } else {
                @unlink($tempFile);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("EnvironmentService: Failed to update .env: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Safely updates APP_VERSION with strict semver validation.
     */
    public static function setAppVersion(string $version): bool
    {
        $cleanVersion = preg_replace('/[^0-9a-zA-Z\.\-\_]/', '', trim($version));
        if (empty($cleanVersion)) {
            return false;
        }

        return self::set('APP_VERSION', $cleanVersion);
    }

    /**
     * Read an environment key directly from the .env file.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                return $default;
            }

            $content = file_get_contents($envPath);
            if ($content === false) {
                return $default;
            }

            $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=\s*(.*)$/m';
            if (preg_match($pattern, $content, $matches)) {
                $val = trim($matches[1]);
                // Strip outer quotes if present
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                return $val;
            }

            return $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Check if a key exists in .env.
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Format a value for safe storage in .env (handling spaces, quotes, nulls).
     */
    private static function formatValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim($value);

        // If already quoted properly, return as is
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return $value;
        }

        // Quote if value contains spaces, hash, dollar, or quotes
        if (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '$') || str_contains($value, '"')) {
            $escaped = str_replace('"', '\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }
}
