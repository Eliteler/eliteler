<?php

namespace App\Console\Commands;

use App\Services\Analytics\GeoIpUpdater;
use Illuminate\Console\Command;
use Throwable;

class GeoIpBackfill extends Command
{
    protected $signature = 'analytics:geoip-backfill';

    protected $description = 'Fill in country and city for old visits from their stored IP address';

    public function handle(GeoIpUpdater $updater): int
    {
        try {
            $filled = $updater->backfill(fn (int $count) => $this->line("Resolved {$count} visits so far..."));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Done. {$filled} visits now have a location.");

        return self::SUCCESS;
    }
}
