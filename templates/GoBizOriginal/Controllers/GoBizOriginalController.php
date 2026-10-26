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

namespace Templates\GoBizOriginal\Controllers;

use App\Announcement;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Page;
use App\Support\Admin\PluginPage;
use App\WebPopup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

/**
 * Admin pages of the GoBiz Original web template. The pages are React:
 * templates/GoBizOriginal/resources/js/{pages,edit-page,custom-page,config}.tsx
 * (npm run plugin:build -- GoBizOriginal). The public website (Views/Website) stays Blade.
 */
class GoBizOriginalController extends Controller
{
    private const TEMPLATE = 'GoBizOriginal';

    /** Website pages in list order: page_name => [label, public path or null when it's part of another page] */
    private const PAGES = [
        'home'    => ['Home', '/'],
        'about'   => ['About us', '/about-us'],
        'pricing' => ['Pricing', null],
        'faq'     => ['FAQ', '/faq'],
        'contact' => ['Contact us', '/contact-us'],
        'privacy' => ['Privacy policy', '/privacy-policy'],
        'refund'  => ['Refund policy', '/refund-policy'],
        'terms'   => ['Terms & conditions', '/terms-and-conditions'],
        'footer'  => ['Footer', null],
    ];

    /** Pages the site can't work without: they can't be turned off */
    private const LOCKED = ['home', 'footer'];

    /** Colour setting value => swatch (Tailwind 600) */
    private const COLORS = [
        'blue' => '#2563eb', 'indigo' => '#4f46e5', 'violet' => '#7c3aed', 'purple' => '#9333ea', 'fuchsia' => '#c026d3',
        'pink' => '#db2777', 'rose' => '#e11d48', 'red' => '#dc2626', 'orange' => '#ea580c', 'amber' => '#d97706',
        'yellow' => '#ca8a04', 'lime' => '#65a30d', 'green' => '#16a34a', 'emerald' => '#059669', 'teal' => '#0d9488',
        'cyan' => '#0891b2', 'sky' => '#0284c7', 'slate' => '#475569', 'gray' => '#4b5563', 'zinc' => '#52525b',
        'neutral' => '#525252', 'stone' => '#57534e',
    ];

    private const IMAGE_RULE = 'mimes:jpeg,png,jpg,gif,webp,svg';

    // Website pages and custom pages (two tabs of one page)
    public function index()
    {
        return $this->pagesPage('pages');
    }

    public function customPagesIndex()
    {
        return $this->pagesPage('custom');
    }

    private function pagesPage(string $tab)
    {
        $rows = DB::table('pages')->where('template_id', self::TEMPLATE)->whereIn('page_name', array_keys(self::PAGES))
            ->get(['page_name', 'status'])->keyBy('page_name');

        $staticPages = collect(self::PAGES)
            ->filter(fn ($page, $key) => $rows->has($key))
            ->map(fn ($page, $key) => [
                'key'           => $key,
                'name'          => __($page[0]),
                'path'          => $page[1],
                'active'        => $rows[$key]->status === 'active',
                'canDeactivate' => ! in_array($key, self::LOCKED, true),
            ])->values();

        $customPages = DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', 'Custom Page')
            ->orderByDesc('created_at')->get()
            ->map(fn ($page) => [
                'id'      => $page->id,
                'name'    => (string) $page->section_name,
                'slug'    => (string) $page->section_title,
                'active'  => $page->status === 'active',
                'updated' => $page->updated_at ? Carbon::parse($page->updated_at)->toIso8601String() : null,
            ]);

        return PluginPage::template(self::TEMPLATE, 'pages', [
            'tab'         => $tab,
            'siteUrl'     => url('/'),
            'staticPages' => $staticPages,
            'customPages' => $customPages,
        ]);
    }

