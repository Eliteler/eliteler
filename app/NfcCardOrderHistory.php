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

/** One step in a Smart Product order's tracking timeline. */
class NfcCardOrderHistory extends Model
{
    protected $fillable = [
        'nfc_card_order_id',
        'order_status',
        'message',
        'tracking_number',
        'courier_partner',
        'notified',
        'created_by',
    ];

    protected $casts = [
        'notified' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(NfcCardOrder::class, 'nfc_card_order_id', 'nfc_card_order_id');
    }
}
