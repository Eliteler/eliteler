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

namespace App\Services\Security;

use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enterprise Security Service to prevent registration and impersonation
 * using privileged, system-reserved, or internal administrative email addresses.
 *
 * Remediates CWE-285 (Improper Authorization) and CWE-269 (Improper Privilege Management).
 */
class ReservedEmailService
{
    /**
     * Default list of privileged and system-reserved email prefixes (local-parts).
     * These represent high-value administrative, operational, or system identities.
     */
    protected const DEFAULT_RESERVED_PREFIXES = [
        // Administrative & System Control
        'admin',
        'administrator',
        'superadmin',
        'sysadmin',
        'system',
        'root',
        'master',
        'webmaster',
        'hostmaster',
        'postmaster',

        // Security, Compliance & Legal
        'security',
        'abuse',
        'compliance',
        'legal',
        'privacy',
        'audit',
        'soc',
        'cert',

        // Operations, Billing & Support
        'support',
        'help',
        'helpdesk',
        'desk',
        'service',
        'services',
        'billing',
        'payments',
        'payment',
        'finance',
        'accounting',
        'invoice',
        'invoices',
        'sales',
        'contact',
        'info',
        'information',
        'team',
        'staff',
        'operations',

        // System & Automated Mailboxes
        'noreply',
        'no-reply',
        'mailer-daemon',
        'daemon',
        'bounce',
        'bounces',
        'notification',
        'notifications',
        'alert',
        'alerts',
        'auto',
        'automated',
        'robot',
        'bot',

        // Executive & Organization Impersonation
        'ceo',
        'cto',
        'cfo',
        'coo',
        'ciso',
        'founder',
        'director',
        'manager',
        'official',
    ];

    /**
     * Common public/free webmail provider domains where generic reserved prefixes
     * (e.g. admin@gmail.com, support@yahoo.com) could be abused for impersonation.
     */
    protected const PUBLIC_WEBMAIL_DOMAINS = [
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'ymail.com',
        'outlook.com',
        'hotmail.com',
        'live.com',
        'msn.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'aol.com',
        'proton.me',
        'protonmail.com',
        'zoho.com',
        'mail.com',
        'gmx.com',
        'yandex.com',
    ];

