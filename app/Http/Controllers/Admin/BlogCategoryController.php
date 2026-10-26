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

use App\Blog;
use App\BlogCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Blog categories. blog_categories.status: 1 visible, 0 hidden, 2 deleted.
 * "Uncategorized" is the fallback: posts of a deleted category move there.
 */
class BlogCategoryController extends Controller
{
    private const FALLBACK = 'uncategorized';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $counts = Blog::where('status', '!=', 2)->groupBy('category')->selectRaw('category, COUNT(*) as total')->pluck('total', 'category');

        $categories = BlogCategory::where('status', '!=', 2)->orderBy('blog_category_title')->get()->map(fn (BlogCategory $category) => [
            'id'         => $category->blog_category_id,
            'name'       => $category->blog_category_title,
            'slug'       => $category->blog_category_slug,
            'visible'    => (int) $category->status === 1,
            'posts'      => (int) ($counts[$category->blog_category_id] ?? 0),
            'isFallback' => $category->blog_category_slug === self::FALLBACK,
        ]);

        return Inertia::render('website/blogs/categories', [
            'categories' => $categories,
            'open'       => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
        ]);
                    }

    // Old sub-pages now open the dialog on the list page
    public function createBlogCategory()
    {
        return redirect()->route('admin.blog.categories', ['new' => 1]);
    }

    public function editBlogCategory($id)
    {
        return redirect()->route('admin.blog.categories', ['edit' => $id]);
    }

    public function publishBlogCategory(Request $request)
    {
        $data = $this->validated($request, null);

        $category                      = new BlogCategory();
        $category->published_by        = Auth::id();
        $category->blog_category_id    = uniqid();
        $category->blog_category_title = $data['category_name'];
        $category->blog_category_slug  = $data['category_slug'];
        $category->save();

        return back()->with('success', __(':name added.', ['name' => $category->blog_category_title]));
        }

    public function updateBlogCategory(Request $request, $id)
    {
        $category = BlogCategory::where('blog_category_id', $id)->where('status', '!=', 2)->firstOrFail();
        $data     = $this->validated($request, $category);

        $category->blog_category_title = $data['category_name'];
        // The fallback keeps its slug so it can always be found
        if ($category->blog_category_slug !== self::FALLBACK) {
            $category->blog_category_slug = $data['category_slug'];
        }
        $category->save();

        return back()->with('success', __(':name saved.', ['name' => $category->blog_category_title]));
    }

    public function actionBlogCategory(Request $request)
    {
        $data     = $request->validate(['id' => 'required|string', 'mode' => 'required|in:publish,unpublish,delete']);
        $category = BlogCategory::where('blog_category_id', $data['id'])->where('status', '!=', 2)->firstOrFail();

        if ($data['mode'] === 'delete') {
            if ($category->blog_category_slug === self::FALLBACK) {
                return back()->with('failed', __('Uncategorized can’t be deleted. Posts without a category go there.'));
        }

            $fallback = $this->fallback();
            $moved    = Blog::where('category', $category->blog_category_id)->update(['category' => $fallback->blog_category_id]);
            $category->forceFill(['status' => 2])->save();

            return back()->with('success', $moved
                ? __(':name deleted. Its :count posts moved to Uncategorized.', ['name' => $category->blog_category_title, 'count' => $moved])
                : __(':name deleted.', ['name' => $category->blog_category_title]));
        }

        $category->forceFill(['status' => $data['mode'] === 'publish' ? 1 : 0])->save();

        return back()->with('success', $data['mode'] === 'publish'
            ? __(':name is visible on the blog.', ['name' => $category->blog_category_title])
            : __(':name is hidden from the blog.', ['name' => $category->blog_category_title]));
    }

    private function validated(Request $request, ?BlogCategory $category): array
    {
        $request->merge(['category_slug' => Str::slug((string) ($request->input('category_slug') ?: $request->input('category_name')))]);

        return $request->validate([
            'category_name' => 'required|string|min:2|max:191',
            'category_slug' => ['required', 'string', 'max:191', Rule::unique('blog_categories', 'blog_category_slug')->where(fn ($q) => $q->where('status', '!=', 2))->ignore($category?->id)],
        ], [
            'category_slug.unique' => __('Another category already uses this URL.'),
        ], [
            'category_name' => __('name'),
            'category_slug' => __('URL'),
        ]);
    }

    /** The Uncategorized category, created again if it was removed. */
    private function fallback(): BlogCategory
    {
        $fallback = BlogCategory::where('blog_category_slug', self::FALLBACK)->orderBy('id')->first();

        if (! $fallback) {
            $fallback = new BlogCategory();
            $fallback->published_by        = Auth::id();
            $fallback->blog_category_id    = uniqid();
            $fallback->blog_category_title = 'Uncategorized';
            $fallback->blog_category_slug  = self::FALLBACK;
        }
        $fallback->status = 1;
        $fallback->save();

        return $fallback;
    }
}
