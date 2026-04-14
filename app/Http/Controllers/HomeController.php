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
use App\Plan;
use App\Setting;
use App\Currency;
use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CustomDomainController;

class HomeController extends Controller
{
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
                    'url'  => asset($settings->site_logo),
                    'size' => 300
                ]);

                JsonLd::setTitle($pageTitle);
                JsonLd::setDescription($pageDescription);
                JsonLd::addImage(asset($settings->site_logo));

                return view(
                    $web_template . '::Website.pages.index',
                    compact('homePage', 'settings', 'currency', 'config')
                );
            }

            return redirect('/login');
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
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch FAQ page once
        $faqPage = GoBizCommonService::templatePageContentGet('faq', $web_template);

        if (! $faqPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($faqPage[0]->title);
        $description = trans($faqPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($faqPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($faqPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

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
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch Privacy page once
        $privacyPage = GoBizCommonService::templatePageContentGet('privacy', $web_template);

        if (! $privacyPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($privacyPage[0]->title);
        $description = trans($privacyPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($privacyPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($privacyPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

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
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch Refund page once
        $refundPage = GoBizCommonService::templatePageContentGet('refund', $web_template);

        if (! $refundPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($refundPage[0]->title);
        $description = trans($refundPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($refundPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($refundPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

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
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch Terms page once
        $termsPage = GoBizCommonService::templatePageContentGet('terms', $web_template);

        if (! $termsPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($termsPage[0]->title);
        $description = trans($termsPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($termsPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($termsPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

        return view(
            $web_template . '::Website.pages.terms-and-conditions.index',
            compact('termsPage', 'supportPage', 'settings', 'config')
        );
    }

    // About page
    public function about()
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch About page once
        $aboutPage = GoBizCommonService::templatePageContentGet('about', $web_template);

        if (! $aboutPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($aboutPage[0]->title);
        $description = trans($aboutPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($aboutPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($aboutPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

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
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch Contact page once
        $contactPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

        if (! $contactPage) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('footer', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($contactPage[0]->title);
        $description = trans($contactPage[0]->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta(
            'article:section',
            ucfirst($contactPage[0]->page_name) . ' - ' . $title,
            'property'
        );
        SEOMeta::addKeyword([trans($contactPage[0]->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

        return view(
            $web_template . '::Website.pages.contact.index',
            compact('contactPage', 'supportPage', 'settings', 'config')
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

    // Custom pages
    public function customPage($id)
    {
        // Load config once
        $config = GoBizCommonService::config();

        // Website enabled check
        if (($config[38]->config_value ?? null) !== 'yes') {
            return redirect('/login');
        }

        $web_template = getConfigData('web_template');

        // Fetch page once
        $page = GoBizCommonService::customPageContent($id, $web_template);

        if (! $page) {
            abort(404);
        }

        // Support pages
        $supportPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

        // Settings (single call)
        $settings = GoBizCommonService::settings();

        // SEO
        $title       = trans($page->title);
        $description = trans($page->description);

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta('article:section', $title, 'property');
        SEOMeta::addKeyword([trans($page->keywords)]);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([
            'url'  => asset($settings->site_logo),
            'size' => 300
        ]);

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addImage(asset($settings->site_logo));

        return view(
            $web_template . '::Website.pages.custom-page.index',
            compact('page', 'supportPage', 'settings', 'config')
        );
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
