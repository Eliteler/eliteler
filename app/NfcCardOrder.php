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

use App\User;
use App\NfcCardDesign;
use App\NfcCardOrderAsset;
use App\NfcCardOrderHistory;
use App\NfcCardOrderTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NfcCardOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfc_card_order_id',
        'user_id',
        'nfc_card_id',
        'nfc_card_order_transaction_id',
        'nfc_card_logo',
        'order_details',
        'delivery_address',
        'delivery_note',
        'order_status',
        'is_nfc_written',
        'is_locked',
        'shipped_at',
        'delivered_at',
        'status',
    ];

    protected $casts = [
        'shipped_at'   => 'datetime',
        'delivered_at' => 'datetime',
        'is_locked'    => 'boolean',
    ];

    /** user_id holds users.id (orders are saved with Auth::id()). */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function nfcCardDesign()
    {
        return $this->belongsTo(NfcCardDesign::class, 'nfc_card_id', 'nfc_card_id');
    }

    public function nfcCardTransaction()
    {
        return $this->belongsTo(NfcCardOrderTransaction::class, 'nfc_card_order_transaction_id', 'nfc_card_order_transaction_id');
    }

    /** Files uploaded for the order's image assets (one per asset). */
    public function assets()
    {
        return $this->hasMany(NfcCardOrderAsset::class, 'nfc_card_order_id', 'nfc_card_order_id');
    }

    /** Tracking timeline, oldest first. */
    public function histories()
    {
        return $this->hasMany(NfcCardOrderHistory::class, 'nfc_card_order_id', 'nfc_card_order_id')->orderBy('id');
    }
}
