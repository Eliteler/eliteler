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

use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    protected $fillable = [
        'card_id',
        'product_id',
        'category_id',
        'badge',
        'product_image',
        'product_name',
        'product_short_description',
        'product_description',
        'regular_price',
        'sales_price',
        'product_status'
    ];

    public function product_category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id', 'category_id');
    }
}
 