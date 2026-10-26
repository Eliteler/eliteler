<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds public/sitemap.xml. Each URL is added once, and fixed paths are only
 * added when a route answers them (so no 404s end up in the sitemap).
 */
class SitemapService
{
    public const FILE = 'sitemap.xml';

    private const PAGES = ['/', '/about-us', '/faq', '/contact-us', '/privacy-policy', '/terms-and-conditions', '/refund-policy'];

    private const WEB_TOOLS = [
        '/html-beautifier', '/html-minifier', '/css-beautifier', '/css-minifier', '/js-beautifier', '/js-minifier',
        '/random-password-generator', '/bcrypt-password-generator', '/md5-password-generator', '/random-word-generator',
        '/text-counter', '/lorem-generator', '/emojies', '/dns-lookup', '/ip-lookup', '/whois-lookup', '/qr-maker',
    ];

    private Sitemap $sitemap;

    /** @var array<string, true> */
    private array $added = [];

    /** Categories that can be included, with how many URLs each would add. */
    public function categories(): array
    {
        $cards = DB::table('business_cards')->where('card_status', 'activated')->where('status', '1')
            ->selectRaw('card_type, COUNT(*) as total, SUM(custom_domain IS NOT NULL AND custom_domain != "") as domains')
            ->groupBy('card_type')->get()->keyBy('card_type');
        $cardUrls = fn (string $type) => (int) ($cards[$type]->total ?? 0) + (int) ($cards[$type]->domains ?? 0);

        return array_values(array_filter([
            ['id' => 'pages', 'label' => __('Website pages'), 'description' => __('Home, about, FAQ, contact, policies and your custom pages.'), 'count' => count($this->existing(self::PAGES)) + $this->customPages()->count()],
            ['id' => 'blog', 'label' => __('Blog'), 'description' => __('The blog index and every published post.'), 'count' => 1 + DB::table('blogs')->where('status', '1')->count()],
            ['id' => 'vcards', 'label' => __('Business cards'), 'description' => __('Active business cards, including custom domains.'), 'count' => $cardUrls('vcard')],
            ['id' => 'store', 'label' => __('Stores'), 'description' => __('Active WhatsApp stores, including custom domains.'), 'count' => $cardUrls('store')],
            $this->plugin('WebTools') ? ['id' => 'webtools', 'label' => __('Web tools'), 'description' => __('Free tools such as the QR maker and password generator.'), 'count' => count($this->existing(self::WEB_TOOLS))] : null,
            $this->plugin('OrderNFCSystem') ? ['id' => 'plugin_nfc', 'label' => __('NFC cards'), 'description' => __('The NFC card shop page.'), 'count' => count($this->existing(['/nfc-cards']))] : null,
            $this->plugin('Directory') ? ['id' => 'plugin_directory', 'label' => __('Directory'), 'description' => __('The business directory and its listings.'), 'count' => count($this->existing(['/directory'])) + $cardUrls('directory')] : null,
        ]));
    }

    /** @return int number of URLs written */
    public function generate(array $categories): int
    {
        $this->sitemap = Sitemap::create();
        $this->added   = [];
        $all           = in_array('all', $categories, true);
        $wants         = fn (string $id) => $all || in_array($id, $categories, true);

        if ($wants('pages')) {
            foreach ($this->existing(self::PAGES) as $path) {
                $this->add(url($path), $path === '/' ? 1.0 : 0.8, Url::CHANGE_FREQUENCY_MONTHLY);
            }
            foreach ($this->customPages() as $title) {
                $this->add(url('/p/' . Str::slug($title)), 0.6, Url::CHANGE_FREQUENCY_MONTHLY);
            }
        }

        if ($wants('blog')) {
            $this->add(url('/blogs'), 0.9, Url::CHANGE_FREQUENCY_WEEKLY);
            foreach (DB::table('blogs')->where('status', '1')->get(['slug', 'updated_at']) as $blog) {
                $this->add(url('blog/' . $blog->slug), 0.7, Url::CHANGE_FREQUENCY_WEEKLY, $blog->updated_at);
            }
        }

        foreach (['vcards' => 'vcard', 'store' => 'store'] as $id => $type) {
            if ($wants($id)) {
                $this->addCards($type, Url::CHANGE_FREQUENCY_MONTHLY);
            }
        }

        if ($wants('webtools') && $this->plugin('WebTools')) {
            foreach ($this->existing(self::WEB_TOOLS) as $path) {
                $this->add(url($path), 0.5, Url::CHANGE_FREQUENCY_YEARLY);
            }
        }

        if ($wants('plugin_nfc') && $this->plugin('OrderNFCSystem')) {
            foreach ($this->existing(['/nfc-cards']) as $path) {
                $this->add(url($path), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
            }
        }

        if ($wants('plugin_directory') && $this->plugin('Directory')) {
            foreach ($this->existing(['/directory']) as $path) {
                $this->add(url($path), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
            }
            $this->addCards('directory', Url::CHANGE_FREQUENCY_WEEKLY);
        }

        $this->sitemap->writeToFile(public_path(self::FILE));

        return count($this->added);
    }

    /** Details of the current sitemap file, or null when there is none. */
    public function status(): ?array
    {
        $path = public_path(self::FILE);

        if (! file_exists($path)) {
            return null;
        }

        return [
            'url'         => url(self::FILE),
            'generatedAt' => Carbon::createFromTimestamp(filemtime($path))->toIso8601String(),
            'sizeKb'      => round(filesize($path) / 1024, 1),
            'urls'        => substr_count((string) file_get_contents($path), '<url>'),
        ];
    }

    private function addCards(string $type, string $frequency): void
    {
        $cards = DB::table('business_cards')->where('card_type', $type)->where('card_status', 'activated')->where('status', '1')->get(['card_url', 'custom_domain']);

        foreach ($cards as $card) {
            $this->add(url('/' . $card->card_url), 0.6, $frequency);
            if (! empty($card->custom_domain)) {
                $this->add('https://' . $card->custom_domain, 0.6, $frequency);
            }
        }
    }

    private function add(string $url, float $priority, string $frequency, $updatedAt = null): void
    {
        if (isset($this->added[$url])) {
            return;
        }

        $tag = Url::create($url)->setPriority($priority)->setChangeFrequency($frequency);
        if ($updatedAt) {
            $tag->setLastModificationDate(Carbon::parse($updatedAt));
        }

        $this->sitemap->add($tag);
        $this->added[$url] = true;
    }

    /** Active custom page titles for the current website template. */
    private function customPages()
    {
        $template = DB::table('config')->where('config_key', 'web_template')->value('config_value');

        return DB::table('pages')
            ->where('template_id', $template)
            ->where('status', 'active')
            ->where('page_name', 'Custom Page')
            ->whereNotNull('section_title')->where('section_title', '!=', '')
            ->pluck('section_title')
            ->unique();
    }

    /** Keep only the paths a fixed GET route answers (not the /{id} business card catch-all). */
    private function existing(array $paths): array
    {
        $routes = Route::getRoutes();

        return array_values(array_filter($paths, function ($path) use ($routes) {
            try {
                return ! str_contains($routes->match(Request::create(url($path), 'GET'))->uri(), '{');
            } catch (\Throwable) {
                return false;
            }
        }));
    }

    private function plugin(string $name): bool
    {
        return plugin_active($name);
    }
}
