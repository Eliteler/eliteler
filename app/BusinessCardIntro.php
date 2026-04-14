<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessCardIntro extends Model
{
    protected $table = 'business_card_intros';

    protected $fillable = [
        'business_card_intro_id',
        'intro_code',
        'intro_thumbnail',
        'intro_name',
        'status',
    ];

    /**
     * Get intros
     */
    public static function getIntros($status)
    {
        $query = self::query();

        if ($status === 'active') {
            $query->where('status', 1);
        } elseif ($status === 'disabled') {
            $query->where('status', 0);
        }

        return $query->get();
    }
    
    /**
     * Get Single Intro
     */

    public static function getIntro($id) {
        return self::query()->where('business_card_intro_id', $id)->first();
    }
}
