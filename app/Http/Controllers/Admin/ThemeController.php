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

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Theme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ThemeController extends Controller
{
    // Placeholder theme used for customers' custom designs, never listed.
    private const CUSTOM_THEME_ID = '588969111147';

    // themes.theme_description value that marks store themes.
    private const STORE = 'WhatsApp Store';

    public function __construct()
    {
        $this->middleware('auth');
    }

    // All themes, filtered by status (route), type and search (query)
    public function themes(Request $request, $status)
    {
        $status = in_array($status, ['all', 'active', 'disabled'], true) ? $status : 'all';
        $type   = in_array($request->query('type'), ['vcard', 'store'], true) ? $request->query('type') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = Theme::where('theme_id', '!=', self::CUSTOM_THEME_ID)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status === 'active' ? 1 : 0))
            ->when($search !== '', fn ($q) => $q->where('theme_name', 'like', "%{$search}%"));

        // Counts per type tab (within the current status and search)
        $counts = (clone $base)
            ->selectRaw('COUNT(*) as all_count, SUM(theme_description = ?) as store_count', [self::STORE])
            ->first();

        $themes = (clone $base)
            ->when($type === 'vcard', fn ($q) => $q->where('theme_description', '!=', self::STORE))
            ->when($type === 'store', fn ($q) => $q->where('theme_description', self::STORE))
            ->withCount('businessCards')
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Theme $theme) => $this->present($theme));

        return Inertia::render('themes/index', [
            'themes'  => $themes,
            'filters' => compact('status', 'type', 'search'),
            'counts'  => [
                'all'   => (int) $counts->all_count,
                'store' => (int) $counts->store_count,
                'vcard' => (int) $counts->all_count - (int) $counts->store_count,
            ],
        ]);
    }

    // Edit theme
    public function editTheme($id)
    {
        $theme = Theme::where('theme_id', $id)
            ->where('theme_id', '!=', self::CUSTOM_THEME_ID)
            ->withCount('businessCards')
            ->first();

        if (! $theme) {
            return redirect()->route('admin.themes', ['status' => 'all'])->with('failed', __('Theme not found!'));
        }

        return Inertia::render('themes/edit', [
            'theme' => [
                ...$this->present($theme),
                'css'       => $theme->theme_css ?? '',
                'js'        => $theme->theme_js ?? '',
                'updatedAt' => $theme->updated_at?->toIso8601String(),
            ],
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    // Update theme
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme_id'        => ['required', Rule::exists('themes', 'theme_id')],
            'theme_name'      => 'required|string|min:3|max:191',
            'theme_thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env('SIZE_LIMIT', 2048),
            'css'             => 'nullable|string|max:25000',
            'js'              => 'nullable|string|max:25000',
        ]);

        $theme = Theme::where('theme_id', $validated['theme_id'])->firstOrFail();

        if ($request->hasFile('theme_thumbnail')) {
            if (! empty($theme->theme_thumbnail)) {
                StorageHelper::deleteFile($theme->theme_thumbnail, 'theme');
            }

            $theme->theme_thumbnail = StorageHelper::uploadImage(
                $request->file('theme_thumbnail'),
                '',
                'theme-' . time(),
                'theme'
            );
        }

        // CSS is injected inside a <style> block: drop any style/script tags
        $css = preg_replace('/<\/?(style|script)[^>]*>/i', '', (string) ($validated['css'] ?? ''));

        $theme->theme_name = $validated['theme_name'];
        $theme->theme_css  = trim($css) !== '' ? trim($css) : null;
        $theme->theme_js   = trim((string) ($validated['js'] ?? '')) !== '' ? trim($validated['js']) : null;
        $theme->save();

        return redirect()->route('admin.edit.theme', $theme->theme_id)
            ->with('success', __('Theme updated successfully!'));
    }

    // Enable / disable theme
    public function updateThemeStatus(Request $request)
    {
        $validated = $request->validate([
            'id'     => ['required', Rule::exists('themes', 'theme_id')],
            'status' => 'required|in:enable,disable',
        ]);

        Theme::where('theme_id', $validated['id'])->update(['status' => $validated['status'] === 'enable' ? '1' : '0']);

        return back()->with('success', $validated['status'] === 'enable' ? __('Theme enabled.') : __('Theme disabled.'));
    }

    private function present(Theme $theme): array
    {
        return [
            'id'        => $theme->theme_id,
            'name'      => $theme->theme_name,
            'type'      => $theme->theme_description === self::STORE ? 'store' : 'vcard',
            'thumbnail' => $theme->theme_thumbnail ? asset('img/vCards/' . $theme->theme_thumbnail) : null,
            'active'    => (string) $theme->status === '1',
            'usage'     => (int) $theme->business_cards_count,
            'customCode' => ! empty($theme->theme_css) || ! empty($theme->theme_js),
        ];
    }
}
