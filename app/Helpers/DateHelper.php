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

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

// Special sentinel value used to mark the "human diff" (relative time) option.
if (!defined('DATE_FORMAT_DIFF_FOR_HUMANS')) {
    define('DATE_FORMAT_DIFF_FOR_HUMANS', 'diffForHumans');
}

// Get date time formats
if (!function_exists('getDateTimeFormats')) {
    /**
     * Get available datetime formats.
     *
     * @return array<string, string>
     */
    function getDateTimeFormats(): array
    {
        return [
            // Standard International Formats
            'Y-m-d H:i'        => '2025-03-03 14:30 (ISO 8601, No Seconds)',
            'Y/m/d H:i'        => '2025/03/03 14:30 (Japan, Korea, No Seconds)',
            'Y.m.d H:i'        => '2025.03.03 14:30 (Alternative International)',

            // European Formats
            'd-m-Y H:i'        => '03-03-2025 14:30 (Germany, France, Italy)',
            'd/m/Y H:i'        => '03/03/2025 14:30 (Europe, Latin America)',
            'd.m.Y H:i'        => '03.03.2025 14:30 (Germany, Russia)',
            'd M Y H:i'        => '03 Mar 2025 14:30 (UK, India - 24-hour)',
            'd M Y h:i A'      => '03 Mar 2025 02:30 PM (UK, India - 12-hour)',

            // North & South America
            'm/d/Y h:i A'      => '03/03/2025 02:30 PM (USA, Canada)',
            'm-d-Y h:i A'      => '03-03-2025 02:30 PM (Alternative USA)',
            'M d, Y h:i A'     => 'Mar 03, 2025 02:30 PM (Common format, North America)',
            'd-m-Y H:i (BR)'   => '03-03-2025 14:30 (Brazil, Argentina)',

            // Asia & Middle East
            'd/m/Y H:i (Intl)' => '03/03/2025 14:30 (India, Pakistan, UAE)',
            'Y-m-d g:i A'      => '2025-03-03 2:30 PM (China, South Korea, Japan - 12-hour)',
            'Y-m-d H:i (Asia)' => '2025-03-03 14:30 (Asia - 24-hour)',

            // Full Date & Time Formats
            'l, d F Y h:i A'   => 'Monday, 03 March 2025 02:30 PM (Full Date & Time)',
            'l, d M Y H:i'     => 'Monday, 03 Mar 2025 14:30 (Full Date, 24-hour)',
            'D, d M Y h:i A'   => 'Mon, 03 Mar 2025 02:30 PM (Short Weekday, 12-hour)',
            'D, d M Y H:i'     => 'Mon, 03 Mar 2025 14:30 (Short Weekday, 24-hour)',

            // Diff for human (ex: 3 days ago)
            DATE_FORMAT_DIFF_FOR_HUMANS => '3 days ago (Relative / Human Readable)',
        ];
    }
}

// The admin's timezone and date format (Settings → General), read once per request
if (! function_exists('userDateSettings')) {
    /**
     * By key, not by row number: code that reads config rows by position breaks silently
     * when rows move. Kept on the request, not in a static: Octane and queue workers reuse
     * the process, and a static would keep the old format after the admin changes it.
     *
     * @return array{timezone: string, format: string} format is a PHP date format, or DATE_FORMAT_DIFF_FOR_HUMANS
     */
    function userDateSettings(): array
    {
        $memo = 'gobiz_date_settings';
        if (request()->attributes->has($memo)) {
            return request()->attributes->get($memo);
        }

        $config = DB::table('config')->whereIn('config_key', ['timezone', 'date_time_format'])->pluck('config_value', 'config_key');
        $format = trim((string) ($config['date_time_format'] ?? '')) ?: 'M d, Y h:i A';

        $settings = [
            'timezone' => trim((string) ($config['timezone'] ?? '')) ?: (config('app.timezone') ?: 'UTC'),
            // Some options carry a label, e.g. "d-m-Y H:i (BR)": not part of the format
            // (its letters would be read as format characters)
            'format'   => $format === DATE_FORMAT_DIFF_FOR_HUMANS ? $format : (trim(preg_replace('/\s*\([^)]*\)/', '', $format)) ?: 'M d, Y h:i A'),
        ];
        request()->attributes->set($memo, $settings);

        return $settings;
    }
}

// Older name of userDateSettings()
if (! function_exists('getDateHelperSettings')) {
    /** @return array{timezone: string, format: string} */
    function getDateHelperSettings(): array
    {
        return userDateSettings();
    }
}

// A date in the admin's timezone and the user's language
if (! function_exists('userCarbonDate')) {
    /**
     * Plain calendar dates ("2025-03-03": bookings, check-in days) stay that day: converting
     * them from midnight UTC would show the day before in timezones behind UTC.
     * Null when the value isn't a date.
     */
    function userCarbonDate(string|\DateTimeInterface $date): ?Carbon
    {
        $timezone = userDateSettings()['timezone'];

        try {
            $carbonDate = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date))
                ? Carbon::parse(trim($date), $timezone)
                : Carbon::parse($date)->setTimezone($timezone);
        } catch (\Throwable) {
            return null;
        }

        return $carbonDate->locale(App::getLocale() ?: 'en');
    }
}

// Format a date and time the way the admin chose (timezone, format, the user's language)
if (! function_exists('formatDateForUser')) {
    /**
     * @param  string|\DateTimeInterface|null  $date
     * @param  string  $format  kept for older callers; the admin's setting decides
     * @param  bool  $absolute  always a real date, also when the admin chose "3 days ago"
     *                          (invoices, emails, "active until …" messages)
     */
    function formatDateForUser(string|\DateTimeInterface|null $date, string $format = 'full', bool $absolute = false): ?string
    {
        if (empty($date) || ! ($carbonDate = userCarbonDate($date))) {
            return null;
        }

        $configuredFormat = userDateSettings()['format'];

        // "3 days ago" style: Carbon translates it into the user's language
        if ($configuredFormat === DATE_FORMAT_DIFF_FOR_HUMANS) {
            return $absolute ? $carbonDate->translatedFormat('M d, Y h:i A') : $carbonDate->diffForHumans();
        }

        return $carbonDate->translatedFormat($configuredFormat);
    }
}

// Format only the date part of the admin's chosen format
if (! function_exists('formatDateOnlyForUser')) {
    /**
     * @param  string|\DateTimeInterface|null  $date
     * @param  bool  $absolute  always a real date, also when the admin chose "3 days ago"
     */
    function formatDateOnlyForUser(string|\DateTimeInterface|null $date, bool $absolute = false): ?string
    {
        if (empty($date) || ! ($carbonDate = userCarbonDate($date))) {
            return null;
        }

        $configuredFormat = userDateSettings()['format'];

        // Relative time has no separate date part
        if ($configuredFormat === DATE_FORMAT_DIFF_FOR_HUMANS) {
            return $absolute ? $carbonDate->translatedFormat('M d, Y') : $carbonDate->diffForHumans();
        }

        // The format without its time characters (H h G g i s A a and ":"), tidied up
        $dateOnlyFormat = trim(preg_replace('/\s+/', ' ', preg_replace('/[HhGgisAa:]+/', '', $configuredFormat)), ' ,');

        return $carbonDate->translatedFormat($dateOnlyFormat ?: 'M d, Y');
    }
}
