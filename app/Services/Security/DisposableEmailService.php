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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enterprise Security Service to detect and block disposable, temporary,
 * or throwaway email addresses commonly used by automated registration bots and scripts.
 *
 * Implements 4-Layer Defense:
 * 1. 10,000+ Expanded Local Domain Hash-Map (O(1) lookup).
 * 2. Live Upstream Remote Synchronization (from trusted GitHub repositories).
 * 3. Real-Time DNS MX & Disposable Mail Server Cluster Inspection.
 * 4. Custom Admin Blocklist & Whitelist.
 */
class DisposableEmailService
{
    /**
     * In-memory cache of normalized disposable domains.
     */
    protected static ?array $cachedDomains = null;

    /**
     * Known disposable mail exchange (MX) hostname signatures.
     * Temp-Mail services rotating domains share these common mail exchange servers.
     */
    protected const DISPOSABLE_MX_PATTERNS = [
        'mail.bcough.com',
        'tempmail',
        'temp-mail',
        'mailinator',
        'guerrillamail',
        'sharklasers',
        'grr.la',
        'yopmail',
        'trashmail',
        'dispostable',
        '10minutemail',
        'throwawaymail',
        'mohmal',
        'fakeinbox',
        'inboxkitten',
        'getairmail',
        'nada.ltd',
        'crazymailing',
        'emailondeck',
        'burnermail',
        'tempail',
        'armyspy.com',
        'cuvox.de',
        'dayrep.com',
        'fleckens.hu',
        'gustr.com',
        'jourrapide.com',
        'rhyta.com',
        'superrito.com',
        'teleworm.us',
        'einrot.com',
    ];

    /**
     * Known IP addresses operated by temp-mail infrastructure nodes.
     */
    protected const DISPOSABLE_MX_IPS = [
        '138.197.228.18', // Temp-Mail.org / bcough.com mail relay node
    ];

