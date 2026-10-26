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
use Plugins\WebTools\Controllers\WebToolsController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes (working)
    Route::get('admin/plugin/webtools', [WebToolsController::class, 'index'])->name('admin.plugin.webtools');
    Route::post('admin/plugin/update-webtools', [WebToolsController::class, 'update'])->name('admin.plugin.update.webtools')->middleware('demo.mode');
});