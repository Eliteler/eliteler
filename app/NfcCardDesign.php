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

use App\NfcCardOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NfcCardDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfc_card_id',
        'nfc_card_name',
        'product_type',
        'short_description',
        'nfc_card_description',
        'nfc_card_front_image',
        'nfc_card_back_image',
        'gallery_images',
        'nfc_card_price',
        'compare_at_price',
        'available_stocks',
        'generates_keys',
        'allow_logo_upload',
        'image_assets',
        'default_link_type',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'gallery_images'    => 'array',
        'generates_keys'    => 'boolean',
        'allow_logo_upload' => 'boolean',
        'image_assets'      => 'array',
    ];

    /**
     * All product photos: the gallery when set, otherwise the old
     * front/back images (designs created before Smart Products).
     *
     * @return array<int, string>
     */
    public function photos(): array
    {
        $gallery = array_values(array_filter((array) $this->gallery_images));

        if ($gallery) {
            return $gallery;
        }

        return array_values(array_unique(array_filter([$this->nfc_card_front_image, $this->nfc_card_back_image])));
    }

    /** Can it be ordered right now? Stock is only the available_stocks count. */
    public function inStock(): bool
    {
        return (int) $this->available_stocks > 0;
    }

    public function nfcCardOrders()
    {
        return $this->hasMany(NfcCardOrder::class, 'nfc_card_id', 'nfc_card_id');
    }
}
