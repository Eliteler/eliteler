<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SitemapService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Generates public/sitemap.xml from the chosen content types. */
class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return Inertia::render('settings/sitemap', [
            'categories' => $this->sitemap->categories(),
            'status'     => $this->sitemap->status(),
        ]);
    }

    public function generate(Request $request)
    {
        $ids  = collect($this->sitemap->categories())->pluck('id')->push('all')->all();
        $data = $request->validate([
            'categories'   => 'required|array|min:1',
            'categories.*' => 'in:' . implode(',', $ids),
        ], [
            'categories.required' => __('Choose at least one type of content.'),
        ], [
            'categories.*' => __('content type'),
        ]);

        $count = $this->sitemap->generate($data['categories']);

        return back()->with('success', __('Sitemap created with :count links.', ['count' => $count]));
    }
}
