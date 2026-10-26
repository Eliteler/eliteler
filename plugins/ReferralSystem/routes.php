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
use Plugins\ReferralSystem\Controllers\ReferralSystemController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes (working)
    Route::get('admin/plugin/referral-system', [ReferralSystemController::class, 'index'])->name('admin.plugin.referral.system');
    Route::post('admin/plugin/update-referral-system', [ReferralSystemController::class, 'update'])->name('admin.plugin.update.status.referral.system')->middleware('demo.mode');
});