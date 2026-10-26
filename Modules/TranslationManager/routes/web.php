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
use Modules\TranslationManager\app\Http\Controllers\TranslationManagerController;

// Admin only, with the "translations" permission. {locale} is a real locale
// code (en, pt_BR) so it can never point outside resources/lang.
Route::middleware(['web', 'auth', 'admin', 'twofactor', 'user.page.permission:translations'])
    ->prefix('admin/translations')
    ->name('translation-manager.')
    ->where(['locale' => '[a-z]{2,3}(_[A-Za-z]{2,4})?'])
    ->group(function () {
        Route::get('/', [TranslationManagerController::class, 'index'])->name('index');
        Route::get('/create', [TranslationManagerController::class, 'create'])->name('create');
        Route::post('/', [TranslationManagerController::class, 'store'])->name('store')->middleware('demo.mode');
        Route::get('/{locale}/edit', [TranslationManagerController::class, 'edit'])->name('edit');
        Route::post('/{locale}/update', [TranslationManagerController::class, 'update'])->name('update')->middleware('demo.mode');
        Route::post('/add-key', [TranslationManagerController::class, 'addKey'])->name('add-key')->middleware('demo.mode'); // New route to append key
        Route::get('/{locale}/missing', [TranslationManagerController::class, 'missing'])->name('missing');
        Route::post('/{locale}/sync-missing', [TranslationManagerController::class, 'syncMissing'])->name('sync-missing')->middleware('demo.mode');
        Route::get('/sync-status/active', [TranslationManagerController::class, 'activeSyncStatuses'])->name('sync-status.active');
        Route::get('/{locale}/sync-status', [TranslationManagerController::class, 'syncStatus'])->name('sync-status'); // Poll background sync job progress
        Route::get('/{locale}/export', [TranslationManagerController::class, 'export'])->name('export');
        Route::post('/import', [TranslationManagerController::class, 'import'])->name('import')->middleware('demo.mode');
        Route::delete('/{locale}', [TranslationManagerController::class, 'destroy'])->name('destroy')->middleware('demo.mode');
    });
