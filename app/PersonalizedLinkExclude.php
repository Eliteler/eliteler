<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalizedLinkExclude extends Model
{
    protected $fillable = ['keyword'];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
