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

use App\Services\Security\ReservedEmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditPrivilegedEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit-privileged-emails 
                            {--disable : Deactivate identified privileged customer accounts}
                            {--all : Scan all accounts instead of only customer (role_id = 2) accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit database for accounts created with privileged or system-reserved email addresses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $scanAll = (bool) $this->option('all');
        $shouldDisable = (bool) $this->option('disable');

        $this->info('Starting privileged email audit...');
        $this->line($scanAll ? 'Scanning ALL user accounts...' : 'Scanning customer accounts (role_id = 2)...');

        $flagged = ReservedEmailService::auditPrivilegedAccounts(! $scanAll);

        if ($flagged->isEmpty()) {
            $this->info('✓ Audit completed: No suspicious or privileged accounts found.');
            return self::SUCCESS;
        }

        $this->warn(sprintf('⚠ Found %d account(s) using privileged or system-reserved emails:', $flagged->count()));

        $headers = ['ID', 'User ID', 'Name', 'Email', 'Role', 'Status', 'Flagged Reason', 'Created At'];
        $rows = [];

        foreach ($flagged as $user) {
            $statusLabel = match ((int) $user->status) {
                1       => 'Active',
                0       => 'Inactive',
                -1      => 'Deleted',
                default => (string) $user->status,
            };

            $roleLabel = match ((int) $user->role_id) {
                1       => 'Super Admin',
                2       => 'Customer',
                3       => 'Staff',
                4       => 'Manager',
                default => (string) $user->role_id,
            };

            $rows[] = [
                $user->id,
                $user->user_id,
                $user->name,
                $user->email,
                $roleLabel,
                $statusLabel,
                $user->flagged_reason ?? 'Privileged email',
                $user->created_at ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);

        if ($shouldDisable) {
            $deactivatedCount = 0;

            foreach ($flagged as $user) {
                // Only deactivate customer accounts (role_id = 2) to protect real admins
                if ((int) $user->role_id === 2 && (int) $user->status === 1) {
                    DB::table('users')->where('id', $user->id)->update([
                        'status' => 0,
                    ]);

                    Log::warning('Security Audit: Privileged customer account deactivated', [
                        'user_id' => $user->user_id,
                        'email'   => $user->email,
                        'reason'  => $user->flagged_reason ?? 'Privileged email',
                    ]);

                    $deactivatedCount++;
                }
            }

            $this->info("✓ Successfully deactivated {$deactivatedCount} active customer account(s).");
        } else {
            $this->line('');
            $this->comment('Tip: Run with "--disable" flag to automatically deactivate flagged customer accounts:');
            $this->line('  php artisan security:audit-privileged-emails --disable');
        }

        return self::SUCCESS;
    }
}