    /**
     * Check if the EmailSecurity addon plugin is installed and active.
     */
    public static function isPluginActive(): bool
    {
        if (function_exists('plugin_active')) {
            try {
                return plugin_active('EmailSecurity');
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return file_exists(base_path('plugins/EmailSecurity/plugin.json'));
    }

    /**
     * Quick check if an email is reserved or privileged.
     */
    public static function isReserved(?string $email): bool
    {
        if (! self::isPluginActive()) {
            return false;
        }

        $result = self::checkEmail($email);
        return ! $result['allowed'];
    }

    /**
     * Detailed check of an email address.
     * Returns an array with validation status, failure reason, and localized error message.
     *
     * @return array{allowed: bool, reason: string|null, message: string|null}
     */
    public static function checkEmail(?string $email): array
    {
        // 1. If EmailSecurity addon plugin is not installed/purchased, gracefully allow registration
        if (! self::isPluginActive()) {
            return [
                'allowed' => true,
                'reason'  => null,
                'message' => null,
            ];
        }

        if (empty($email) || ! str_contains($email, '@')) {
            return [
                'allowed' => false,
                'reason'  => 'invalid_format',
                'message' => __('Invalid email format.'),
            ];
        }

        // Check if feature is globally disabled via config
        if (config('auth.reserved_emails.enabled', true) === false) {
            return ['allowed' => true, 'reason' => null, 'message' => null];
        }

        // Check if database config has disabled reserved email checks
        try {
            $dbDisabled = DB::table('config')->where('config_key', 'block_reserved_emails')->value('config_value');
            if ($dbDisabled !== null && in_array(strtolower(trim((string) $dbDisabled)), ['0', 'false', 'off'], true)) {
                return ['allowed' => true, 'reason' => null, 'message' => null];
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $normalizedEmail = strtolower(trim($email));
        $parts = explode('@', $normalizedEmail, 2);
        $rawLocalPart = $parts[0] ?? '';
        $domain = $parts[1] ?? '';

        // Handle sub-addressing / plus-addressing (e.g. "admin+promo@gmail.com" -> "admin")
        $baseLocalPart = explode('+', $rawLocalPart)[0];

        // Also normalize dot-separated variants (e.g. "a.d.m.i.n" or "super.admin")
        $strippedLocalPart = str_replace(['.', '-', '_'], '', $baseLocalPart);

        // 1. Check if email uses the platform's internal corporate domain suffix (*@domain.com)
        if (config('auth.reserved_emails.block_internal_domains', true) && self::isInternalDomain($domain)) {
            self::logSecurityAlert($normalizedEmail, 'internal_domain', "Attempted registration with platform domain suffix: {$domain}");
            return [
                'allowed' => false,
                'reason'  => 'internal_domain',
                'message' => __('This email domain is reserved for platform administration. Please use a different email address.'),
            ];
        }

        // 2. Check for collision with existing privileged staff/admin accounts (role_id in 1, 3, 4)
        if (self::isExistingAdminOrStaff($normalizedEmail)) {
            self::logSecurityAlert($normalizedEmail, 'admin_collision', "Attempted registration colliding with active administrative account.");
            return [
                'allowed' => false,
                'reason'  => 'admin_collision',
                'message' => __('This email address is reserved. Please use a different email address.'),
            ];
        }

        return [
            'allowed' => true,
            'reason'  => null,
            'message' => null,
        ];
    }

    /**
     * Get default reserved prefixes list.
     */
    public static function getDefaultPrefixes(): array
    {
        return self::DEFAULT_RESERVED_PREFIXES;
    }

    /**
     * Get the consolidated list of reserved local-part prefixes.
     * Prefers custom list configured in database config table ('reserved_email_prefixes'),
     * falling back to the enterprise DEFAULT_RESERVED_PREFIXES list.
     */
    public static function getReservedPrefixes(): array
    {
        $prefixes = null;

        // 1. Check if database config has custom prefixes
        try {
            $dbConfig = DB::table('config')->where('config_key', 'reserved_email_prefixes')->value('config_value');
            if ($dbConfig !== null && trim((string) $dbConfig) !== '') {
                $prefixes = array_values(array_filter(array_map(fn ($p) => strtolower(trim($p)), explode(',', (string) $dbConfig))));
            }
        } catch (\Throwable $e) {
            // fallback
        }

        if ($prefixes === null) {
            $prefixes = self::DEFAULT_RESERVED_PREFIXES;
        }

        $additional = config('auth.reserved_emails.additional_prefixes', []);
        if (is_string($additional)) {
            $additional = array_filter(array_map('trim', explode(',', $additional)));
        }

        if (is_array($additional) && ! empty($additional)) {
            $prefixes = array_merge($prefixes, array_map('strtolower', $additional));
        }

        return array_values(array_unique(array_filter($prefixes, fn ($p) => $p !== '')));
    }

    /**
     * Save/update reserved prefixes list in the database config table.
     *
     * @param array|string $prefixes
     */
    public static function setReservedPrefixes(array|string $prefixes): void
    {
        if (is_array($prefixes)) {
            $list = array_values(array_unique(array_filter(array_map(fn ($p) => strtolower(trim((string) $p)), $prefixes))));
            $value = implode(',', $list);
        } else {
            $parts = array_values(array_unique(array_filter(array_map(fn ($p) => strtolower(trim($p)), explode(',', $prefixes)))));
            $value = implode(',', $parts);
        }

        DB::table('config')->updateOrInsert(
            ['config_key' => 'reserved_email_prefixes'],
            ['config_value' => $value, 'updated_at' => now()]
        );
    }

    /**
     * Get the primary platform domain suffix (e.g. gobiz.com, domain.com)
     * configured for this installation.
     */
    public static function getPlatformDomain(): string
    {
        $appUrl = config('app.url');
        if (! empty($appUrl)) {
            $host = strtolower(parse_url($appUrl, PHP_URL_HOST) ?? '');
            if ($host && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                $parts = explode('.', $host);
                if (count($parts) > 2) {
                    return implode('.', array_slice($parts, -2));
                }
                return $host;
            }
        }

        $mainDomain = env('MAIN_DOMAIN');
        if (! empty($mainDomain)) {
            return strtolower(trim($mainDomain));
        }

        if (app()->runningInConsole() === false && request()->getHost()) {
            $currentHost = strtolower(request()->getHost());
            $parts = explode('.', $currentHost);
            if (count($parts) > 2) {
                return implode('.', array_slice($parts, -2));
            }
            return $currentHost;
        }

        $internal = self::getInternalDomains();
        return $internal[0] ?? 'gobiz.com';
    }

    /**
     * Get list of internal application domains that public users cannot register with.
     */
    public static function getInternalDomains(): array
    {
        $domains = [];

        // App URL host
        $appUrl = config('app.url');
        if ($appUrl) {
            $host = strtolower(parse_url($appUrl, PHP_URL_HOST) ?? '');
            if ($host) {
                $domains[] = $host;
                // Add apex domain if host is a subdomain (e.g. ip.v2u.ai -> v2u.ai)
                $hostParts = explode('.', $host);
                if (count($hostParts) > 2) {
                    $domains[] = implode('.', array_slice($hostParts, -2));
                }
            }
        }

        // MAIN_DOMAIN env
        $mainDomain = env('MAIN_DOMAIN');
        if ($mainDomain) {
            $domains[] = strtolower(trim($mainDomain));
        }

        // Current request host if available
        if (app()->runningInConsole() === false && request()->getHost()) {
            $currentHost = strtolower(request()->getHost());
            $domains[] = $currentHost;
            $currentParts = explode('.', $currentHost);
            if (count($currentParts) > 2) {
                $domains[] = implode('.', array_slice($currentParts, -2));
            }
        }

        // Additional domains from configuration
        $configuredDomains = config('auth.reserved_emails.additional_domains', []);
        if (is_string($configuredDomains)) {
            $configuredDomains = array_filter(array_map('trim', explode(',', $configuredDomains)));
        }

        if (is_array($configuredDomains)) {
            foreach ($configuredDomains as $d) {
                $domains[] = strtolower(trim((string) $d));
            }
        }

        // Filter empty and localhost/dummy testing domains if needed
        $domains = array_values(array_unique(array_filter($domains, fn ($d) => $d !== '' && ! in_array($d, ['localhost', '127.0.0.1'], true))));

        // Exclude customer custom domains (business_cards.custom_domain) so tenant domains are never treated as internal
        try {
            $tenantCustomDomains = DB::table('business_cards')
                ->whereNotNull('custom_domain')
                ->where('custom_domain', '!=', '')
                ->pluck('custom_domain')
                ->map(fn ($d) => strtolower(trim((string) $d)))
                ->all();

            if (! empty($tenantCustomDomains)) {
                $domains = array_values(array_diff($domains, $tenantCustomDomains));
            }
        } catch (\Throwable $e) {
            // ignore if DB is not available
        }

        return $domains;
    }

    /**
     * Check if a domain matches any internal application domain.
     */
    public static function isInternalDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '') {
            return false;
        }

        $internalDomains = self::getInternalDomains();

        foreach ($internalDomains as $internal) {
            if ($domain === $internal || str_ends_with($domain, '.' . $internal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of public webmail provider domains.
     */
    public static function getPublicWebmailDomains(): array
    {
        $domains = self::PUBLIC_WEBMAIL_DOMAINS;

        try {
            $dbConfig = DB::table('config')->where('config_key', 'public_webmail_domains')->value('config_value');
            if ($dbConfig !== null && trim((string) $dbConfig) !== '') {
                $custom = array_values(array_filter(array_map(fn ($d) => strtolower(trim($d)), explode(',', (string) $dbConfig))));
                $domains = array_merge($domains, $custom);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return array_values(array_unique(array_filter($domains, fn ($d) => $d !== '')));
    }

    /**
     * Check if a domain matches any public/free webmail provider domain.
     */
    public static function isPublicWebmailDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '') {
            return false;
        }

        $publicDomains = self::getPublicWebmailDomains();
        return in_array($domain, $publicDomains, true);
    }

    /**
     * Check if the email belongs to an existing administrator or staff user.
     */
    public static function isExistingAdminOrStaff(string $email): bool
    {
        try {
            return DB::table('users')
                ->whereIn('role_id', [1, 3, 4])
                ->where('email', $email)
                ->where('status', '!=', -1)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Audit database for existing customer accounts (role_id = 2) with privileged emails.
     *
     * @return Collection Collection of suspicious user records with reasons
     */
    public static function auditPrivilegedAccounts(bool $onlyCustomers = true): Collection
    {
        $query = DB::table('users');

        if ($onlyCustomers) {
            $query->where('role_id', 2);
        }

        $users = $query->select(['id', 'user_id', 'name', 'email', 'role_id', 'status', 'created_at'])->get();

        $suspicious = collect();

        foreach ($users as $user) {
            // Temporarily ignore check 3 (collision) since we are auditing the user itself
            $email = strtolower(trim($user->email ?? ''));
            if (empty($email) || ! str_contains($email, '@')) {
                continue;
            }

            $parts = explode('@', $email, 2);
            $baseLocal = explode('+', $parts[0] ?? '')[0];
            $strippedLocal = str_replace(['.', '-', '_'], '', $baseLocal);
            $domain = $parts[1] ?? '';

            $reason = null;

            $isPrefixTarget = self::isInternalDomain($domain) || self::isPublicWebmailDomain($domain);

            if (self::isInternalDomain($domain)) {
                $reason = "Internal application domain: {$domain}";
            } elseif ($isPrefixTarget && (in_array($baseLocal, self::getReservedPrefixes(), true) || in_array($strippedLocal, self::getReservedPrefixes(), true))) {
                $reason = "Reserved prefix on public/internal domain: {$baseLocal}@{$domain}";
            }

            if ($reason !== null) {
                $user->flagged_reason = $reason;
                $suspicious->push($user);
            }
        }

        return $suspicious;
    }

    /**
     * Log a high-severity security alert when an unauthorized attempt is detected.
     */
    protected static function logSecurityAlert(string $email, string $type, string $details): void
    {
        try {
            Log::warning('Security Alert: Privileged email registration attempt blocked', [
                'type'       => $type,
                'email'      => $email,
                'details'    => $details,
                'ip'         => app()->runningInConsole() ? 'CLI' : request()->ip(),
                'user_agent' => app()->runningInConsole() ? 'CLI' : request()->userAgent(),
                'timestamp'  => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            // Never allow logging failure to crash request
        }
    }
}
