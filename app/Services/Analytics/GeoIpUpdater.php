<?php

namespace App\Services\Analytics;

use App\Visitor;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PharData;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * Downloads MaxMind's free GeoLite2-City database with the account id and
 * license key from admin > Visitor Analytics, and fills in the location of
 * old visits once the database is there.
 */
class GeoIpUpdater
{
    private const DOWNLOAD_URL = 'https://download.maxmind.com/geoip/databases/GeoLite2-City/download?suffix=tar.gz';

    /** @return array{account_id: string, license_key: string, last_updated: string} */
    public static function settings(): array
    {
        $config = DB::table('config')
            ->whereIn('config_key', ['maxmind_account_id', 'maxmind_license_key', 'geoip_last_updated'])
            ->pluck('config_value', 'config_key');

        return [
            'account_id'   => (string) ($config['maxmind_account_id'] ?? ''),
            'license_key'  => (string) ($config['maxmind_license_key'] ?? ''),
            'last_updated' => (string) ($config['geoip_last_updated'] ?? ''),
        ];
    }

    public static function configured(): bool
    {
        $settings = self::settings();

        return $settings['account_id'] !== '' && $settings['license_key'] !== '';
    }

    /** @return array{available: bool, size: int|null, build_date: string|null} */
    public static function status(): array
    {
        if (!GeoLocator::available()) {
            return ['available' => false, 'size' => null, 'build_date' => null];
        }

        try {
            $metadata = (new Reader(GeoLocator::path()))->metadata();
            $built = date('Y-m-d', $metadata->buildEpoch);
        } catch (Throwable) {
            $built = null;
        }

        return ['available' => true, 'size' => filesize(GeoLocator::path()) ?: null, 'build_date' => $built];
    }

    /** Download, unpack and swap in a fresh GeoLite2-City.mmdb. Throws with a readable message on failure. */
    public function update(): void
    {
        $settings = self::settings();
        if (!self::configured()) {
            throw new RuntimeException(__('Add your MaxMind account ID and license key first.'));
        }

        $work = storage_path('app/geoip/tmp-' . uniqid());
        File::ensureDirectoryExists($work);
        $archive = $work . '/GeoLite2-City.tar.gz';

        try {
            $response = Http::withBasicAuth($settings['account_id'], $settings['license_key'])
                ->timeout(300)
                ->sink($archive)
                ->get(self::DOWNLOAD_URL);

            if ($response->status() === 401) {
                throw new RuntimeException(__('MaxMind rejected the account ID or license key.'));
            }
            if (!$response->successful()) {
                throw new RuntimeException(__('MaxMind download failed (HTTP :status).', ['status' => $response->status()]));
            }

            $mmdb = null;
            foreach (new RecursiveIteratorIterator(new PharData($archive)) as $file) {
                if (str_ends_with($file->getFilename(), '.mmdb')) {
                    $mmdb = $work . '/GeoLite2-City.mmdb';
                    copy($file->getPathname(), $mmdb);
                    break;
                }
            }
            if (!$mmdb) {
                throw new RuntimeException(__('The MaxMind download did not contain a database file.'));
            }

            // Make sure it opens before replacing the working copy
            (new Reader($mmdb))->metadata();
            rename($mmdb, GeoLocator::path());
            GeoLocator::reset();

            DB::table('config')->where('config_key', 'geoip_last_updated')
                ->update(['config_value' => now()->toDateTimeString(), 'updated_at' => now()]);
        } finally {
            File::deleteDirectory($work);
        }
    }

    /** Resolve visits still marked Unknown from their stored IP. Returns how many rows were filled in. */
    public function backfill(?callable $progress = null): int
    {
        if (!GeoLocator::available()) {
            throw new RuntimeException(__('Download the GeoLite2 database first.'));
        }

        $geo = app(GeoLocator::class);
        $filled = 0;

        // Collect the addresses first: updating rows while paging over the same filter would skip some
        $ips = Visitor::query()
            ->whereNull('country_code')
            ->where('country', 'Unknown')
            ->distinct()
            ->pluck('ip_address');

        foreach ($ips->chunk(500) as $chunk) {
            foreach ($chunk as $ip) {
                $location = $geo->lookup($ip);
                if ($location['country_code'] === null) {
                    continue;
                }
                $filled += Visitor::where('ip_address', $ip)->whereNull('country_code')->update($location);
            }
            if ($progress) {
                $progress($filled);
            }
        }

        return $filled;
    }

    public static function unresolvedCount(): int
    {
        return Visitor::whereNull('country_code')->where('country', 'Unknown')->count();
    }
}
