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

/** A file a customer uploaded for one image asset of a Smart Product order. */
class NfcCardOrderAsset extends Model
{
    protected $fillable = [
        'nfc_card_order_id',
        'asset_key',
        'file_path',
        'file_type',
        'original_name',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(NfcCardOrder::class, 'nfc_card_order_id', 'nfc_card_order_id');
    }
}