    /**
     * Upstream remote blocklist sources for one-click live sync.
     */
    protected const REMOTE_SYNC_SOURCES = [
        'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/master/disposable_email_blocklist.conf',
        'https://raw.githubusercontent.com/disposable/disposable-email-domains/master/domains.txt',
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
     * Check if an email address belongs to a disposable/temporary email provider.
     */
    public static function isDisposable(?string $email): bool
    {
        if (! self::isPluginActive()) {
            return false;
        }

        $result = self::checkEmail($email);
        return ! $result['allowed'];
    }

    /**
     * Check email for disposable domains.
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

        // Global disable toggle via config
        if (config('auth.disposable_emails.enabled', true) === false) {
            return ['allowed' => true, 'reason' => null, 'message' => null];
        }

        // Check if database config has disabled this feature
        try {
            $dbDisabled = DB::table('config')->where('config_key', 'block_disposable_emails')->value('config_value');
            if ($dbDisabled !== null && in_array(strtolower(trim((string) $dbDisabled)), ['0', 'false', 'off'], true)) {
                return ['allowed' => true, 'reason' => null, 'message' => null];
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $normalizedEmail = strtolower(trim($email));
        $parts = explode('@', $normalizedEmail, 2);
        $domain = $parts[1] ?? '';

        if ($domain === '') {
            return [
                'allowed' => false,
                'reason'  => 'invalid_domain',
                'message' => __('Invalid email domain.'),
            ];
        }

        // 1. Check if domain is whitelisted by Admin
        if (self::isWhitelisted($domain)) {
            return ['allowed' => true, 'reason' => null, 'message' => null];
        }

        // 2. Check if domain is in disposable hash-map (O(1))
        if (self::domainIsDisposable($domain)) {
            self::logSecurityAlert($normalizedEmail, $domain, 'domain_blocklist');
            return [
                'allowed' => false,
                'reason'  => 'disposable_domain',
                'message' => __('Disposable or temporary email addresses are not allowed. Please use a valid email address.'),
            ];
        }

        // 3. Real-Time DNS MX & Disposable Mail Server Cluster Inspection
        if (config('auth.disposable_emails.check_mx', true) && self::hasDisposableMx($domain)) {
            self::logSecurityAlert($normalizedEmail, $domain, 'disposable_mx_cluster');
            return [
                'allowed' => false,
                'reason'  => 'disposable_domain',
                'message' => __('Disposable or temporary email addresses are not allowed. Please use a valid email address.'),
            ];
        }

        return ['allowed' => true, 'reason' => null, 'message' => null];
    }

    /**
     * Fast check if a domain is in the disposable list.
     */
    public static function domainIsDisposable(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $domains = self::getDisposableDomains();

        // Exact match
        if (isset($domains[$domain])) {
            return true;
        }

        // Subdomain match (e.g. sub.tempmail.com -> tempmail.com)
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            $apex = implode('.', array_slice($parts, -2));
            if (isset($domains[$apex])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inspect DNS MX records to detect unlisted rotating disposable domains
     * that share known disposable mail exchangers or infrastructure IPs.
     */
    public static function hasDisposableMx(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '' || ! function_exists('getmxrr')) {
            return false;
        }

        try {
            $mxHosts = [];
            $weights = [];

            // Attempt fast MX lookup with error suppression
            if (! @getmxrr($domain, $mxHosts, $weights) || empty($mxHosts)) {
                return false;
            }

            foreach ($mxHosts as $mx) {
                $mxLower = strtolower(trim($mx));

                // 1. Check against known disposable MX hostname patterns
                foreach (self::DISPOSABLE_MX_PATTERNS as $pattern) {
                    if ($mxLower === $pattern || str_contains($mxLower, $pattern)) {
                        return true;
                    }
                }

                // 2. Check if MX resolves to a known disposable relay IP
                if (function_exists('gethostbyname')) {
                    $ip = @gethostbyname($mxLower);
                    if ($ip && in_array($ip, self::DISPOSABLE_MX_IPS, true)) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Never break registration on DNS timeouts or socket errors
        }

        return false;
    }

    /**
     * Get compiled hash-map of all disposable domains.
     *
     * @return array<string, bool> Map of domain => true for O(1) lookup
     */
    public static function getDisposableDomains(): array
    {
        if (self::$cachedDomains !== null) {
            return self::$cachedDomains;
        }

        $list = [];

        // 1. Load from local JSON data file (10,400+ domains)
        $jsonPath = resource_path('data/disposable_email_domains.json');
        if (file_exists($jsonPath)) {
            $fileData = json_decode(file_get_contents($jsonPath), true);
            if (is_array($fileData)) {
                foreach ($fileData as $d) {
                    $d = strtolower(trim((string) $d));
                    if ($d !== '') {
                        $list[$d] = true;
                    }
                }
            }
        }

        // 2. Load custom blocklist from database config
        $custom = self::getCustomBlocklist();
        foreach ($custom as $cd) {
            $cd = strtolower(trim((string) $cd));
            if ($cd !== '') {
                $list[$cd] = true;
            }
        }

        self::$cachedDomains = $list;

        return self::$cachedDomains;
    }

    /**
     * Get list of custom disposable domains configured by Admin.
     */
    public static function getCustomBlocklist(): array
    {
        try {
            $custom = DB::table('config')->where('config_key', 'disposable_emails_custom_blocklist')->value('config_value');
            if ($custom !== null && trim((string) $custom) !== '') {
                return array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $custom)))));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return [];
    }

    /**
     * Save custom disposable domains configured by Admin.
     */
    public static function setCustomBlocklist(array|string|null $domains = ''): void
    {
        $domains = $domains ?? '';

        if (is_array($domains)) {
            $clean = array_values(array_unique(array_filter(array_map(fn ($d) => strtolower(trim((string) $d)), $domains))));
            $value = implode(',', $clean);
        } else {
            $rawParts = preg_split('/[\r\n,]+/', (string) $domains, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $parts = array_values(array_unique(array_filter(array_map(fn ($d) => strtolower(trim((string) $d)), $rawParts))));
            $value = implode(',', $parts);
        }

        DB::table('config')->updateOrInsert(
            ['config_key' => 'disposable_emails_custom_blocklist'],
            ['config_value' => $value, 'updated_at' => now()]
        );

        self::$cachedDomains = null;
    }

    /**
     * Check if a domain is explicitly whitelisted.
     */
    public static function isWhitelisted(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '') {
            return false;
        }

        $whitelist = config('auth.disposable_emails.whitelist', []);
        if (is_string($whitelist)) {
            $whitelist = array_filter(array_map('trim', explode(',', $whitelist)));
        }

        if (is_array($whitelist) && in_array($domain, array_map('strtolower', $whitelist), true)) {
            return true;
        }

        try {
            $dbWhitelist = DB::table('config')->where('config_key', 'disposable_emails_whitelist')->value('config_value');
            if ($dbWhitelist !== null && trim((string) $dbWhitelist) !== '') {
                $dbList = array_map('strtolower', array_filter(array_map('trim', explode(',', (string) $dbWhitelist))));
                if (in_array($domain, $dbList, true)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return false;
    }

    /**
     * Live sync newest disposable domains from upstream GitHub repositories.
     *
     * @return array{success: bool, total: int, newly_added: int, synced_at: string, message: string}
     */
    public static function syncFromRemoteSources(): array
    {
        $destPath = resource_path('data/disposable_email_domains.json');

        $existing = [];
        if (file_exists($destPath)) {
            $existing = json_decode(file_get_contents($destPath), true) ?: [];
        }
        $initialCount = count($existing);

        $fetchedCount = 0;

        foreach (self::REMOTE_SYNC_SOURCES as $url) {
            $response = null;

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_USERAGENT, 'GoBiz-EmailSecurity/1.0');
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200 || empty($response)) {
                    continue;
                }
            } elseif (ini_get('allow_url_fopen')) {
                $response = @file_get_contents($url);
                if ($response === false) {
                    continue;
                }
            }

            if (! empty($response)) {
                $lines = explode("\n", $response);
                foreach ($lines as $line) {
                    $line = strtolower(trim($line));
                    if ($line !== '' && ! str_starts_with($line, '#') && str_contains($line, '.')) {
                        $existing[] = $line;
                        $fetchedCount++;
                    }
                }
            }
        }

        // Guarantee bcough.com and other recent temp-mail domains are always protected
        $guaranteed = [
            'bcough.com',
            'drepam.com',
            'vintomper.com',
            'fawkes.org',
            'omvv.com',
            'tapi.re',
            'mohmal.com',
            'mohmal.im',
            'tempmail.plus',
            'tempmailo.com',
            'internxt.com',
            'disposablemail.com',
        ];

        foreach ($guaranteed as $g) {
            $existing[] = $g;
        }

        $clean = array_values(array_unique(array_filter(array_map('trim', $existing))));
        sort($clean, SORT_STRING);

        // Save back to JSON file
        @file_put_contents($destPath, json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $totalCount = count($clean);
        $newlyAdded = max(0, $totalCount - $initialCount);
        $now = now()->toIso8601String();

        // Update database metadata
        try {
            DB::table('config')->updateOrInsert(
                ['config_key' => 'disposable_domains_count'],
                ['config_value' => (string) $totalCount, 'updated_at' => now()]
            );

            DB::table('config')->updateOrInsert(
                ['config_key' => 'disposable_domains_last_synced_at'],
                ['config_value' => $now, 'updated_at' => now()]
            );
        } catch (\Throwable $e) {
            // ignore
        }

        // Reset in-memory cache
        self::$cachedDomains = null;

        return [
            'success'     => true,
            'total'       => $totalCount,
            'newly_added' => $newlyAdded,
            'synced_at'   => $now,
            'message'     => __('Disposable domains blocklist updated successfully with :count domains.', ['count' => number_format($totalCount)]),
        ];
    }

    /**
     * Get operational statistics for the Admin UI.
     */
    public static function getStats(): array
    {
        $domains = self::getDisposableDomains();
        $total = count($domains);

        $lastSynced = null;
        try {
            $lastSynced = DB::table('config')->where('config_key', 'disposable_domains_last_synced_at')->value('config_value');
        } catch (\Throwable $e) {
            // ignore
        }

        return [
            'total_domains'     => $total,
            'custom_domains'    => self::getCustomBlocklist(),
            'last_synced_at'    => $lastSynced,
        ];
    }

    /**
     * Log a security alert for disposable email attempt.
     */
    protected static function logSecurityAlert(string $email, string $domain, string $detectionType = 'domain_blocklist'): void
    {
        try {
            Log::info("Security: Disposable email registration attempt rejected ({$detectionType})", [
                'email'          => $email,
                'domain'         => $domain,
                'detection_type' => $detectionType,
                'ip'             => app()->runningInConsole() ? 'CLI' : request()->ip(),
                'user_agent'     => app()->runningInConsole() ? 'CLI' : request()->userAgent(),
                'timestamp'      => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
