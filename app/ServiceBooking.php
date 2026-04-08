<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    // Fillable fields
    protected $fillable = [
        'service_booking_id',
        'user_id',
        'vcard_id',
        'service_booking',
        'service_booking_available_days',
        'service_booking_amount',
        'service_booking_start_time',
        'service_booking_end_time',
        'service_booking_receive_email',
    ];

    // Store times consistently with seconds
    public function setServiceBookingStartTimeAttribute($value)
    {
        $this->attributes['service_booking_start_time'] =
            $value ? \Carbon\Carbon::createFromFormat('H:i', $value)->format('H:i:s') : null;
    }

    public function setServiceBookingEndTimeAttribute($value)
    {
        $this->attributes['service_booking_end_time'] =
            $value ? \Carbon\Carbon::createFromFormat('H:i', $value)->format('H:i:s') : null;
    }

    // Always return without seconds (HH:MM)
    public function getServiceBookingStartTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null;
    }

    public function getServiceBookingEndTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null;
    }
}
