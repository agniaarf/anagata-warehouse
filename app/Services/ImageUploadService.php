<?php

namespace App\Services;

use App\Support\Cms;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageUploadService
{
    private const MAX_IMAGE_DIMENSION = 10000;
    private const MAX_IMAGE_PIXELS = 12000000;
    public const THUMB_DIMENSION = 720;

    /**
     * Resize (if needed) and convert an uploaded image to WebP, storing it on
     * the public disk. Returns the relative path plus the final dimensions
     * (used by callers to emit width/height attributes and prevent CLS).
     */
    public function store(UploadedFile $file, string $folder, int $maxDimension = 1920, int $quality = 82): array
    {
        $path = $file->getRealPath();
        // Suppressed: getimagesize() emits a PHP warning (which Laravel's error
        // handler escalates to an uncaught exception) on unreadable/garbage input
        // instead of just returning false — the return-value check below is the
        // real handling, this just stops that warning from bypassing it.
        $info = @getimagesize($path);
        if ($info === false) {
            throw new RuntimeException('Uploaded file is not a readable image.');
        }

        [$width, $height, $type] = $info;

        if (
            $width < 1 || $height < 1 ||
            $width > self::MAX_IMAGE_DIMENSION || $height > self::MAX_IMAGE_DIMENSION ||
            $width > self::MAX_IMAGE_PIXELS / $height
        ) {
            throw new RuntimeException('Image dimensions exceed the safe upload limit.');
        }

        if (! function_exists('imagewebp')) {
            throw new RuntimeException('The GD extension (with WebP support) is required for image uploads.');
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            default => throw new RuntimeException('Unsupported image type. Only JPG, JPEG, and PNG are accepted.'),
        };

        if ($image === false) {
            throw new RuntimeException('The uploaded image could not be opened.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (max($width, $height) > $maxDimension) {
            [$image, $width, $height] = $this->downscale($image, $width, $height, $maxDimension);
        }

        // Small grid-sized copy stored next to the original (see Cms::thumbnailPath),
        // so listings like the gallery don't download a 1920px file for a ~400px tile.
        $thumbData = null;
        if (max($width, $height) > self::THUMB_DIMENSION) {
            [$thumb] = $this->downscale($image, $width, $height, self::THUMB_DIMENSION, false);
            $thumbData = $this->encodeWebp($thumb, $quality);
        }

        $data = $this->encodeWebp($image, $quality);

        $relativePath = trim($folder, '/').'/'.Str::random(24).'.webp';
        if (! Storage::disk('public')->put($relativePath, $data)) {
            throw new RuntimeException('The converted image could not be saved.');
        }
        if ($thumbData !== null) {
            Storage::disk('public')->put(Cms::thumbnailPath($relativePath), $thumbData);
        }

        return ['path' => $relativePath, 'width' => $width, 'height' => $height];
    }

    /**
     * Create the grid-sized thumbnail for an already-stored WebP upload. Used by
     * `php artisan cms:thumbnails` to backfill images uploaded before thumbnails
     * existed. Returns false when no thumbnail is needed or it couldn't be made.
     */
    public function makeThumbnail(string $relativePath, int $quality = 82): bool
    {
        $disk = Storage::disk('public');
        if (! str_ends_with($relativePath, '.webp') || ! $disk->exists($relativePath) || ! function_exists('imagecreatefromwebp')) {
            return false;
        }

        $image = @imagecreatefromwebp($disk->path($relativePath));
        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if (max($width, $height) <= self::THUMB_DIMENSION) {
            imagedestroy($image);

            return false;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        [$thumb] = $this->downscale($image, $width, $height, self::THUMB_DIMENSION);

        return $disk->put(Cms::thumbnailPath($relativePath), $this->encodeWebp($thumb, $quality));
    }

    /**
     * Resample to fit within $maxDimension. Frees the source unless told not to
     * (the thumbnail path still needs the full-size image afterwards).
     *
     * @return array{0: \GdImage, 1: int, 2: int}
     */
    private function downscale($image, int $width, int $height, int $maxDimension, bool $destroySource = true): array
    {
        $ratio = $maxDimension / max($width, $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if ($destroySource) {
            imagedestroy($image);
        }

        return [$resized, $newWidth, $newHeight];
    }

    /** Encode a GD image as WebP bytes, freeing the image. */
    private function encodeWebp($image, int $quality): string
    {
        ob_start();
        $encoded = imagewebp($image, null, $quality);
        $data = ob_get_clean();
        imagedestroy($image);

        if (! $encoded || $data === false || $data === '') {
            throw new RuntimeException('The uploaded image could not be converted to WebP.');
        }

        return $data;
    }
}
