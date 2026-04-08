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
use Plugins\GoogleAdSense\Controllers\GoogleAdSenseController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/plugin/google_adsense/settings', [GoogleAdSenseController::class, 'googleAdSenseSettings'])->name('admin.plugin.google_adsense.settings');
    Route::post('admin/plugin/google_adsense/settings/update', [GoogleAdSenseController::class, 'googleAdSenseSettingsUpdate'])->name('admin.google_adsense_settings.update')->middleware('demo.mode');
});
