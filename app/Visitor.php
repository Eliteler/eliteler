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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One page view of a public vCard / store. card_id is business_cards.card_id
 * (the stable id, not the URL slug). Rows are written by
 * App\Services\Analytics\VisitTracker.
 */
class Visitor extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'is_unique' => 'boolean',
        'is_bot'    => 'boolean',
    ];

    /** Visits by people, not crawlers. */
    public function scopeHuman($query)
    {
        return $query->where('is_bot', 0);
    }
}
