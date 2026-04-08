<?php

use Illuminate\Support\Facades\Route;
use Plugins\AiBuilder\Controllers\AiBuilderController;
use Plugins\AiBuilder\Controllers\User\AiBuilderController as UserAiBuilderController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // Admin routes (working)
    Route::get('admin/plugin/aibuilder', [AiBuilderController::class, 'index'])->name('admin.plugin.aibuilder');
    Route::post('admin/plugin/update-aibuilder', [AiBuilderController::class, 'update'])->name('admin.plugin.update.aibuilder');
});

Route::group(['as' => 'user.', 'prefix' => 'user', 'namespace' => 'User', 'middleware' => ['auth', 'user', 'frame.destroyer', 'twofactor', 'scriptsanitizer'], 'where' => ['locale' => '[a-zA-Z]{2}']], function () {
    // User routes (working)
    Route::get('aibuilder', [UserAiBuilderController::class, 'index'])->name('aibuilder');
    Route::post('aibuilder/generate', [UserAiBuilderController::class, 'generate'])->name('aibuilder.generate');
});
