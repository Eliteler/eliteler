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

namespace App;

use App\Blog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    protected $table = 'blog_categories';

    // Scope a query to only include published blogs (status = 1)
    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    public function blogs()
    {
        return $this->hasOne(Blog::class, 'category', 'blog_category_id');
    }
}
