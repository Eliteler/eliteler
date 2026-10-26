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

namespace App\Http\Controllers;

use App\BusinessCard;
use App\Classes\GoBizUpdater2700;
use App\Currency;
use App\Http\Controllers\CustomDomainController;
use App\Mail\ContactFormMail;
use App\Plan;
use App\Services\GoBizCommonService;
use App\Setting;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    // Helper function to handle redirection when website is disabled
    private function getWebsiteRedirect()
    {
        // Retrieve redirect URL from config table
        $redirectUrl = DB::table('config')->where('config_key', 'website_redirect_url')->value('config_value');

        // Trim trailing slashes for accurate comparison
        $trimmedRedirectUrl = rtrim($redirectUrl ?? '', '/');
        $trimmedBaseUrl     = rtrim(url('/'), '/');
        $trimmedCurrentUrl  = rtrim(url()->current(), '/');

        // Prevent infinite redirect loop if redirectUrl is empty, matches base URL, or matches current URL
        if (
            !empty($trimmedRedirectUrl) &&
            $trimmedRedirectUrl !== $trimmedBaseUrl &&
            $trimmedRedirectUrl !== $trimmedCurrentUrl
        ) {
            return redirect()->to($redirectUrl);
        }

        // Default fallback to login to avoid ERR_TOO_MANY_REDIRECTS
        return redirect('/login');
    }

    // Home page
    public function index(Request $request)
    {
        $config = GoBizCommonService::config();
        $settings = GoBizCommonService::settings();

        // Pages
        $pages = GoBizCommonService::templatePageContentAll($config[93]->config_value);

        $host = str_replace(['http://', 'https://', 'www.'], '', $request->getHost());

        try {
            // Ensure storage link (run once)
            if (!File::isDirectory(public_path('storage'))) {
                File::link(storage_path('app/public'), public_path('storage'));
            }

            // Update SUB_DOMAIN in .env only if missing
            $envFile = base_path('.env');
            $envContent = file_get_contents($envFile);

            if (!preg_match('/^SUB_DOMAIN=.*$/m', $envContent)) {
                $newSubDomain = 'SUB_DOMAIN=' . $this->getSubDomain($request->getHost());

                if (preg_match('/^APP_LOG_LEVEL=.*$/m', $envContent)) {
                    $envContent = preg_replace(
                        '/^APP_LOG_LEVEL=.*$/m',
                        "$0" . PHP_EOL . $newSubDomain,
                        $envContent
                    );
                } else {
                    $envContent .= PHP_EOL . $newSubDomain;
                }

                file_put_contents($envFile, $envContent);
            }
        } catch (\Throwable $e) {
            // silent fail
        }

        /* ================= MAIN DOMAIN ================= */
        if (env('MAIN_DOMAIN') === $host) {

            if ($config[38]->config_value === 'yes') {

                $web_template = getConfigData('web_template');

                // Use first() to avoid array/object confusion
                $homePage = GoBizCommonService::templatePageContentGet('home', $web_template);

                $currency = GoBizCommonService::singleConfig('currency');

                // SEO
                $pageTitle       = trans($homePage[0]->title ?? '');
                $pageDescription = trans($homePage[0]->description ?? '');

                SEOTools::setTitle($pageTitle);
                SEOTools::setDescription($pageDescription);

                SEOMeta::setTitle($pageTitle);
                SEOMeta::setDescription($pageDescription);
                SEOMeta::addMeta(
                    'article:section',
                    ucfirst($homePage[0]->page_name ?? '') . ' - ' . $pageTitle,
                    'property'
                );
                SEOMeta::addKeyword([trans($homePage[0]->keywords ?? '')]);
                SEOMeta::setCanonical(url()->current());

                OpenGraph::setTitle($pageTitle);
                OpenGraph::setDescription($pageDescription);
                OpenGraph::setUrl(URL::full());
                OpenGraph::addImage([
                    'url'  => asset($settings->favicon),
                    'size' => 300
                ]);

                JsonLd::setTitle($pageTitle);
                JsonLd::setDescription($pageDescription);
                JsonLd::addImage(asset($settings->favicon));

                return view(
                    $web_template . '::Website.pages.index',
                    compact('homePage', 'settings', 'currency', 'config')
                );
            }

            // Redirect if website status is disabled
            return $this->getWebsiteRedirect();
        }

        /* ================= CUSTOM DOMAIN ================= */
        return app(CustomDomainController::class)->customDomain($request);
    }

    // FAQ page
    public function faq()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch FAQ page once
        $faqPage = GoBizCommonService::templatePageContentGet('faq', $web_template);

        // Abort if no data found or collection is empty
        if (blank($faqPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($faqPage)
            ? (is_object($faqPage) && method_exists($faqPage, 'first') ? $faqPage->first() : ($faqPage[0] ?? null))
            : $faqPage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.faq.index',
            compact('faqPage', 'supportPage', 'settings', 'config')
        );
    }

    // Privacy policy
    public function privacyPolicy()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch Privacy page once
        $privacyPage = GoBizCommonService::templatePageContentGet('privacy', $web_template);

        // Abort if no data found or collection is empty
        if (blank($privacyPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($privacyPage)
            ? (is_object($privacyPage) && method_exists($privacyPage, 'first') ? $privacyPage->first() : ($privacyPage[0] ?? null))
            : $privacyPage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.privacy-policy.index',
            compact('privacyPage', 'supportPage', 'settings', 'config')
        );
    }

    // Refund policy
    public function refundPolicy()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch Refund page once
        $refundPage = GoBizCommonService::templatePageContentGet('refund', $web_template);

        // Abort if no data found or collection is empty
        if (blank($refundPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($refundPage)
            ? (is_object($refundPage) && method_exists($refundPage, 'first') ? $refundPage->first() : ($refundPage[0] ?? null))
            : $refundPage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.refund-policy.index',
            compact('refundPage', 'supportPage', 'settings', 'config')
        );
    }

    // Terms and conditions
    public function termsAndConditions()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch Terms page once
        $termsPage = GoBizCommonService::templatePageContentGet('terms', $web_template);

        // Abort if no data found or collection is empty
        if (blank($termsPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($termsPage)
            ? (is_object($termsPage) && method_exists($termsPage, 'first') ? $termsPage->first() : ($termsPage[0] ?? null))
            : $termsPage;

        // Verify page item exists
        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.terms-and-conditions.index',
            compact('termsPage', 'supportPage', 'settings', 'config')
        );
    }

    // Cookie Policy
    public function cookiePolicy()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch Cookie page once
        $cookiePage = GoBizCommonService::templatePageContentGet('cookie', $web_template);

        // Abort if no data found or collection is empty
        if (blank($cookiePage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($cookiePage)
            ? (is_object($cookiePage) && method_exists($cookiePage, 'first') ? $cookiePage->first() : ($cookiePage[0] ?? null))
            : $cookiePage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.cookie-policy.index',
            compact('cookiePage', 'supportPage', 'settings', 'config')
        );
    }

    // About page
    public function about()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch About page once
        $aboutPage = GoBizCommonService::templatePageContentGet('about', $web_template);

        // Abort if no data found or collection is empty
        if (blank($aboutPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($aboutPage)
            ? (is_object($aboutPage) && method_exists($aboutPage, 'first') ? $aboutPage->first() : ($aboutPage[0] ?? null))
            : $aboutPage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.about.index',
            compact('aboutPage', 'supportPage', 'settings', 'config')
        );
    }

    // Contact page
    public function contact()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch Contact page once
        $contactPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

        // Abort if no data found or collection is empty
        if (blank($contactPage)) {
            abort(404);
        }

        // Safely get the first item from array, collection, or object
        $pageItem = is_iterable($contactPage)
            ? (is_object($contactPage) && method_exists($contactPage, 'first') ? $contactPage->first() : ($contactPage[0] ?? null))
            : $contactPage;

        if (! $pageItem) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($pageItem->title ?? '');
        $description = trans($pageItem->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($pageItem->page_name ?? '') . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($pageItem->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.contact.index',
            compact('contactPage', 'supportPage', 'settings', 'config')
        );
    }

    // Custom pages
    public function customPage($id)
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return $this->getWebsiteRedirect();
        }

        $web_template = getConfigData('web_template');

        // Fetch page once
        $page = GoBizCommonService::customPageContent($id, $web_template);

        // Abort if custom page does not exist
        if (blank($page)) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO configurations
        $title       = trans($page->title ?? '');
        $description = trans($page->description ?? '');

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta('article:section', $title, 'property');
        SEOMeta::addKeyword([trans($page->keywords ?? '')]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->favicon ?? ''),
            'size' => 300,
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->favicon ?? ''));

        return view(
            $web_template . '::Website.pages.custom-page.index',
            compact('page', 'supportPage', 'settings', 'config')
        );
    }

    // Sent mail
    public function sentMail(Request $request)
    {
        // Validate form
        if (env('RECAPTCHA_ENABLE') == 'on') {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'message' => 'required',
                'g-recaptcha-response' => 'required|recaptcha',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'message' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        try {
            // Send mail
            Mail::to(config('mail.from.address'))
                ->send(new ContactFormMail($request->all()));
        } catch (\Exception $e) {
            return back()->with('error', __('We are sorry, but your message could not be sent.'));
        }

        return redirect()->back()->with('success', __('Email sent successfully.'));
    }

    // Generate Json for manifest.json
    public function generateNew($fill)
    {
        $basicManifest = [
            'name'             => $fill['name'],
            'short_name'       => $fill['short_name'],
            'start_url'        => $fill['start_url'],
            'background_color' => '#ffffff',
            'theme_color'      => '#000000',
            'display'          => 'standalone',
            'status_bar'       => 'black',
            'splash'           => $fill['splash'],
            'icons'            => [],
            'shortcuts'        => [],
        ];

        // Icons
        if (!empty($fill['icons'])) {
            foreach ($fill['icons'] as $size => $file) {
                $fileInfo = pathinfo($file['path']);

                $basicManifest['icons'][] = [
                    'src'     => $file['path'],
                    'type'    => 'image/' . ($fileInfo['extension'] ?? 'png'),
                    'sizes'   => $size,
                    'purpose' => $file['purpose'] ?? 'any',
                ];
            }
        }

        // Shortcuts
        if (!empty($fill['shortcuts'])) {
            foreach ($fill['shortcuts'] as $shortcut) {

                $icons = [];

                if (!empty($shortcut['icons'])) {
                    $fileInfo = pathinfo($shortcut['icons']['src']);

                    $icons[] = [
                        'src'     => $shortcut['icons']['src'],
                        'type'    => 'image/' . ($fileInfo['extension'] ?? 'png'),
                        'sizes'   => '512x512',
                        'purpose' => $shortcut['icons']['purpose'] ?? 'any',
                    ];
                }

                $basicManifest['shortcuts'][] = [
                    'name'        => trans($shortcut['name']),
                    'description' => trans($shortcut['description']),
                    'url'         => $shortcut['url'],
                    'icons'       => $icons,
                ];
            }
        }

        return $basicManifest;
    }

    // Get subdomain from URL
    public function getSubDomain($url)
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;

        // Remove 'www.' if present
        $host = str_replace('www.', '', $host);

        // If host is an IP or 'localhost', return 'www'
        if (filter_var($host, FILTER_VALIDATE_IP) || $host === 'localhost') {
            return 'www';
        }

        // Break into parts
        $parts = explode('.', $host);

        // If subdomain exists (like docs.nativecode.test), return it
        if (count($parts) > 2) {
            return $parts[0];
        }

        // Default when no subdomain → "www"
        return 'www';
    }
}
