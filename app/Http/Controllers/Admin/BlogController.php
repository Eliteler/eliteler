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

use App\Blog;
use App\Setting;
use App\BlogCategory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
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

    // Check slug exists
    public function createSlug($title, $count = 0)
    {
        // Generate the initial slug from the title
        $slug = Str::slug($title);

        // If a count is provided, append it to the slug
        if ($count > 0) {
            $slug .= '-' . $count;
        }

        // Check if the slug already exists in the database
        $existingSlug = Blog::where('slug', $slug)->first();

        // If the slug exists, recursively call this method with an incremented count
        if ($existingSlug) {
            return $this->createSlug($title, $count + 1);
        }

        // If the slug does not exist, return it
        return $slug;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Blogs
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $blogs = Blog::where('status', '!=', 2)->orderBy('created_at', 'desc')->get();

            return DataTables::of($blogs)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return formatDateForUser($row->created_at);
                })
                ->addColumn('blogCategory', function ($row) {
                    return __($row->blogCategory->blog_category_title ?? trans('No category'));
                })
                ->addColumn('tags', function ($row) {
                    $tags = explode(',', $row->tags);
                    $tags = collect($tags)->take(2)->map(function ($tag) {
                        return '<span class="badge bg-primary text-capitalize text-white mb-1">' . __($tag) . '</span><br>';
                    })->implode('');
                    return $tags;
                })
                ->addColumn('heading', function ($row) {
                    return '<a href="' . route('view.blog', $row->slug) . '" target="_blank">' . __($row->heading) . '</a>';
                })
                ->addColumn('short_description', function ($row) {
                    return __(mb_strimwidth($row->short_description, 0, 99, '...'));
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 0
                        ? '<span class="badge bg-red text-white text-white">' . __('Unpublished') . '</span>'
                        : '<span class="badge bg-green text-white text-white">' . __('Published') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a class="dropdown-item" href="' . route('admin.edit.blog', $row->blog_id) . '">' . __('Edit') . '</a>';
                
                    if ($row->status == 0) {
                        $actions .= '<a class="dropdown-item" href="#" onclick="getBlog(\'' . $row->blog_id . '\', \'publish\'); return false;">' . __('Publish') . '</a>';
                    } else {
                        $actions .= '<a class="dropdown-item" href="#" onclick="getBlog(\'' . $row->blog_id . '\', \'unpublish\'); return false;">' . __('Unpublish') . '</a>';
                    }
                
                    $actions .= '<a class="dropdown-item" href="#" onclick="getBlog(\'' . $row->blog_id . '\', \'delete\'); return false;">' . __('Delete') . '</a>';
                
                    return '
                        <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <div class="dropdown-menu dropdown-menu-end">
                            ' . $actions . '
                        </div>
                    ';
                })                
                ->rawColumns(['heading', 'tags', 'status', 'action'])
                ->make(true);
        }

        $settings = Setting::first();
        $config = DB::table('config')->get();

        return view('admin.pages.blogs.index', compact('settings', 'config'));
    }

    // Add Blog
    public function createBlog()
    {
        // Queries
        $blogsCategories = BlogCategory::where('status', '!=', 2)->get();

        // Queries
        $settings = Setting::first();
        $config = DB::table('config')->get();

        // View
        return view('admin.pages.blogs.create', compact('blogsCategories', 'settings', 'config'));
    }

    // Publish Blog
    public function publishBlog(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'blog_cover' => ['required', 'mimes:jpg,jpeg,png,webp'],
            'blog_name' => 'required|min:3',
            'blog_slug' => 'required|min:3',
            'short_description' => 'required|min:3',
            'long_description' => 'required|min:3',
            'category_id' => 'required',
            'tags' => 'required',
            'seo_title' => 'required',
            'seo_description' => 'required',
            'seo_keywords' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        
        // Uploaded file
        $blogCoverFile = $request->file('blog_cover');

        // Get original name and extension
        $originalName = $blogCoverFile->getClientOriginalName();
        $originalFilename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = strtolower($blogCoverFile->getClientOriginalExtension());

        // Allowed extensions
        $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp'];

        if (in_array($extension, $allowedExtensions)) {
            // Build filename and path
            $uniqueFilename = $originalFilename . '_' . uniqid() . '.' . $extension;
            $relativeStoragePath = 'images/blogs/cover-images/';
            $fullStoragePath = $relativeStoragePath . $uniqueFilename;

            // Store in storage/app/public/images/blogs/cover-images/
            Storage::disk('public')->put($fullStoragePath, file_get_contents($blogCoverFile));

            // Public access path (optional)
            $CoverImage = 'storage/' . $fullStoragePath;
        }

        // Generate a unique slug for the blog post
        $existingSlug = Blog::where('slug', $request->blog_slug)->first();

        if ($existingSlug) {
            $blogSlug = $this->createSlug($request->blog_name);
        } else {
            $blogSlug = $request->blog_slug;
        }

        // Step 1: Process base64 <img> tags
        $updatedBlogBody = preg_replace_callback(
            '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $extension = $matches[1]; // e.g., png, jpeg
                $decodedData = base64_decode($matches[2]);

                if ($decodedData !== false) {
                    $filename = Str::random(10) . '.' . $extension;
                    $relativePath = 'uploads/blogs/' . $filename;

                    Storage::disk('public')->put($relativePath, $decodedData);

                    return '<img src="' . asset('storage/' . $relativePath) . '" />';
                }

                return $matches[0]; // Return original tag if decoding fails
            },
            $request->long_description
        );

        // Step 2: Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
        $updatedBlogBody = preg_replace_callback(
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
            $updatedBlogBody
        );

        // Save Blog
        $blog = new Blog();
        $blog->published_by = Auth::user()->id;
        $blog->blog_id = uniqid();
        $blog->cover_image = $CoverImage;
        $blog->heading = ucfirst($request->blog_name);
        $blog->slug = $blogSlug;
        $blog->short_description = ucfirst($request->short_description);
        $blog->long_description = $updatedBlogBody;
        $blog->category = $request->category_id;
        $blog->tags = ucfirst($request->tags);
        $blog->title = ucfirst($request->seo_title);
        $blog->description = ucfirst($request->seo_description);
        $blog->keywords = $request->seo_keywords;
        $blog->save();

        // Redirect
        return redirect()->route('admin.create.blog')->with('success', trans('Published!'));
    }

    // Edit Blog
    public function editBlog($id)
    {
        // Queries
        $blogsCategories = BlogCategory::where('status', '!=', 2)->get();

        // Get page details
        $blogDetails = Blog::where('blog_id', $id)->where('status', '!=', 2)->first();

        if ($blogDetails) {
            // Queries
            $settings = Setting::first();
            $config = DB::table('config')->get();

            // View
            return view('admin.pages.blogs.edit', compact('blogsCategories', 'blogDetails', 'settings', 'config'));
        } else {
            return redirect()->route('admin.blogs')->with('failed', trans('Not Found!'));
        }
    }

    // Update Blog
    public function updateBlog(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'blog_name' => 'required|min:3',
            'blog_slug' => 'required|min:3',
            'short_description' => 'required|min:3',
            'long_description' => 'required|min:3',
            'category_id' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Blog id
        $blogId = $request->segment(3);

        // Check cover image
        if ($request->hasFile('blog_cover')) {
            // Validation
            $validator = Validator::make($request->all(), [
                'blog_cover' => ['required', 'mimes:jpg,jpeg,png,webp'],
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->all()[0])->withInput();
            }

            // Cover image file
            $blogCoverFile = $request->file('blog_cover');

            // Get original name and extension
            $originalName = $blogCoverFile->getClientOriginalName();
            $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower($blogCoverFile->getClientOriginalExtension());

            // Allowed image types
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp'];

            if (in_array($extension, $allowedExtensions)) {
                // Final file name and relative path
                $uniqueFilename = $filenameWithoutExt . '_' . uniqid() . '.' . $extension;
                $relativePath = 'images/blogs/cover-images/';
                $storagePath = $relativePath . $uniqueFilename;

                // Store the file in storage/app/public/images/blogs/cover-images/
                Storage::disk('public')->put($storagePath, file_get_contents($blogCoverFile));

                // Public path (accessible via browser)
                $CoverImage = 'storage/' . $storagePath;
            }

            // Update blog cover image
            Blog::where('blog_id', $blogId)->update(['cover_image' => $CoverImage]);
        }

        // Generate a unique slug for the blog post
        $existingSlug = Blog::where('slug', $request->blog_slug)->first();

        if ($existingSlug) {
            $blogSlug = $request->blog_slug;
        } else {
            $blogSlug = $this->createSlug($request->blog_name);
        }

        // Step 1: Process base64 <img> tags
        $updatedBlogBody = preg_replace_callback(
            '/<img[^>]+src=["\']data:image\/([^;\']+);base64,([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $extension = $matches[1]; // e.g., png, jpeg
                $decodedData = base64_decode($matches[2]);

                if ($decodedData !== false) {
                    $filename = Str::random(10) . '.' . $extension;
                    $relativePath = 'uploads/blogs/' . $filename;

                    Storage::disk('public')->put($relativePath, $decodedData);

                    return '<img src="' . asset('storage/' . $relativePath) . '" />';
                }

                return $matches[0]; // Return original tag if decoding fails
            },
            $request->long_description
        );

        // Step 2: Ensure <a> tags have target="_blank" and rel="noopener noreferrer"
        $updatedBlogBody = preg_replace_callback(
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
            $updatedBlogBody
        );

        // Update blog details
        Blog::where('blog_id', $blogId)->update([
            'heading' => ucfirst($request->blog_name), 'slug' => $blogSlug, 'short_description' => $request->short_description,
            'long_description' => $updatedBlogBody, 'category' => $request->category_id, 'tags' => ucfirst($request->tags), 'title' => ucfirst($request->seo_title),
            'description' => ucfirst($request->seo_description), 'keywords' => $request->seo_keywords
        ]);

        // Redirect
        return redirect()->route('admin.edit.blog', $blogId)->with('success', trans('Updated!'));
    }

    // Actions
    public function actionBlog(Request $request)
    {
        // Check status
        switch ($request->query('mode')) {
            case 'unpublish':
                $status = 0;
                break;

            case 'delete':
                $status = 2;
                break;

            default:
                $status = 1;
                break;
        }

        // Update status
        Blog::where('blog_id', $request->query('id'))->update(['status' => $status]);

        // Redirect
        return redirect()->route('admin.blogs')->with('success', trans('Updated!'));
    }
}
