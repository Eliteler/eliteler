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
use Plugins\SMTP\Controllers\SMTPController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/plugin/smtp/settings', [SMTPController::class, 'smtpSettings'])->name('admin.plugin.smtp.settings');
    Route::post('admin/plugin/smtp/settings/update', [SMTPController::class, 'smtpSettingsUpdate'])->name('admin.smtp_settings.update')->middleware('demo.mode');
    Route::post('admin/plugin/test/email', [SMTPController::class, 'testEmail'])->name('admin.plugin.test.email')->middleware('demo.mode');
});
