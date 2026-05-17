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

use App\Page;
use App\Setting;
use App\WebPopup;
use App\Announcement;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GoBizOriginalController extends Controller
{
    // Pages
    public function index(Request $request)
    {
        // app settings
        $app_settings = $this->getAppSettings();
        // Queries
        $settings = $app_settings['settings'];
        $config = $app_settings['config'];

        // Static pages
        if ($request->ajax()) {
            $pages = DB::table('pages')->whereIn('page_name', ['home', 'about', 'contact', 'faq', 'pricing', 'privacy', 'footer', 'refund', 'terms'])
                ->where('template_id', 'GoBizOriginal')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('page_name');

            return DataTables::of($pages)
                ->addIndexColumn()
                ->editColumn('page_name', function ($page) {
                    return match ($page->first()->page_name) {
                        'home' => __('Home'),
                        'about' => __('About Us'),
                        'contact' => __('Contact Us'),
                        'privacy' => __('Privacy Policy'),
                        'refund' => __('Refund Policy'),
                        'terms' => __('Terms & Conditions'),
                        default => trans(ucfirst($page->first()->page_name)),
                    };
                })
                ->editColumn('url', function ($page) {
                    $baseUrl = env('APP_URL');
                    return match ($page->first()->page_name) {
                        'home' => '<a href="' . $baseUrl . '" target="_blank">/</a>',
                        'about' => '<a href="' . $baseUrl . '/about-us" target="_blank">' . trans('/about-us') . '</a>',
                        'contact' => '<a href="' . $baseUrl . '/contact-us" target="_blank">' . trans('/contact-us') . '</a>',
                        'privacy' => '<a href="' . $baseUrl . '/privacy-policy" target="_blank">' . trans('/privacy-policy') . '</a>',
                        'refund' => '<a href="' . $baseUrl . '/refund-policy" target="_blank">' . trans('/refund-policy') . '</a>',
                        'terms' => '<a href="' . $baseUrl . '/terms-and-conditions" target="_blank">' . trans('/terms-and-conditions') . '</a>',
                        default => '<a href="' . $baseUrl . '/' . $page->first()->page_name . '" target="_blank">/' . trans($page->first()->page_name) . '</a>',
                    };
                })
                ->editColumn('status', function ($page) {
                    return $page->first()->status == 'active'
                        ? '<span class="badge bg-green text-white">' . __('Active') . '</span>'
                        : '<span class="badge bg-red text-white">' . __('Deactive') . '</span>';
                })
                ->addColumn('action', function ($page) {
                    $editUrl = route('admin.web-template.gobiz-original.edit-page', $page->first()->page_name);
                    $actionBtn = '<a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>';

                    if ($page->first()->status == 'inactive') {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="updatePageStatus(\'' . $page->first()->page_name . '\', `activate`, `static`); return false;">' . __('Activate') . '</a>';
                    } else if (!in_array($page->first()->page_name, ['home', 'footer'])) {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="updatePageStatus(\'' . $page->first()->page_name . '\', `deactivate`, `static`); return false;">' . __('Deactivate') . '</a>';
                    }

                    return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="nav-item dropdown">
                                    ' . $actionBtn . '
                                </div>
                            </div>';
                })
                ->rawColumns(['url', 'status', 'action'])
                ->make(true);
        }

        // return view
        return view('GoBizOriginal::Admin.index', compact('settings', 'config'));
    }

    // Custom pages
    public function customPagesIndex(Request $request)
    {
        if ($request->ajax()) {
            $custom_pages = DB::table('pages')->where('page_name', 'Custom Page')
                ->where('template_id', 'GoBizOriginal')
                ->orderBy('created_at', 'desc')
                ->get();

            return DataTables::of($custom_pages)
                ->addIndexColumn()
                ->editColumn('section_name', function ($page) {
                    return ucwords($page->section_name);
                })
                ->editColumn('url', function ($page) {
                    return '<a href="' . env('APP_URL') . '/p/' . $page->section_title . '" target="_blank">/' . $page->section_title . '</a>';
                })
                ->editColumn('status', function ($page) {
                    return $page->status == 'active'
                        ? '<span class="badge bg-green text-white">' . __('Active') . '</span>'
                        : '<span class="badge bg-red text-white">' . __('Deactive') . '</span>';
                })
                ->addColumn('action', function ($page) {
                    $editUrl = route('admin.web-template.gobiz-original.edit-custom-page', $page->id);
                    $actionBtn = '<a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>';

                    if ($page->status == 'inactive') {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="updatePageStatus(\'' . $page->id . '\', `activate`, `custom`); return false;">' . __('Activate') . '</a>';
                    } else {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="updatePageStatus(\'' . $page->id . '\', `deactivate`, `custom`); return false;">' . __('Deactivate') . '</a>';
                    }

                    $actionBtn .= '<a class="dropdown-item" href="#" onclick="deletePage(\'' . $page->id . '\', `delete`); return false;">' . __('Delete') . '</a>';

                    return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="nav-item dropdown">
                                    ' . $actionBtn . '
                                </div>
                            </div>';
                })
                ->rawColumns(['url', 'status', 'action'])
                ->make(true);
        }
    }

    // Edit page
    public function editPage($id)
    {
        // Get page details
        $sections = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->get();

        if (count($sections) > 0) {
            // app settings
            $app_settings = $this->getAppSettings();
            // Queries
            $settings = $app_settings['settings'];
            $config = $app_settings['config'];

            // return view
            return view('GoBizOriginal::Admin.edit-page', compact('sections', 'settings', 'config'));
        } else {
            return redirect()->back()->with('failed', trans('Not Found!'));
        }
    }

    // Update page
    public function updatePage(Request $request, $id)
    {
        // Update page
        $sections = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->get();
        for ($i = 0; $i < count($sections); $i++) {
            $safe_section_content = $request->input('section' . $i);
            DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->where('id', $sections[$i]->id)->update(['section_content' => $safe_section_content]);
            DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->where('id', $sections[$i]->id)->update(['description' => $request->description, 'keywords' => $request->keywords]);
        }

        // SEO
        DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->update(['title' => $request->title]);
        DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->update(['keywords' => $request->keywords]);
        DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $id)->update(['description' => $request->description]);

        // Page redirect
        return redirect()->route('admin.web-template.gobiz-original')->with('success', trans('Updated!'));
    }

    // Add page
    public function addCustomPage()
    {
        // app settings
        $app_settings = $this->getAppSettings();
        // Queries
        $settings = $app_settings['settings'];
        $config = $app_settings['config'];

        // View
        return view('GoBizOriginal::Admin.add-custom-page', compact('settings', 'config'));
    }

    // Save page
    public function saveCustomPage(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'page_name' => 'required',
            'slug' => 'required',
            'body' => 'required',
            'title' => 'required',
            'description' => 'required',
            'keywords' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Step 1: Process base64 <img> tags
        $updatedBody = preg_replace_callback(
            '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $extension = $matches[1]; // e.g., png, jpeg
                $decodedData = base64_decode($matches[2]);

                if ($decodedData !== false) {
                    $filename = Str::random(10) . '.' . $extension;
                    $relativePath = 'uploads/pages/' . $filename;

                    Storage::disk('public')->put($relativePath, $decodedData);

                    return '<img src="' . asset('storage/' . $relativePath) . '" />';
                }

                return $matches[0]; // Return original tag if decoding fails
            },
            $request->body
        );

        // Step 2: Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
        $updatedBody = preg_replace_callback(
            '/<a\b([^>]*)href=["\'](.*?)["\']([^>]*)>/i',
            function ($matches) {
                $beforeHref = $matches[1];
                $href = $matches[2];
                $afterHref = $matches[3];

                $tag = "<a{$beforeHref}href=\"{$href}\"{$afterHref}>";

                // If target already exists, don't add again
                if (!preg_match('/\btarget=["\']?[_a-zA-Z]+["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' target="_blank">';
                }

                // If rel already exists, don't add again
                if (!preg_match('/\brel=["\']?[^"\']*["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
                }

                return $tag;
            },
            $updatedBody
        );

        // Save the page
        $page = new Page();
        $page->template_id = 'GoBizOriginal';
        $page->page_name = "Custom Page";
        $page->section_name = ucfirst($request->page_name);
        $page->section_title = $request->slug;
        $page->section_content = Purifier::clean($updatedBody);
        $page->title = ucfirst($request->title);
        $page->description = ucfirst($request->description);
        $page->keywords = $request->keywords;
        $page->save();

        return redirect()->route('admin.web-template.gobiz-original')->with('success', trans('Created!'));
    }

    // Edit custom page
    public function editCustomPage($id)
    {
        // Get page details
        $page = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('id', $id)->where('page_name', 'Custom Page')->first();

        if ($page) {
            // app settings
            $app_settings = $this->getAppSettings();
            // Queries
            $settings = $app_settings['settings'];
            $config = $app_settings['config'];

            // View
            return view('GoBizOriginal::Admin.edit-custom-page', compact('page', 'settings', 'config'));
        } else {
            return redirect()->back()->with('failed', trans('Not Found!'));
        }
    }

    // Update custom page
    public function updateCustomPage(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'page_name'    => 'required|string|max:255',
            'slug'         => 'required|string|max:255',
            'body'         => 'required',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'keywords'     => 'required|string',
            'page_id'      => 'required|exists:pages,id'
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Step 2: Extract base64 images and replace with stored URLs
        $bodyContent = $request->body;

        // Step 1: Process base64 <img> tags
        $updatedBody = preg_replace_callback(
            '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $extension = $matches[1]; // e.g., png, jpeg
                $decodedData = base64_decode($matches[2]);

                if ($decodedData !== false) {
                    $filename = Str::random(10) . '.' . $extension;
                    $relativePath = 'uploads/pages/' . $filename;

                    Storage::disk('public')->put($relativePath, $decodedData);

                    return '<img src="' . asset('storage/' . $relativePath) . '" />';
                }

                return $matches[0]; // Return original tag if decoding fails
            },
            $bodyContent
        );

        // Step 2: Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
        $updatedBody = preg_replace_callback(
            '/<a\b([^>]*)href=["\'](.*?)["\']([^>]*)>/i',
            function ($matches) {
                $beforeHref = $matches[1];
                $href = $matches[2];
                $afterHref = $matches[3];

                $tag = "<a{$beforeHref}href=\"{$href}\"{$afterHref}>";

                // If target already exists, don't add again
                if (!preg_match('/\btarget=["\']?[_a-zA-Z]+["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' target="_blank">';
                }

                // If rel already exists, don't add again
                if (!preg_match('/\brel=["\']?[^"\']*["\']?/i', $tag)) {
                    $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
                }

                return $tag;
            },
            $updatedBody
        );

        // Step 3: Clean HTML before saving
        $cleanedBody = Purifier::clean($updatedBody);

        // Step 4: Update the page
        DB::table('pages')->where('template_id', 'GoBizOriginal')->where('id', $request->page_id)->update([
            'section_name'    => ucfirst($request->page_name),
            'section_title'   => $request->slug,
            'section_content' => $cleanedBody,
            'title'           => ucfirst($request->title),
            'description'     => ucfirst($request->description),
            'keywords'        => $request->keywords,
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.web-template.gobiz-original')->with('success', trans('Updated!'));
    }

    // Status Page
    public function updateStatus(Request $request)
    {
        // check type
        if ($request->query('type') == 'static') {
            $page_details = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $request->query('id'))->first();

            // Check status
            if ($page_details->status == 'inactive') {
                $status = 'active';
            } else {
                $status = 'inactive';
            }

            // Update status
            DB::table('pages')->where('template_id', 'GoBizOriginal')->where('page_name', $request->query('id'))->update(['status' => $status]);
        } else {
            $page_details = DB::table('pages')->where('template_id', 'GoBizOriginal')->where('id', $request->query('id'))->first();

            // Check status
            if ($page_details->status == 'inactive') {
                $status = 'active';
            } else {
                $status = 'inactive';
            }

            // Update status
            DB::table('pages')->where('template_id', 'GoBizOriginal')->where('id', $request->query('id'))->update(['status' => $status]);
        }

        return redirect()->route('admin.web-template.gobiz-original')->with('success', trans('Updated!'));
    }

    // Delete Page
    public function deletePage(Request $request)
    {
        // Update status
        DB::table('pages')->where('template_id', 'GoBizOriginal')->where('id', $request->query('id'))->delete();

        return redirect()->route('admin.web-template.gobiz-original')->with('success', trans('Deleted!'));
    }

    // template config
    public function templateConfig(Request $request)
    {
        // app settings
        $app_settings = $this->getAppSettings();
        // Queries
        $settings = $app_settings['settings'];
        $config = $app_settings['config'];

        $template_config = DB::table('gobiz_original_config')->first();

        // Announcements
        $announcements = Announcement::where('template_id', 'GoBizOriginal')->first();

        // Popup
        $popups = WebPopup::where('template_id', 'GoBizOriginal')->first();

        // return view
        return view('GoBizOriginal::Admin.template-config', compact('settings', 'config', 'template_config', 'announcements', 'popups'));
    }

    // update template config
    public function updateConfig(Request $request)
    {
        // Validate form data
        $rules = [
            'template_color' => 'required|in:blue,indigo,green,yellow,red,purple,pink,gray,slate,zinc,neutral,stone,orange,amber,lime,emerald,teal,cyan,sky,violet,fuchsia,rose',
            'theme_slider' => 'required|in:1,0',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Check validation
        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        $updateData = [
            'template_color' => $request->template_color,
            'theme_slider'   => $request->theme_slider,
            'custom_css'     => $request->custom_css,
            'custom_js'      => $request->custom_js,
        ];

        // === Primary Image ===
        if ($request->hasFile('banner_image')) {
            $validator = Validator::make($request->all(), [
                'banner_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $updateData['banner_image'] = $this->uploadImage($request->file('banner_image'));
        }

        // === Secondary Image ===
        if ($request->hasFile('auth_image')) {
            $validator = Validator::make($request->all(), [
                'auth_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $updateData['auth_image'] = $this->uploadImage($request->file('auth_image'));
        }

        // Update template config only with available fields
        DB::table('gobiz_original_config')
            ->where('id', 1)
            ->update($updateData);

        // Page redirect
        return redirect()->route('admin.web-template.gobiz-original.config')->with('success', trans('Updated!'));
    }

    // Update mobile application action banner
    public function appActionBanner(Request $request)
    {
        // Validate form data
        $rules = [
            'app_action' => 'required|in:1,0',
            'app_heading' => 'required',
            'app_description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Check validation
        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Update template config only with available fields
        DB::table('gobiz_original_config')
            ->where('id', 1)
            ->update([
                'app_action' => $request->app_action,
                'app_heading' => $request->app_heading,
                'app_description' => $request->app_description,
                'google_play_store_link' => $request->google_play_store_link,
                'apple_app_store_link'   => $request->apple_app_store_link,
            ]);

        // Page redirect
        return redirect()->route('admin.web-template.gobiz-original.config')->with('success', trans('Updated!'));
    }

    // Update announcements
    public function updateAnnouncement(Request $request)
    {
        // Build JSON configuration
        $config = [
            'bg_color'        => $request->announcement_bg ?? '#000000',
            'text_color'      => $request->announcement_text_color ?? '#ffffff',
            'marquee'         => $request->boolean('announcement_marquee'),
            'marquee_speed'   => $request->announcement_marquee_speed ?? 90,
            'items'           => [],
        ];

        // Collect valid announcements only
        if ($request->filled('announcements')) {
            foreach ($request->announcements as $item) {
                $text = trim(strip_tags($item['text'] ?? ''));

                if ($text !== '') {
                    $config['items'][] = [
                        'text'   => $item['text'], // keep original HTML
                        'active' => isset($item['active']),
                    ];
                }
            }
        }

        // Check if the announcements exist
        if (Announcement::where('template_id', 'GoBizOriginal')->count() === 0) {
            // Create announcements
            Announcement::create([
                'announcement_enabled'       => 1,
                'template_id'                => 'GoBizOriginal',
                'announcement_configuration' => $config,
                'status'                     => 1,
            ]);

            return redirect()->route('admin.web-template.gobiz-original.config')->with('success', trans('Created!'));
        } else {
            // Update announcements
            Announcement::where('template_id', 'GoBizOriginal')->update([
                'announcement_enabled'       => 1,
                'template_id'                => 'GoBizOriginal',
                'announcement_configuration' => $config,
                'status'                     => 1,
            ]);
        }

        return redirect()->route('admin.web-template.gobiz-original.config')->with('success', trans('Updated!'));
    }

    // Update popup
    public function updatePopup(Request $request)
    {
        $popup = WebPopup::firstOrNew([
            'template_id' => 'GoBizOriginal',
        ]);

        $popup->enabled = 1;
        $popup->link = $request->popup_link ?: null;

        if ($request->hasFile('popup_image') && $request->file('popup_image')->isValid()) {
            $validator = Validator::make($request->all(), [
                'popup_image' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            $popup->image = $this->uploadImage($request->file('popup_image'), 'web/popups');
        }

        $popup->status = $request->popup_status == '1' ? 1 : 0;
        $popup->save();

        return redirect()->route('admin.web-template.gobiz-original.config')->with('success', trans('Updated!'));
    }

    // get app settings
    private function getAppSettings()
    {
        // Queries
        $settings = GoBizCommonService::settings();
        $config = GoBizCommonService::config();

        // View
        return ['settings' => $settings, 'config' => $config];
    }

    // Helper function to handle file uploads
    function uploadImage($file, $folder = 'images/web/elements')
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, file_get_contents($file));

        return 'storage/' . $path; // return public URL path
    }
}
