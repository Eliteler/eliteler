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

namespace App\Http\Controllers\Admin;

use App\Theme;
use App\Setting;
use App\BusinessCard;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // All Themes
    public function themes($status)
    {
        // Status
        if ($status == 'all') {
            $themes = Theme::withCount('businessCards')
                ->where('theme_id', '!=', "588969111147")
                ->orderByDesc('id')
                ->paginate(12);
        } else if ($status == 'active') {
            $themes = Theme::where('status', 1)
                ->withCount('businessCards')
                ->where('theme_id', '!=', "588969111147")
                ->orderByDesc('id')
                ->paginate(12);
        } else if ($status == 'disabled') {
            $themes = Theme::where('status', 0)
                ->withCount('businessCards')
                ->where('theme_id', '!=', "588969111147")
                ->orderByDesc('id')
                ->paginate(12);
        }

        $settings = Setting::active()->first();

        return view('admin.pages.themes.index', compact('themes', 'settings'));
    }

    // Active Themes
    public function activeThemes()
    {
        // Queries
        $themes = Theme::where('status', 1)
            ->withCount('businessCards')
            ->where('theme_id', '!=', "588969111147")
            ->orderByDesc('id')
            ->paginate(12);

        $settings = Setting::active()->first();

        return view('admin.pages.themes.active-themes', compact('themes', 'settings'));
    }

    // Disabled Themes
    public function disabledThemes()
    {
        // Queries
        $themes = Theme::where('status', 0)
            ->where('theme_id', '!=', "588969111147")
            ->withCount('businessCards')
            ->orderByDesc('id')
            ->paginate(12);

        $settings = Setting::active()->first();

        return view('admin.pages.themes.disabled-themes', compact('themes', 'settings'));
    }

    // Edit theme
    public function editTheme(Request $request, $id)
    {
        // Queries
        $theme_details = Theme::where('theme_id', $id)->first();
        $settings      = Setting::where('status', 1)->first();

        return view('admin.pages.themes.edit', compact('theme_details', 'settings'));
    }

    // Update theme
    public function updateTheme(Request $request)
    {
        // Validate theme name
        $validated = $request->validate([
            'theme_name' => 'required|min:3',
        ]);

        // If thumbnail is uploaded
        if ($request->hasFile('theme_thumbnail')) {
            $validator = Validator::make($request->all(), [
                'theme_thumbnail' => 'required|mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT'),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Handle upload
            $file = $request->file('theme_thumbnail');

            if ($file) {
                $fileName = 'theme-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $fileName;

                // Save to public storage
                Storage::disk('theme')->put($path, file_get_contents($file));

                // Update both name and thumbnail
                Theme::where('theme_id', $request->theme_id)->update([
                    'theme_name' => $request->theme_name,
                    'theme_thumbnail' => $fileName,
                ]);
            }
        } else {
            // Update only theme name
            Theme::where('theme_id', $request->theme_id)->update([
                'theme_name' => $request->theme_name,
            ]);
        }

        return redirect()->route('admin.edit.theme', $request->theme_id)->with('success', trans('Updated!'));
    }

    // Update status
    public function updateThemeStatus(Request $request)
    {
        // Parameters
        if ($request->query('status') == 'enable') {
            $status = '1';
        } else {
            $status = '0';
        }

        Theme::where('theme_id', $request->query('id'))->update(['status' => $status]);

        return redirect()->back()->with('success', trans('Updated!'));
    }

    // Search theme
    public function searchTheme(Request $request, $status)
    {
        // Parameters
        $search = $request->query('query');

        $settings = Setting::where('status', 1)->first();

        $themes = Theme::where('theme_id', '!=', '588969111147')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('theme_name', 'like', "%{$search}%")
                        ->orWhere('theme_description', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status === 'active' ? 1 : 0);
            })
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        foreach ($themes as $theme) {
            $theme->business_cards_count = BusinessCard::where('theme_id', $theme->theme_id)
                ->where('theme_id', '!=', '588969111147')
                ->count();
        }

        return view('admin.pages.themes.index', compact('themes', 'status', 'settings'));
    }


    // Add Theme CSS
    public function addThemeCss(Request $request, $id)
    {
        // Check if theme exists
        if (!Theme::where('theme_id', $id)->first()) {
            return redirect()->route('admin.themes')->with('failed', trans('Theme not found!'));
        }

        // Get theme
        $themeDetails = Theme::where('theme_id', $id)->first();
        $settings = Setting::active()->first();

        return view('admin.pages.themes.add-theme-css', compact('themeDetails', 'settings'));
    }

    // Update Theme CSS
    public function updateThemeCss(Request $request)
    {
        $theme_id = $request->theme_id;
        $css = $request->css;

        // Remove <style> ... </style> (with attributes) and <script> ... </script>
        $css = preg_replace('/<\/?style[^>]*>/i', '', $css);
        $css = preg_replace('/<\/?script[^>]*>/i', '', $css);

        // Trim spaces
        $css = trim($css);

        // Show error if CSS is empty
        if (empty($css)) {
            return redirect()->route('admin.edit.theme', $theme_id)
                ->with('failed', trans('Invalid CSS code.'));
        }

        // Save CSS
        $theme = Theme::where('theme_id', $theme_id)->first();
        if ($theme) {
            $theme->theme_css = $css;
            $theme->save();
        }

        return redirect()->route('admin.edit.theme', $theme_id)
            ->with('success', trans('Updated!'));
    }

    // Add Theme JS
    public function addThemeJs(Request $request, $id)
    {
        // Check if theme exists
        if (!Theme::where('theme_id', $id)->first()) {
            return redirect()->route('admin.themes')->with('failed', trans('Theme not found!'));
        }

        // Get theme
        $themeDetails = Theme::where('theme_id', $id)->first();
        $settings = Setting::active()->first();

        return view('admin.pages.themes.add-theme-js', compact('themeDetails', 'settings'));
    }

    // Update Theme JS
    public function updateThemeJs(Request $request)
    {
        $theme_id = $request->theme_id;
        $js = $request->js;

        // Show error if JS is empty
        if (empty($js)) {
            return redirect()->route('admin.edit.theme', $theme_id)
                ->with('failed', trans('Invalid JS code.'));
        }

        // Save JS
        $theme = Theme::where('theme_id', $theme_id)->first();
        if ($theme) {
            $theme->theme_js = $js;
            $theme->save();
        }

        return redirect()->route('admin.edit.theme', $theme_id)->with('success', trans("Updated!"));
    }
}
