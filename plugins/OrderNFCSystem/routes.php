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
use Plugins\OrderNFCSystem\Controllers\NFCCardOrderController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes (working)
    Route::get('admin/plugin/nfc-card-order-system/status', [NFCCardOrderController::class, 'index'])->name('admin.plugin.status.nfc.cards.order')->middleware(['user.page.permission:nfc_card_orders']);
    Route::post('admin/plugin/nfc-card-order-system/update', [NFCCardOrderController::class, 'update'])->name('admin.plugin.update.status.nfc.cards.order')->middleware(['user.page.permission:nfc_card_orders', 'demo.mode']);
});