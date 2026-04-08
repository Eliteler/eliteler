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

class BusinessCardDetail extends Model
{
    public function business_cards()
    {
        return $this->belongsTo(BusinessCard::class, '', 'card_id');
    }
}
