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

class WebPopup extends Model
{
    protected $fillable = [
        'template_id',
        'enabled',
        'image',
        'link',
        'status',
    ];
}
