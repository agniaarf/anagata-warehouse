<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\WebsiteController;
use Illuminate\Support\Facades\Route;

// These routes are nested under the /bojong prefix and auth middleware.
Route::redirect('/', '/bojong/website');
Route::redirect('/dashboard', '/bojong/website');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');

// Homepage sections, their display order, and their copy.
Route::prefix('website')->name('admin.website.')->group(function () {
    Route::get('/', [WebsiteController::class, 'homepage'])->name('homepage');
    Route::post('/{slug}/visibility', [WebsiteController::class, 'toggleVisible'])->name('toggle-visible');
    Route::put('/{slug}/nav', [WebsiteController::class, 'updateNav'])->name('update-nav');
    Route::post('/{slug}/move/{direction}', [WebsiteController::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('move');
    Route::get('/{slug}/edit', [WebsiteController::class, 'editSection'])->name('section.edit');
    Route::put('/{slug}/edit', [WebsiteController::class, 'updateSection'])->name('section.update');
});

// Repeatable lists such as photos, service cards, and warehouse locations.
Route::prefix('content')->name('admin.content.')->group(function () {
    Route::get('/', [ContentController::class, 'index'])->name('index');
    Route::get('/{section}/{group}', [ContentController::class, 'show'])->name('show');
    Route::post('/{section}/{group}/items', [ContentController::class, 'storeItem'])->name('items.store');
    Route::put('/{section}/{group}/items/{item}', [ContentController::class, 'updateItem'])->name('items.update');
    Route::delete('/{section}/{group}/items/{item}', [ContentController::class, 'destroyItem'])->name('items.destroy');
    Route::post('/{section}/{group}/items/{item}/move/{direction}', [ContentController::class, 'moveItem'])
        ->whereIn('direction', ['up', 'down'])
        ->name('items.move');
});

// Site identity, contact details, and search settings.
Route::prefix('settings')->name('admin.settings.')->group(function () {
    Route::get('/{tab}', [SettingsController::class, 'show'])->name('show');
    Route::put('/{tab}', [SettingsController::class, 'update'])->name('update');
});

Route::post('/uploads', [UploadController::class, 'store'])->name('admin.uploads.store');
