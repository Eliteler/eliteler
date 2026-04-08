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

use Illuminate\Support\Facades\Route;
use Plugins\CookieConsent\Controllers\CookieConsentController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes
    Route::get('admin/plugin/cookie-consent', [CookieConsentController::class, 'index'])->name('admin.plugin.cookie-consent');
    Route::post('admin/plugin/cookie-consent/update', [CookieConsentController::class, 'update'])->name('admin.plugin.cookie-consent.update')->middleware(['demo.mode']);
});