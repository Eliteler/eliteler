<?php

namespace App\Console\Commands;

use App\Services\Analytics\GeoIpUpdater;
use Illuminate\Console\Command;
use Throwable;

class GeoIpUpdate extends Command
{
    protected $signature = 'analytics:geoip-update';

    protected $description = 'Download the latest MaxMind GeoLite2-City database for visitor analytics';

    public function handle(GeoIpUpdater $updater): int
    {
        try {
            $updater->update();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('GeoLite2-City database updated.');

        return self::SUCCESS;
    }
}
