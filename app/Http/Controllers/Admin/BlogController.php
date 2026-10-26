<?php

// --------------------------------------------------------------------------
// GoBiz vCard SaaS
// --------------------------------------------------------------------------
// Developed by NativeCode © 2021 - https://nativecode.in
// All rights reserved
// Unauthorized distribution is prohibited
// --------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Blog;
use App\BlogCategory;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

// Blog posts. blogs.status: 1 published, 0 draft, 2 deleted.
// blogs.category holds blog_categories.blog_category_id; tags are comma separated.
// Images pasted into the body arrive as base64 and are saved as files.
class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        // Filter query to only retrieve published blogs (status = 1)
        $base = Blog::where('status', 1);

        $posts = (clone $base)
            ->when($search !== '', fn($q) => $q->where(fn($q) => $q->where('heading', 'like', "%{$search}%")->orWhere('tags', 'like', "%{$search}%")))
            ->with('blogCategory')
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn(Blog $blog) => [
                'id'        => $blog->blog_id,
                'title'     => $blog->heading,
                'summary'   => Str::limit((string) $blog->short_description, 120),
                'cover'     => $blog->cover_image ? asset($blog->cover_image) : null,
                'category'  => $blog->blogCategory?->blog_category_title,
                'tags'      => $this->tags($blog->tags),
                'published' => (int) $blog->status === 1,
                'url'       => route('view.blog', $blog->slug),
                'createdAt' => $blog->created_at?->toIso8601String(),
            ]);

        return Inertia::render('website/blogs/index', [
            'posts'  => $posts,
            'search' => $search,
            'counts' => [
                'all'       => (clone $base)->count(),
                'published' => (clone $base)->count(),
                'drafts'    => 0,
            ],
        ]);
    }

    public function createBlog()
    {
        return $this->form(null);
    }

    public function editBlog($id)
    {
        // Find blog where status is 1
        $blog = Blog::where('blog_id', $id)->where('status', 1)->first();

        return $blog ? $this->form($blog) : redirect()->route('admin.blogs')->with('failed', __('Post not found.'));
    }

    public function publishBlog(Request $request)
    {
        $data = $this->validated($request, null);

        $blog               = new Blog();
        $blog->blog_id      = uniqid();
        $blog->published_by = Auth::id();
        $this->fill($blog, $data, $request)->save();

        return redirect()->route('admin.blogs')->with('success', $blog->status ? __('Post published.') : __('Draft saved.'));
    }

    public function updateBlog(Request $request, $id)
    {
        $blog = Blog::where('blog_id', $id)->where('status', '!=', 2)->firstOrFail();
        $data = $this->validated($request, $blog);

        $this->fill($blog, $data, $request)->save();

        return back()->with('success', __('Post saved.'));
    }

    public function actionBlog(Request $request)
    {
        $data = $request->validate(['id' => 'required|string', 'mode' => 'required|in:publish,unpublish,delete']);
        $blog = Blog::where('blog_id', $data['id'])->where('status', '!=', 2)->firstOrFail();

        $blog->forceFill(['status' => ['publish' => 1, 'unpublish' => 0, 'delete' => 2][$data['mode']]])->save();

        return back()->with('success', [
            'publish'   => __('Post published.'),
            'unpublish' => __('Post moved to drafts.'),
            'delete'    => __('Post deleted.'),
        ][$data['mode']]);
    }

    private function form(?Blog $blog)
    {
        return Inertia::render('website/blogs/form', [
            'post'        => $blog ? [
                'id'                => $blog->blog_id,
                'blog_name'         => $blog->heading,
                'blog_slug'         => urldecode($blog->slug),
                'category_id'       => $blog->category,
                'tags'              => $this->tags($blog->tags),
                'short_description' => (string) $blog->short_description,
                'long_description'  => (string) $blog->long_description,
                'seo_title'         => (string) $blog->title,
                'seo_description'   => (string) $blog->description,
                'seo_keywords'      => (string) $blog->keywords,
                'published'         => (int) $blog->status === 1,
                'cover'             => $blog->cover_image ? asset($blog->cover_image) : null,
                'url'               => route('view.blog', $blog->slug),
            ] : null,
            // Only fetch active categories where status is 1
            'categories'  => BlogCategory::where('status', 1)
                ->orderBy('blog_category_title')
                ->get(['blog_category_id', 'blog_category_title'])
                ->map(fn($c) => [
                    'value' => $c->blog_category_id,
                    'label' => __($c->blog_category_title),
                ]),
            'blogUrl'     => url('blog'),
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    private function validated(Request $request, ?Blog $blog): array
    {
        // Titles in non-Latin scripts can slug to empty string, fallback to random string
        $slug = Str::slug((string) ($request->input('blog_slug') ?: $request->input('blog_name')));
        $request->merge(['blog_slug' => $slug !== '' ? $slug : ($blog?->slug ?: 'post-' . Str::lower(Str::random(8)))]);

        return $request->validate([
            'blog_name'         => 'required|string|min:3|max:191',
            'blog_slug'         => ['required', 'string', 'max:191', Rule::unique('blogs', 'slug')->where(fn($q) => $q->where('status', '!=', 2))->ignore($blog?->id)],
            // Ensure selected category has status = 1
            'category_id'       => ['required', Rule::exists('blog_categories', 'blog_category_id')->where(fn($q) => $q->where('status', 1))],
            'tags'              => 'nullable|array|max:20',
            'tags.*'            => 'string|max:50',
            'short_description' => 'required|string|min:3|max:500',
            'long_description'  => 'required|string|min:3',
            'blog_cover'        => [$blog ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . env('SIZE_LIMIT', 2048)],
            'seo_title'         => 'nullable|string|max:191',
            'seo_description'   => 'nullable|string|max:500',
            'seo_keywords'      => 'nullable|string|max:500',
            'published'         => 'required|boolean',
        ], [
            'blog_slug.unique' => __('Another post already uses this URL.'),
        ], [
            'blog_name'         => __('title'),
            'blog_slug'         => __('URL'),
            'category_id'       => __('category'),
            'short_description' => __('summary'),
            'long_description'  => __('content'),
            'blog_cover'        => __('cover image'),
        ]);
    }

    private function fill(Blog $blog, array $data, Request $request): Blog
    {
        if ($request->hasFile('blog_cover')) {
            if ($blog->cover_image) {
                StorageHelper::deleteFile($blog->cover_image);
            }
            $blog->cover_image = StorageHelper::uploadImage($request->file('blog_cover'), 'images/blogs/cover-images');
        }

        $tags = collect($data['tags'] ?? [])->map(fn($tag) => trim($tag))->filter()->unique()->values();

        $blog->heading           = $data['blog_name'];
        $blog->slug              = $data['blog_slug'];
        $blog->category          = $data['category_id'];
        $blog->tags              = $tags->implode(',');
        $blog->short_description = $data['short_description'];
        $blog->long_description  = $this->prepareBody($data['long_description']);
        // Templates print these in the page head, so fall back to the post itself
        $blog->title             = ($data['seo_title'] ?? '') ?: $data['blog_name'];
        $blog->description       = ($data['seo_description'] ?? '') ?: Str::limit(strip_tags($data['short_description']), 160, '');
        $blog->keywords          = ($data['seo_keywords'] ?? '') ?: $tags->implode(', ');
        $blog->status            = $request->boolean('published') ? 1 : 0;

        return $blog;
    }

    // Save pasted base64 images as files and open body links in a new tab
    private function prepareBody(string $html): string
    {
        $html = preg_replace_callback(
            '/<img([^>]*?)src=["\']data:image\/([a-z0-9.+-]+);base64,([^"\']+)["\']([^>]*)>/i',
            function ($match) {
                $extension = strtolower($match[2]) === 'svg+xml' ? null : preg_replace('/[^a-z0-9]/', '', strtolower($match[2]));
                $binary    = base64_decode($match[3], true);
                if (! $extension || $binary === false) {
                    return '';
                }

                return '<img' . $match[1] . 'src="' . asset(StorageHelper::uploadImage($binary, 'uploads/blogs', Str::random(10) . '.' . $extension)) . '"' . $match[4] . '>';
            },
            $html
        );

        return preg_replace_callback('/<a\b([^>]*)>/i', function ($match) {
            $attributes = $match[1];
            if (! preg_match('/\btarget\s*=/i', $attributes)) {
                $attributes .= ' target="_blank"';
            }
            if (! preg_match('/\brel\s*=/i', $attributes)) {
                $attributes .= ' rel="noopener noreferrer"';
            }

            return '<a' . $attributes . '>';
        }, $html);
    }

    private function tags(?string $tags): array
    {
        return collect(explode(',', (string) $tags))->map(fn($tag) => trim($tag))->filter()->values()->all();
    }
}
