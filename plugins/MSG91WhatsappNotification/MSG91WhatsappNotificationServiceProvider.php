<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace Plugins\MSG91WhatsappNotification;

use App\BookedAppointment;
use App\User;
use Illuminate\Support\ServiceProvider;
use Plugins\MSG91WhatsappNotification\Observers\MSG91WhatsappAppointmentNotificationObserver;
use Plugins\MSG91WhatsappNotification\Observers\MSG91WhatsappNotificationObserver;

class MSG91WhatsappNotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/Views', 'msg91');

        User::observe(MSG91WhatsappNotificationObserver::class);
        BookedAppointment::observe(MSG91WhatsappAppointmentNotificationObserver::class);
    }

    public function register()
    {
        // You can register other services if needed
    }
}
