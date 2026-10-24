<?php

namespace App\Console\Commands;

use App\Services\ImageUploadService;
use App\Support\Cms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Backfill `.thumb.webp` files for CMS uploads made before ImageUploadService
 * started writing them. Safe to re-run: existing thumbnails are skipped, and
 * pages already fall back to the original image when a thumbnail is missing.
 */
class GenerateCmsThumbnails extends Command
{
    protected $signature = 'cms:thumbnails';

    protected $description = 'Generate missing grid-sized thumbnails for uploaded CMS images';

    public function handle(ImageUploadService $service): int
    {
        $disk = Storage::disk('public');
        $made = 0;

        foreach ($disk->allFiles('cms') as $path) {
            if (! str_ends_with($path, '.webp') || str_ends_with($path, '.thumb.webp')) {
                continue;
            }
            if ($disk->exists(Cms::thumbnailPath($path))) {
                continue;
            }
            if ($service->makeThumbnail($path)) {
                $made++;
            }
        }

        $this->info("Generated {$made} thumbnail(s).");

        return self::SUCCESS;
    }
}
