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

namespace App\Classes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The NativeCode license server's view of this installation: license,
 * latest version and support plan. Used by Software Updates (lookup) and
 * the dashboard notices (dashboardStatus).
 */
class AvailableVersion
{
    private const CHECK_URL = 'https://verify.nativecode.in/check-update';

    private const CACHE_KEY = 'gobiz.update_status';

    /** Ask the license server about this installation. */
    public function lookup(?string $purchaseCode, ?string $email, int $timeout = 20): array
    {
        try {
            $response = (new \GuzzleHttp\Client(['timeout' => $timeout, 'connect_timeout' => min($timeout, 10)]))->post(self::CHECK_URL, [
                'headers'     => ['Accept' => 'application/json', 'User-Agent' => 'Mozilla/5.0 (compatible; UpdateChecker/1.0)'],
                'form_params' => [
                    'purchase_code' => $purchaseCode,
                    'server_name'   => self::serverName(),
                    'version'       => $this->config('app_version'),
                    'email'         => $email,
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
        } catch (\Throwable $e) {
            report($e);
            $data = null;
        }

        if (! is_array($data)) {
            return ['ok' => false, 'message' => __('Couldn’t reach the update server. Please try again in a few minutes.')];
        }

        if (empty($data['status'])) {
            return ['ok' => false, 'message' => __((string) ($data['message'] ?? 'License check failed.'))];
        }

        return [
            'ok'              => true,
            'message'         => (string) ($data['message'] ?? ''),
            'latestVersion'   => (string) ($data['version'] ?? ''),
            'updateAvailable' => (bool) ($data['update'] ?? false),
            'notes'           => (string) ($data['notes'] ?? ''),
            'license'         => (string) ($data['license'] ?? ''),
            'supportExpired'  => isset($data['support_remaining_days']) && $data['support_remaining_days'] <= 0,
        ];
    }

    /**
     * Update / support status for the dashboard notices, or null when the
     * license server couldn't confirm the license. Cached so the dashboard
     * never waits on the server: 6 hours after an answer, 30 minutes after a
     * failure. Cleared with the app cache (and by every software update).
     *
     * @return array{updateAvailable: bool, latestVersion: string, supportExpired: bool}|null
     */
    public function dashboardStatus(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached ?: null;
        }

        return $this->remember($this->lookup(config('app.code'), $this->config('activation_email_address'), 5));
    }

    /**
     * Store a lookup result as the dashboard status. Software Updates calls
     * this too, so a successful check there shows on the dashboard right away
     * instead of after an earlier failure or answer expires.
     */
    public function remember(array $result): ?array
    {
        $status = $result['ok']
            ? ['updateAvailable' => $result['updateAvailable'], 'latestVersion' => $result['latestVersion'], 'supportExpired' => $result['supportExpired']]
            : null;

        Cache::put(self::CACHE_KEY, $status ?? [], $status ? now()->addHours(6) : now()->addMinutes(30));

        return $status;
    }

    /**
     * The domain the license is registered to: the APP_URL host from .env
     * rather than the request, since requests through a customer's custom
     * domain, the CLI or queue workers would report another name.
     */
    public static function serverName(): string
    {
        return strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'LOCAL.TEST'));
    }

    public function config(string $key): ?string
    {
        return DB::table('config')->where('config_key', $key)->value('config_value');
    }
}
