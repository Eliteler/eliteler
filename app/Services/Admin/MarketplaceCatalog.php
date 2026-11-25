<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Throwable;

class MarketplaceCatalog
{
    private const BASE_URL = 'https://gobizsaas.com/data/';

    private const ID_KEYS = ['plugins' => 'plugin_id', 'templates' => 'template_id'];

    private const CACHE_KEY = 'gobiz:marketplace:catalogs:v1';

    private const CACHE_TTL_SECONDS = 24 * 60 * 60;

    private const EMPTY_CATALOGS = ['plugins' => null, 'templates' => null];

    public function dashboardCatalogs(): array
    {
        try {
            $cache = Cache::store();
            $catalogs = $cache->get(self::CACHE_KEY);
            if ($catalogs !== null) {
                return $catalogs;
            }

            $remember = fn () => $cache->remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, $this->fetchCatalogs(...));
            $store = $cache->getStore();
            if ($store instanceof LockProvider) {
                return $store->lock(self::CACHE_KEY . ':refresh', 10)->get($remember)
                    ?: $cache->get(self::CACHE_KEY, self::EMPTY_CATALOGS);
            }

            return $remember();
        } catch (Throwable $error) {
            report($error);

            return self::EMPTY_CATALOGS;
        }
    }

    private function fetchCatalogs(): array
    {
        try {
            $responses = Http::pool(function (Pool $pool) {
                foreach (array_keys(self::ID_KEYS) as $catalog) {
                    $pool->as($catalog)->acceptJson()->connectTimeout(2)->timeout(4)
                        ->withoutRedirecting()->get(self::BASE_URL . $catalog . '.json');
                }
            }, concurrency: count(self::ID_KEYS));
        } catch (Throwable $error) {
            report($error);
            $responses = [];
        }

        $catalogs = [];
        foreach (self::ID_KEYS as $catalog => $idKey) {
            $response = $responses[$catalog] ?? null;
            $body = $response instanceof Response && $response->successful() ? $response->body() : '';
            $catalogs[$catalog] = $this->decode($body, $idKey);
        }

        return $catalogs;
    }

    /** The Plugins and Templates pages only read the cached data. */
    public function read(string $catalog): ?array
    {
        try {
            return Cache::get(self::CACHE_KEY)[$catalog] ?? null;
        } catch (Throwable $error) {
            report($error);

            return null;
        }
    }

    public function availableUpdates(string $catalog, ?array $versions, iterable $installed): ?array
    {
        if ($versions === null) {
            return null;
        }

        $updates = [];
        foreach ($installed as $manifest) {
            $id = $manifest[self::ID_KEYS[$catalog]] ?? basename($manifest['path'] ?? '');
            if (! is_string($id) || ! isset($versions[$id])) {
                continue;
            }

            $current = $manifest['version'] ?? null;
            $latest = $versions[$id];
            if ($this->isReleaseVersion($current) && $this->isReleaseVersion($latest)
                && version_compare($latest, $current, '>')) {
                $updates[$id] = $latest;
            }
        }

        return $updates;
    }

    public function listingsFor(Collection $listings, Collection $installed, string $openUrlKey): Collection
    {
        if ($listings->isEmpty()) {
            return $listings;
        }

        $installedById = $installed->keyBy('id');

        return $listings->map(function (array $listing) use ($installedById, $openUrlKey) {
            $installed = $installedById->get($listing['id']);

            return $listing + [
                'installed' => $installed !== null,
                'openUrl' => $installed[$openUrlKey] ?? null,
                'updateVersion' => $installed['updateVersion'] ?? null,
            ];
        });
    }

    private function decode(string $body, string $idKey): ?array
    {
        if (str_starts_with($body, "\xEF\xBB\xBF")) {
            $body = substr($body, 3);
        }
        $rows = json_decode($body);
        if (! is_array($rows)) {
            return null;
        }

        $versions = [];
        $listings = [];
        foreach ($rows as $row) {
            if (! is_object($row)) {
                return null;
            }
            $id = $row->{$idKey} ?? null;
            $version = $row->version ?? null;
            $hasId = is_string($id) && trim($id) !== '';
            $hasVersion = $this->isReleaseVersion($version);

            // Older unpublished entries may have no release version.
            if (($row->status ?? null) === 'unpublished' && (! $hasId || ! $hasVersion)) {
                continue;
            }
            if (! $hasId || ! $hasVersion || isset($versions[$id])) {
                return null;
            }

            $versions[$id] = $version;
            if (($row->status ?? null) === 'published'
                && is_string($row->name ?? null) && trim($row->name) !== ''
                && $this->isWebUrl($row->url ?? null)) {
                $listings[] = [
                    'id' => $id,
                    'name' => $row->name,
                    'description' => is_string($row->description ?? null) ? $row->description : null,
                    'logo' => $this->isWebUrl($row->logo ?? null) ? $row->logo : null,
                    'url' => $row->url,
                ];
            }
        }

        return $versions === [] ? null : compact('versions', 'listings');
    }

    private function isWebUrl(mixed $url): bool
    {
        return is_string($url) && preg_match('~^https?://~i', $url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function isReleaseVersion(mixed $version): bool
    {
        return is_string($version) && preg_match('/^\d+\.\d+\.\d+$/D', $version) === 1;
    }
}
