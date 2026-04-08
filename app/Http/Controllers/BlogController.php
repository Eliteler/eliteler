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

use App\Blog;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\OpenGraph;

class BlogController extends Controller
{
    // Blogs
    public function blogs()
    {
        // Queries
        $blogs = Blog::where('status', 1)
            ->with('blogCategory')
            ->latest() // orders by created_at desc
            ->paginate(6);
        $settings = GoBizCommonService::settings();
        $config = GoBizCommonService::config();

        // Get page details
        $web_template = getConfigData('web_template');
        $page = GoBizCommonService::templatePageContentGet('home', $web_template);
        $supportPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

        // Seo Tools
        SEOTools::setTitle('Blogs' . ' - ' . $page[0]->title);
        SEOTools::setDescription('Blogs' . ' - ' . $page[0]->description);

        SEOMeta::setTitle('Blogs' . ' - ' . $page[0]->title);
        SEOMeta::setDescription('Blogs' . ' - ' . $page[0]->description);
        SEOMeta::addMeta('article:section', 'Blogs', 'property');
        SEOMeta::addKeyword([$page[0]->keywords]);

        // Add Canonical URL
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle('Blogs' . ' - ' . $page[0]->title);
        OpenGraph::setDescription('Blogs' . ' - ' . $page[0]->description);
        OpenGraph::setUrl(URL::full());
        OpenGraph::addImage([asset($settings->site_logo), 'size' => 300]);

        JsonLd::setTitle('Blogs' . ' - ' . $page[0]->title);
        JsonLd::setDescription('Blogs' . ' - ' . $page[0]->description);
        JsonLd::addImage(asset($settings->site_logo));

        // Return values
        $returnValues = compact('blogs', 'config', 'settings', 'supportPage');

        return view($web_template . '::Website.pages.blogs.index', $returnValues);
    }

    // View blog post
    public function viewBlog($slug)
    {
        // Queries
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();
        $settings = GoBizCommonService::settings();
        $config = GoBizCommonService::config();

        if ($blogDetails) {
            // Get page details
            $web_template = getConfigData('web_template');
            $supportPage = GoBizCommonService::templatePageContentGet('contact', $web_template);

            // Recent blogs (except current viewed blog)
            $recentBlogs = Blog::where('slug', '!=', $slug)->where('status', 1)->limit(2)->orderBy('created_at', 'desc')->get();

            // Seo Tools
            SEOTools::setTitle($blogDetails->title);
            SEOTools::setDescription($blogDetails->description);
            SEOTools::addImages(asset($blogDetails->cover_image));

            SEOMeta::setTitle($blogDetails->title);
            SEOMeta::setDescription($blogDetails->description);
            SEOMeta::addMeta('article:section', $blogDetails->title, 'property');
            SEOMeta::addKeyword([$blogDetails->keywords]);

            // Add Canonical URL
            SEOMeta::setCanonical(url()->current());

            OpenGraph::setTitle($blogDetails->title);
            OpenGraph::setDescription($blogDetails->description);
            OpenGraph::addProperty('type', 'article');
            OpenGraph::setUrl(url("blog/" . $blogDetails->slug));

            JsonLd::setType('Article');
            JsonLd::setTitle($blogDetails->title);
            JsonLd::setDescription($blogDetails->description);

            // Return values
            $returnValues = compact('blogDetails', 'recentBlogs', 'config', 'settings', 'supportPage');

            return view($web_template . '::Website.pages.blogs.view', $returnValues);
        } else {
            abort(404);
        }
    }
}
