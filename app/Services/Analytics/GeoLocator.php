<?php

namespace App\Services\Analytics;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Country / city / coordinates for an IP from the local MaxMind GeoLite2-City
 * database (downloaded by `analytics:geoip-update`). Lookups are cached per IP
 * for a day. Private IPs, a missing database or any lookup error give Unknown.
 */
class GeoLocator
{
    public const DATABASE = 'app/geoip/GeoLite2-City.mmdb';

    private const UNKNOWN = [
        'country_code' => null,
        'country'      => 'Unknown',
        'region'       => 'Unknown',
        'city'         => 'Unknown',
        'latitude'     => null,
        'longitude'    => null,
        'timezone'     => null,
    ];

    private static ?Reader $reader = null;

    public static function path(): string
    {
        return storage_path(self::DATABASE);
    }

    public static function available(): bool
    {
        return is_file(self::path());
    }

    /** @return array{country_code: ?string, country: string, region: string, city: string, latitude: ?float, longitude: ?float, timezone: ?string} */
    public function lookup(?string $ip): array
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) || !self::available()) {
            return self::UNKNOWN;
        }

        return Cache::remember('geoip:' . md5($ip), now()->addDay(), function () use ($ip) {
            try {
                self::$reader ??= new Reader(self::path());
                $record = self::$reader->city($ip);

                return [
                    'country_code' => $record->country->isoCode,
                    'country'      => $record->country->name ?: 'Unknown',
                    'region'       => $record->mostSpecificSubdivision->name ?: 'Unknown',
                    'city'         => $record->city->name ?: 'Unknown',
                    'latitude'     => $record->location->latitude,
                    'longitude'    => $record->location->longitude,
                    'timezone'     => $record->location->timeZone,
                ];
            } catch (Throwable) {
                // Address not in the database, or the file is corrupt / mid-update
                return self::UNKNOWN;
            }
        });
    }

    /** Drop the open reader after the database file is replaced. */
    public static function reset(): void
    {
        self::$reader = null;
    }
}
