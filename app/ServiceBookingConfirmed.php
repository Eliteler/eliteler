<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceBookingConfirmed extends Model
{
    // Fillable
    protected $fillable = [
        'service_booking_confirmed_id',
        'user_id',
        'vcard_id',
        'fullname',
        'email',
        'mobile_number',
        'address',
        'checkin_date',
        'checkin_time',
        'checkout_date',
        'checkout_time',
        'number_of_guests',
        'notes',
        'service_booking_confirmed_status'
    ];
}
