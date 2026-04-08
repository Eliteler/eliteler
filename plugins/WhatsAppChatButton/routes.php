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
use Plugins\WhatsAppChatButton\Controllers\WhatsAppChatButtonController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/plugin/whatsapp_chat_button/settings', [WhatsAppChatButtonController::class, 'whatsAppChatButtonSettings'])->name('admin.plugin.whatsapp_chat_button.settings');
    Route::post('admin/plugin/whatsapp_chat_button/settings/update', [WhatsAppChatButtonController::class, 'whatsAppChatButtonSettingsUpdate'])->name('admin.whatsapp_chat_button_settings.update')->middleware('demo.mode');
});
