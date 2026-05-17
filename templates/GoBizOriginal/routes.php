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
use Templates\GoBizOriginal\Controllers\GoBizOriginalController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Pages
    Route::get('admin/web-template/gobiz-original/pages', [GoBizOriginalController::class, 'index'])->name('admin.web-template.gobiz-original')->middleware('user.page.permission:web_templates');
    Route::get('admin/web-template/gobiz-original/custom-pages', [GoBizOriginalController::class, "customPagesIndex"])->name('admin.web-template.gobiz-original.custom-pages')->middleware('user.page.permission:web_templates');

    // Edit page    
    Route::get('admin/web-template/gobiz-original/pages/page/{id}', [GoBizOriginalController::class, "editPage"])->name('admin.web-template.gobiz-original.edit-page')->middleware('user.page.permission:web_templates');
    Route::post('admin/web-template/gobiz-original/pages/update-page/{id}', [GoBizOriginalController::class, "updatePage"])->name('admin.web-template.gobiz-original.update-page')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Add / Edit custom page
    Route::get('admin/web-template/gobiz-original/pages/add-custom-page', [GoBizOriginalController::class, "addCustomPage"])->name('admin.web-template.gobiz-original.add-custom-page')->middleware('user.page.permission:web_templates');
    Route::post('admin/web-template/gobiz-original/pages/save-custom-page', [GoBizOriginalController::class, "saveCustomPage"])->name('admin.web-template.gobiz-original.save-custom-page')->middleware(['user.page.permission:web_templates', 'demo.mode']);
    Route::get('admin/web-template/gobiz-original/pages/custom-page/{id}', [GoBizOriginalController::class, "editCustomPage"])->name('admin.web-template.gobiz-original.edit-custom-page')->middleware('user.page.permission:web_templates');
    Route::post('admin/web-template/gobiz-original/pages/custom-page-update', [GoBizOriginalController::class, "updateCustomPage"])->name('admin.web-template.gobiz-original.update-custom-page')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Update page status
    Route::get('admin/web-template/gobiz-original/pages/update-page-status', [GoBizOriginalController::class, "updateStatus"])->name('admin.web-template.gobiz-original.update-page-status')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Delete page
    Route::get('admin/web-template/gobiz-original/pages/delete-page', [GoBizOriginalController::class, "deletePage"])->name('admin.web-template.gobiz-original.delete-page')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Config
    Route::get('admin/web-template/gobiz-original/config', [GoBizOriginalController::class, "templateConfig"])->name('admin.web-template.gobiz-original.config')->middleware('user.page.permission:web_templates');
    Route::post('admin/web-template/gobiz-original/config', [GoBizOriginalController::class, "updateConfig"])->name('admin.web-template.gobiz-original.update-config')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Update announcements
    Route::post('admin/web-template/gobiz-original/update-announcements', [GoBizOriginalController::class, "updateAnnouncement"])->name('admin.web-template.gobiz-original.update-announcements')->middleware(['user.page.permission:web_templates', 'demo.mode']);
    Route::post('admin/web-template/gobiz-original/update-popup', [GoBizOriginalController::class, "updatePopup"])->name('admin.web-template.gobiz-original.update-popup')->middleware(['user.page.permission:web_templates', 'demo.mode']);

    // Mobile Application Action Banner
    Route::post('admin/web-template/gobiz-original/app-action-banner', [GoBizOriginalController::class, "appActionBanner"])->name('admin.web-template.gobiz-original.app-action-banner')->middleware(['user.page.permission:web_templates', 'demo.mode']);
});
