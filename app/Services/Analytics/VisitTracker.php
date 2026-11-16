<?php

namespace App\Services\Analytics;

use App\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Throwable;

/**
 * Records one visit to a public vCard / store page: browser, OS, device,
 * MaxMind location, where the visitor came from and whether it is the
 * visitor's first hit on that card today. The row is written after the
 * response is sent, so the GeoIP lookup never slows the page.
 */
class VisitTracker
{
    /** Site names, matched against any label of the referrer host (google.co.in, m.facebook.com). */
    private const SEARCH = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex', 'ecosia', 'naver', 'brave'];

    private const SOCIAL = [
        'facebook', 'fb', 'messenger', 'instagram', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'youtube',
        'tiktok', 'pinterest', 'reddit', 'snapchat', 'threads', 'discord',
        // Short domains, matched as the host or its parent domain
        't.co', 'x.com', 'lnkd.in', 'wa.me', 't.me', 'youtu.be',
    ];

    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|curl|wget|python-requests/i';

    public function __construct(private GeoLocator $geo)
    {
    }

    /** @param object $card business_cards row (card_id, card_type) */
    public function record(Request $request, object $card): void
    {
        $data = $this->capture($request, $card);

        // Terminating callbacks stay registered in long-running processes (Octane, workers)
        // and run again on every later request, so this one saves its row only once
        $saved = false;
        app()->terminating(function () use ($data, &$saved) {
            if ($saved) {
                return;
            }
            $saved = true;

            try {
                $data += $this->geo->lookup($data['ip_address']);
                $data['is_unique'] = !Visitor::where('visitor_hash', $data['visitor_hash'])->exists();

                $visitor = new Visitor();
                foreach ($data as $column => $value) {
                    $visitor->{$column} = $value;
                }
                $visitor->save();
            } catch (Throwable $e) {
                Log::error('Visit not recorded: ' . $e->getMessage());
            }
        });
    }

    /** Everything that needs the request, taken before the response is sent. */
    private function capture(Request $request, object $card): array
    {
        $userAgent = (string) $request->userAgent();
        $agent = new Agent($request->server->all(), $userAgent);

        $browser  = $agent->browser() ?: 'Unknown';
        $platform = $agent->platform() ?: 'Unknown';
        $device   = $agent->device();

        // Agent reports Samsung's browser as Chrome; "WebKit" is the engine, not a device
        $version = $browser !== 'Unknown' ? (string) $agent->version($browser) : '';
        if (str_contains($userAgent, 'SamsungBrowser/')) {
            $browser = 'Samsung Internet';
            $version = (string) $agent->version('SamsungBrowser');
        }
        if (in_array($device, [false, '', '0', 'WebKit'], true)) {
            $device = 'Unknown';
        }
        $ip       = $this->clientIp($request);

        $referrer = $request->headers->get('referer');
        $referrerDomain = $referrer ? Str::lower((string) parse_url($referrer, PHP_URL_HOST)) : null;
        if ($referrerDomain === Str::lower($request->getHost())) {
            // Moving around inside the card itself is not a new source
            $referrer = $referrerDomain = null;
        }

        return [
            'card_id'         => $card->card_id,
            'type'            => $card->card_type,
            'ip_address'      => $ip,
            'platform'        => $platform,
            'device'          => $device,
            'browser'         => $browser,
            'browser_version' => Str::limit($version, 32, '') ?: null,
            'device_type'     => $userAgent === '' ? 'Unknown' : ($agent->isTablet() ? 'Tablet' : ($agent->isMobile() ? 'Mobile' : 'Desktop')),
            'language'        => $agent->languages()[0] ?? 'en',
            'user_agent'      => $userAgent,
            'referrer'        => $referrer ? Str::limit($referrer, 2000, '') : null,
            'referrer_domain' => $referrerDomain ?: null,
            'source'          => $this->source($request, $referrerDomain),
            'utm_source'      => $this->param($request, 'utm_source'),
            'utm_medium'      => $this->param($request, 'utm_medium'),
            'utm_campaign'    => $this->param($request, 'utm_campaign'),
            'visitor_hash'    => hash('sha256', implode('|', [$ip, $userAgent, $card->card_id, now()->toDateString()])),
            'is_bot'          => $agent->isRobot() || $userAgent === '' || preg_match(self::BOTS, $userAgent) === 1,
        ];
    }

    /** Behind Cloudflare the real visitor address is in CF-Connecting-IP. */
    private function clientIp(Request $request): string
    {
        $cloudflare = $request->headers->get('CF-Connecting-IP');

        return $cloudflare && filter_var($cloudflare, FILTER_VALIDATE_IP) ? $cloudflare : (string) $request->getClientIp();
    }

    /** Direct, QR, NFC, Search, Social or Referral. ?src=qr|nfc wins over the referrer. */
    private function source(Request $request, ?string $domain): string
    {
        $src = Str::lower((string) $request->query('src', $request->query('ref', '')));
        if ($src === 'qr') {
            return 'QR';
        }
        if ($src === 'nfc') {
            return 'NFC';
        }

        $utm = Str::lower((string) $request->query('utm_source', ''));
        $domain = $domain ?: ($utm !== '' ? $utm : null);
        if (!$domain) {
            return 'Direct';
        }

        if ($this->matches($domain, self::SEARCH)) {
            return 'Search';
        }
        if ($this->matches($domain, self::SOCIAL)) {
            return 'Social';
        }

        return 'Referral';
    }

    /** A referrer host ("m.facebook.com") or a bare utm_source ("facebook") against a list. */
    private function matches(string $domain, array $needles): bool
    {
        $labels = explode('.', $domain);
        if (count($labels) > 1) {
            array_pop($labels); // the TLD: "com" in google.com
        }

        foreach ($needles as $needle) {
            $hit = str_contains($needle, '.')
                ? $domain === $needle || str_ends_with($domain, '.' . $needle)
                : in_array($needle, $labels, true);
            if ($hit) {
                return true;
            }
        }

        return false;
    }

    private function param(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) && $value !== '' ? Str::limit($value, 100, '') : null;
    }
}
