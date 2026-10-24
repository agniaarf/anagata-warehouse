<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\AnalyticsCollectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::post('/analytics/collect', AnalyticsCollectionController::class)
    ->middleware('throttle:60,1')
    ->name('analytics.collect');

// Fallback for hosts where PHP's symlink() is disabled, so `storage:link` can't
// create a real public/storage symlink. Inert wherever that symlink does exist —
// the web server serves the linked file straight off disk and this route is
// never reached — so it's always safe to leave active.
Route::get('/storage/{path}', function (string $path) {
    $root = realpath(storage_path('app/public'));
    $full = realpath($root.'/'.$path);

    abort_if($full === false || ! str_starts_with($full, $root.DIRECTORY_SEPARATOR), 404);
    abort_if(is_dir($full), 404);

    // Content-Type is set explicitly from the extension rather than left for
    // response()->file() to guess — that guess also needs the fileinfo PHP
    // extension, which isn't available on every host (same reason
    // UploadController avoids Laravel's 'image'/'mimes:' rules).
    $mime = match (strtolower(pathinfo($full, PATHINFO_EXTENSION))) {
        'webp' => 'image/webp',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        default => 'application/octet-stream',
    };

    return response()->file($full, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->where('path', '.*')->name('storage.local');

// CMS pages are available under /bojong.
Route::prefix('bojong')->middleware('throttle:20,1')->group(function () {
    require __DIR__.'/auth.php';

    Route::middleware('auth')->group(function () {
        require __DIR__.'/admin.php';
    });
});
