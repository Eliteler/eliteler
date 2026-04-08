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
use Plugins\SubDomain\Controllers\SubDomainController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes
    Route::get('admin/plugin/sub-domain', [SubDomainController::class, 'index'])->name('admin.plugin.sub-domain');
    Route::post('admin/plugin/sub-domain/update', [SubDomainController::class, 'update'])->name('admin.plugin.sub-domain.update')->middleware(['demo.mode']);
});