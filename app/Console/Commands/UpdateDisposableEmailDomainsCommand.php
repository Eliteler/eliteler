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

namespace App\Console\Commands;

use App\Services\Security\DisposableEmailService;
use Illuminate\Console\Command;

class UpdateDisposableEmailDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:update-disposable-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync latest disposable email domain blocklists from remote upstream repositories';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! DisposableEmailService::isPluginActive()) {
            $this->warn('EmailSecurity addon plugin is not installed or active.');
            return self::SUCCESS;
        }

        $this->info('Starting remote synchronization of disposable email domains...');

        $result = DisposableEmailService::syncFromRemoteSources();

        if ($result['success']) {
            $this->info(sprintf(
                '✓ Successfully synced! Total blocked domains: %s (Newly added: %d)',
                number_format($result['total']),
                $result['newly_added']
            ));
            return self::SUCCESS;
        }

        $this->error('Failed to sync disposable email domains.');
        return self::FAILURE;
    }
}
