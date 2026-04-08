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
use Plugins\MaintenanceMode\Controllers\MaintenanceModeController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes
    Route::get('admin/plugin/maintenance-mode', [MaintenanceModeController::class, 'index'])->name('admin.plugin.maintenance-mode');
    Route::post('admin/plugin/maintenance-mode/update', [MaintenanceModeController::class, 'update'])->name('admin.plugin.maintenance-mode.update')->middleware(['demo.mode']);
});