<?php
use Illuminate\Support\Facades\Route;
use Plugins\WebTools\Controllers\WebToolsController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes (working)
    Route::get('admin/plugin/webtools', [WebToolsController::class, 'index'])->name('admin.plugin.webtools');
    Route::post('admin/plugin/update-webtools', [WebToolsController::class, 'update'])->name('admin.plugin.update.webtools')->middleware('demo.mode');
});