    // Page editor: each page is a set of text rows (pages.section_title is the field, section_name the group)
    public function editPage($id)
    {
        abort_unless(array_key_exists($id, self::PAGES), 404);

        $rows = DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', $id)->orderBy('id')->get();
        abort_unless($rows->isNotEmpty(), 404);

        $groups = $rows->groupBy(fn ($row) => (string) $row->section_name)->map(fn ($fields, $group) => [
            'name'   => __(self::label($group)),
            'fields' => $fields->map(fn ($row) => [
                'id'      => $row->id,
                'label'   => __($row->section_title === 'desc' ? 'Description' : self::label((string) $row->section_title)),
                'content' => (string) $row->section_content,
            ])->values(),
        ])->values();

        $first = $rows->first();

        return PluginPage::template(self::TEMPLATE, 'edit-page', [
            'page'    => ['key' => $id, 'name' => __(self::PAGES[$id][0]), 'path' => self::PAGES[$id][1]],
            'groups'  => $groups,
            // The footer is shared by every page, so it has no search engine details
            'seo'     => $id === 'footer' ? null : [
                'title'       => (string) $first->title,
                'description' => (string) $first->description,
                'keywords'    => (string) $first->keywords,
            ],
            'siteUrl' => url('/'),
        ]);
    }

    /** "work_card_title_1" → "Work card title 1" */
    private static function label(string $key): string
    {
        return ucfirst(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $key))));
    }

    public function updatePage(Request $request, $id)
    {
        abort_unless(array_key_exists($id, self::PAGES), 404);

        $ids = DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', $id)->pluck('id')->map(fn ($v) => (string) $v)->all();
        $seo = $id !== 'footer';

        $request->validate([
            'sections'    => 'required|array',
            'sections.*'  => 'nullable|string|max:65000',
            'title'       => [$seo ? 'required' : 'nullable', 'string', 'max:1000'],
            'description' => [$seo ? 'required' : 'nullable', 'string', 'max:2000'],
            'keywords'    => [$seo ? 'required' : 'nullable', 'string', 'max:2000'],
        ], [], [
            'title'       => __('meta title'),
            'description' => __('meta description'),
            'keywords'    => __('meta keywords'),
        ]);

        // Only rows of this page can be changed
        $sections = collect($request->input('sections'))->only($ids);

        DB::transaction(function () use ($sections, $id, $seo, $request) {
            foreach ($sections as $rowId => $content) {
                DB::table('pages')->where('template_id', self::TEMPLATE)->where('id', $rowId)->update([
                    'section_content' => $content === '' ? null : $content,
                    'updated_at'      => now(),
                ]);
            }

            if ($seo) {
                DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', $id)->update([
                    'title'       => $request->title,
                    'description' => $request->description,
                    'keywords'    => $request->keywords,
                ]);
            }
        });

        return back()->with('success', __('Page saved.'));
    }

    // Custom pages (shown at /p/<slug>)
    public function addCustomPage()
    {
        return PluginPage::template(self::TEMPLATE, 'custom-page', ['page' => null, 'siteUrl' => url('/')]);
    }

    public function editCustomPage($id)
    {
        $page = $this->customPages()->where('id', $id)->first();
        abort_unless($page, 404);

        return PluginPage::template(self::TEMPLATE, 'custom-page', [
            'page'    => [
                'id'          => $page->id,
                'page_name'   => (string) $page->section_name,
                'slug'        => (string) $page->section_title,
                'body'        => (string) $page->section_content,
                'title'       => (string) $page->title,
                'description' => (string) $page->description,
                'keywords'    => (string) $page->keywords,
            ],
            'siteUrl' => url('/'),
        ]);
    }

    public function saveCustomPage(Request $request)
    {
        $data = $this->validateCustomPage($request);

        $page = new Page();
        $page->template_id = self::TEMPLATE;
        $page->page_name = 'Custom Page';
        $page->section_name = ucfirst($data['page_name']);
        $page->section_title = $data['slug'];
        $page->section_content = $this->cleanBody($data['body']);
        $page->title = ucfirst($data['title']);
        $page->description = ucfirst($data['description']);
        $page->keywords = $data['keywords'];
        $page->status = 'active';
        $page->save();

        return redirect()->route('admin.web-template.gobiz-original.custom-pages')->with('success', __('Page created.'));
    }

    public function updateCustomPage(Request $request)
    {
        $request->validate(['page_id' => 'required|integer']);
        abort_unless($this->customPages()->where('id', $request->page_id)->exists(), 404);

        $data = $this->validateCustomPage($request, (int) $request->page_id);

        $this->customPages()->where('id', $request->page_id)->update([
            'section_name'    => ucfirst($data['page_name']),
            'section_title'   => $data['slug'],
            'section_content' => $this->cleanBody($data['body']),
            'title'           => ucfirst($data['title']),
            'description'     => ucfirst($data['description']),
            'keywords'        => $data['keywords'],
            'updated_at'      => now(),
        ]);

        return back()->with('success', __('Page saved.'));
    }

    private function validateCustomPage(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'page_name'   => 'required|string|max:255',
            'slug'        => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'section_title')->where('template_id', self::TEMPLATE)->where('page_name', 'Custom Page')->ignore($ignoreId),
            ],
            'body'        => 'required|string',
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'keywords'    => 'required|string|max:2000',
        ], [
            'slug.regex'  => __('Use only lowercase letters, numbers and hyphens.'),
            'slug.unique' => __('Another page already uses this address.'),
        ], [
            'page_name'   => __('page name'),
            'slug'        => __('address'),
            'body'        => __('content'),
            'title'       => __('meta title'),
            'description' => __('meta description'),
            'keywords'    => __('meta keywords'),
        ]);
    }

    /** Store base64 images as files, open links in a new tab, then purify */
    private function cleanBody(string $body): string
    {
        $body = preg_replace_callback('/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i', function ($matches) {
            $decoded = base64_decode($matches[2]);

            if ($decoded === false) {
                return $matches[0];
            }

            return '<img src="' . asset(StorageHelper::uploadImage($decoded, 'uploads/pages', Str::random(10) . '.' . $matches[1])) . '" />';
        }, $body);

        $body = preg_replace_callback('/<a\b([^>]*)href=["\'](.*?)["\']([^>]*)>/i', function ($matches) {
            $tag = "<a{$matches[1]}href=\"{$matches[2]}\"{$matches[3]}>";

            if (! preg_match('/\btarget=["\']?[_a-zA-Z]+["\']?/i', $tag)) {
                $tag = rtrim($tag, '>') . ' target="_blank">';
            }
            if (! preg_match('/\brel=["\']?[^"\']*["\']?/i', $tag)) {
                $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
            }

            return $tag;
        }, $body);

        return Purifier::clean($body);
    }

    private function customPages()
    {
        return DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', 'Custom Page');
    }

    // Show or hide a website page (type static, id = page key) or a custom page (type custom, id = row id)
    public function updateStatus(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:static,custom', 'id' => 'required|string|max:191']);

        if ($data['type'] === 'static') {
            abort_unless(array_key_exists($data['id'], self::PAGES), 404);
            $query = DB::table('pages')->where('template_id', self::TEMPLATE)->where('page_name', $data['id']);
        } else {
            $query = $this->customPages()->where('id', $data['id']);
        }

        $page = (clone $query)->first();
        abort_unless($page, 404);

        if ($page->status === 'active' && $data['type'] === 'static' && in_array($data['id'], self::LOCKED, true)) {
            return back()->with('failed', __('Your site needs this page, so it stays on.'));
        }

        $status = $page->status === 'active' ? 'inactive' : 'active';
        $query->update(['status' => $status]);

        return back()->with('success', $status === 'active' ? __('Page shown on your site.') : __('Page hidden from your site.'));
    }

    // Delete a custom page
    public function deletePage(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        abort_unless($this->customPages()->where('id', $request->id)->delete(), 404);

        return back()->with('success', __('Page deleted.'));
    }

    // Template settings
    public function templateConfig()
    {
        $config = DB::table('gobiz_original_config')->first();
        $announcement = Announcement::where('template_id', self::TEMPLATE)->first()?->announcement_configuration ?? [];
        $popup = WebPopup::where('template_id', self::TEMPLATE)->first();

        return PluginPage::template(self::TEMPLATE, 'config', [
            'appearance'   => [
                'template_color' => (string) ($config->template_color ?? 'blue'),
                'theme_slider'   => (bool) ($config->theme_slider ?? false),
                'custom_css'     => (string) ($config->custom_css ?? ''),
                'custom_js'      => (string) ($config->custom_js ?? ''),
            ],
            'images'       => [
                'banner' => ! empty($config->banner_image) ? asset($config->banner_image) : null,
                'auth'   => ! empty($config->auth_image) ? asset($config->auth_image) : null,
                'popup'  => ! empty($popup?->image) ? asset($popup->image) : null,
            ],
            'colors'       => collect(self::COLORS)->map(fn ($swatch, $value) => ['value' => $value, 'label' => __(ucfirst($value)), 'swatch' => $swatch])->values(),
            'appBanner'    => [
                'app_action'             => (bool) ($config->app_action ?? false),
                'app_heading'            => (string) ($config->app_heading ?? ''),
                'app_description'        => (string) ($config->app_description ?? ''),
                'google_play_store_link' => (string) ($config->google_play_store_link ?? ''),
                'apple_app_store_link'   => (string) ($config->apple_app_store_link ?? ''),
            ],
            'announcement' => [
                'announcement_bg'            => (string) ($announcement['bg_color'] ?? '#000000'),
                'announcement_text_color'    => (string) ($announcement['text_color'] ?? '#ffffff'),
                'announcement_marquee'       => (bool) ($announcement['marquee'] ?? false),
                'announcement_marquee_speed' => (string) ($announcement['marquee_speed'] ?? 90),
                'announcements'              => collect($announcement['items'] ?? [])->map(fn ($item) => [
                    'text'   => (string) ($item['text'] ?? ''),
                    'active' => (bool) ($item['active'] ?? false),
                ])->values(),
            ],
            'popup'        => [
                'popup_status' => (bool) ($popup->status ?? false),
                'popup_link'   => (string) ($popup->link ?? ''),
            ],
            'maxUploadKb'  => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    public function updateConfig(Request $request)
    {
        $max = 'max:' . env('SIZE_LIMIT', 2048);
        $data = $request->validate([
            'template_color' => ['required', Rule::in(array_keys(self::COLORS))],
            'theme_slider'   => 'required|boolean',
            'custom_css'     => 'nullable|string|max:100000',
            'custom_js'      => 'nullable|string|max:100000',
            'banner_image'   => ['nullable', 'file', self::IMAGE_RULE, $max],
            'auth_image'     => ['nullable', 'file', self::IMAGE_RULE, $max],
        ], [], [
            'template_color' => __('colour'),
            'banner_image'   => __('home banner image'),
            'auth_image'     => __('sign-in and sign-up image'),
        ]);

        $update = [
            'template_color' => $data['template_color'],
            'theme_slider'   => $request->boolean('theme_slider'),
            'custom_css'     => $data['custom_css'] ?? null,
            'custom_js'      => $data['custom_js'] ?? null,
        ];

        foreach (['banner_image', 'auth_image'] as $key) {
            if ($request->hasFile($key)) {
                $update[$key] = $this->uploadImage($request->file($key));
            }
        }

        DB::table('gobiz_original_config')->where('id', 1)->update($update);

        return back()->with('success', __('Template settings saved.'));
    }

    // Mobile app banner
    public function appActionBanner(Request $request)
    {
        $data = $request->validate([
            'app_action'             => 'required|boolean',
            'app_heading'            => 'required_if_accepted:app_action|nullable|string|max:255',
            'app_description'        => 'required_if_accepted:app_action|nullable|string|max:1000',
            'google_play_store_link' => 'nullable|url|max:2000',
            'apple_app_store_link'   => 'nullable|url|max:2000',
        ], [
            'app_heading.required_if_accepted'     => __('Add a heading to show the app banner.'),
            'app_description.required_if_accepted' => __('Add a description to show the app banner.'),
        ], [
            'app_heading'            => __('heading'),
            'app_description'        => __('description'),
            'google_play_store_link' => __('Google Play link'),
            'apple_app_store_link'   => __('App Store link'),
        ]);

        DB::table('gobiz_original_config')->where('id', 1)->update([
            'app_action'             => $request->boolean('app_action'),
            'app_heading'            => $data['app_heading'] ?? '',
            'app_description'        => $data['app_description'] ?? '',
            'google_play_store_link' => $data['google_play_store_link'] ?? null,
            'apple_app_store_link'   => $data['apple_app_store_link'] ?? null,
        ]);

        return back()->with('success', __('App banner saved.'));
    }

    // Announcement bar
    public function updateAnnouncement(Request $request)
    {
        $request->validate([
            'announcement_bg'            => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'announcement_text_color'    => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'announcement_marquee'       => 'required|boolean',
            'announcement_marquee_speed' => 'required_if_accepted:announcement_marquee|nullable|integer|min:10|max:500',
            'announcements'              => 'nullable|array|max:20',
            'announcements.*.text'       => 'nullable|string|max:5000',
            'announcements.*.active'     => 'nullable|boolean',
        ], [
            'announcement_bg.regex'         => __('Use a colour like #1a2b3c.'),
            'announcement_text_color.regex' => __('Use a colour like #1a2b3c.'),
        ], [
            'announcement_bg'            => __('background colour'),
            'announcement_text_color'    => __('text colour'),
            'announcement_marquee_speed' => __('scroll speed'),
        ]);

        $config = [
            'bg_color'      => $request->announcement_bg,
            'text_color'    => $request->announcement_text_color,
            'marquee'       => $request->boolean('announcement_marquee'),
            'marquee_speed' => $request->announcement_marquee_speed ?: 90,
            // Announcements without text are dropped; HTML is kept as written
            'items'         => collect($request->input('announcements', []))
                ->filter(fn ($item) => trim(strip_tags($item['text'] ?? '')) !== '')
                ->map(fn ($item) => ['text' => $item['text'], 'active' => filter_var($item['active'] ?? false, FILTER_VALIDATE_BOOLEAN)])
                ->values()->all(),
        ];

        Announcement::updateOrCreate(['template_id' => self::TEMPLATE], [
            'announcement_enabled'       => 1,
            'announcement_configuration' => $config,
            'status'                     => 1,
        ]);

        return back()->with('success', __('Announcement bar saved.'));
    }

    // Popup
    public function updatePopup(Request $request)
    {
        $request->validate([
            'popup_status' => 'required|boolean',
            'popup_link'   => 'nullable|url|max:191',
            'popup_image'  => ['nullable', 'file', self::IMAGE_RULE, 'max:' . env('SIZE_LIMIT', 2048)],
        ], [], [
            'popup_link'  => __('link'),
            'popup_image' => __('image'),
        ]);

        $popup = WebPopup::firstOrNew(['template_id' => self::TEMPLATE]);
        $popup->enabled = 1;
        $popup->link = $request->popup_link ?: null;

        if ($request->hasFile('popup_image')) {
            $popup->image = $this->uploadImage($request->file('popup_image'), 'web/popups');
        }

        $popup->status = $request->boolean('popup_status') ? 1 : 0;
        $popup->save();

        return back()->with('success', __('Popup saved.'));
    }

    private function uploadImage($file, $folder = 'images/web/elements')
    {
        return StorageHelper::uploadImage($file, $folder);
    }
}
