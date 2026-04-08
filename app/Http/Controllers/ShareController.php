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
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;

class ShareController extends Controller
{
    // Blog post share
    public function shareToFacebook($slug)
    {
        // Blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        $url = url('blog/' . $blogDetails->slug);;
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);

        // Create a share URL for Facebook
        $facebookShareUrl = "https://www.facebook.com/sharer/sharer.php?" . http_build_query([
            'u' => $url,
            'quote' => $title,  // Optional: Include a quote parameter to add a predefined text when sharing
            'description' => $description,  // Optional: Include the description parameter for the shared content
            'picture' => $image  // Optional: Include the image parameter for the shared content
        ]);

        return redirect()->away($facebookShareUrl);
    }

    public function shareToTwitter($slug)
    {
        // Blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        $url = url('blog/' . $blogDetails->slug);;
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);

        // Create a share URL for Twitter
        $twitterShareUrl = "https://twitter.com/intent/tweet?" . http_build_query([
            'text' => $title,  // The text to be included in the tweet
            'url' => $url,  // The URL to be included in the tweet
            'description' => $description,  // Optional: Description of the shared content
            'image' => $image  // Optional: URL of the image to be included in the tweet
        ]);

        return redirect()->away($twitterShareUrl);
    }

    public function shareToLinkedIn($slug)
    {
        // Blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        $url = url('blog/' . $blogDetails->slug);;
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);
        $source = url('blog/' . $blogDetails->slug);;

        // Create a meta tag URL for LinkedIn
        $linkedInMetaUrl = "https://www.linkedin.com/shareArticle?" . http_build_query([
            'url' => $url,
            'title' => $title,
            'summary' => $description,
            'source' => $source,
            'mini' => 'true',  // This parameter ensures that the LinkedIn share dialog is in a smaller format
            'images' => $image  // You can specify multiple images separated by commas if needed
        ]);

        $linkedInMetaUrl = "https://www.linkedin.com/shareArticle?url={$url}&title={$title}&summary={$description}&source{$source}";

        return redirect()->away($linkedInMetaUrl);
    }

    public function shareToInstagram($slug)
    {
        // Queries
        $settings = GoBizCommonService::settings();
        $config = GoBizCommonService::config();

        $web_template = getConfigData('web_template');

        // Get page details
        $supportPage = GoBizCommonService::templatePageContentFirst('contact', $config[93]->config_value);

        // Retrieve blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        if (!$blogDetails) {
            return redirect()->route('404'); // Handle the case where the blog post isn't found
        }

        $url = url('blog/' . $blogDetails->slug);
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);

        // Generate Instagram share caption with post details
        $instagramCaption = trans("Check out my latest post: '{$title}'. Visit the full post at: {$url} #blog #latestpost");

        // Instead of redirecting, you should return a view where the user can manually share this content on Instagram
        return view($web_template . '::Website.pages.blogs.share.instagram', [
            'image' => $image,
            'caption' => $instagramCaption,
            'settings' => $settings,
            'config' => $config,
            'supportPage' => $supportPage
        ]);
    }

    public function shareToWhatsApp($slug)
    {
        // Blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        $url = url('blog/' . $blogDetails->slug);;
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);

        $whatsAppShareLink = "https://wa.me/?text=" . urlencode(trans("Check out this post: {$title} - {$description} - {$url}"));

        return redirect()->away($whatsAppShareLink);
    }

    // Share to Email
    public function shareToEmail($slug)
    {
        // Blog details
        $blogDetails = Blog::where('slug', $slug)->where('status', 1)->first();

        $url = url('blog/' . $blogDetails->slug);;
        $title = $blogDetails->title;
        $description = $blogDetails->description;
        $image = asset($blogDetails->cover_image);

        $emailShareLink = "mailto:?subject=" . urlencode(trans("Check out this post: {$title} - {$description} - {$url}")) . "&body=" . urlencode(trans("Check out this post: {$title} - {$description} - {$url}"));

        return redirect()->away($emailShareLink);
    }
}
