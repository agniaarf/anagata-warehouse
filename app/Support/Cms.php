<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Cms
{
    /**
     * Resolve a stored image reference to a renderable URL.
     * Absolute URLs (legacy seeded Unsplash links, external assets) pass through
     * unchanged; relative paths (admin uploads) resolve via the public disk.
     */
    public static function imageUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : Storage::disk('public')->url($path);
    }

    /**
     * Grid-sized version of an image: the `.thumb.webp` sibling that
     * ImageUploadService writes for uploads (falls back to the original when
     * it doesn't exist yet), or a resized Unsplash URL for seeded placeholders.
     */
    public static function thumbUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }
        if (self::isUnsplash($path)) {
            return self::unsplashAtWidth($path, 720);
        }
        if (! Str::startsWith($path, ['http://', 'https://'])) {
            $thumb = self::thumbnailPath($path);
            if ($thumb !== $path && Storage::disk('public')->exists($thumb)) {
                return Storage::disk('public')->url($thumb);
            }
        }

        return self::imageUrl($path);
    }

    /** Full-size version for a lightbox — uncropped for Unsplash placeholders. */
    public static function largeUrl(?string $path): string
    {
        return $path && self::isUnsplash($path)
            ? self::unsplashAtWidth($path, 1600)
            : self::imageUrl($path);
    }

    /** `cms/gallery/abc.webp` → `cms/gallery/abc.thumb.webp` */
    public static function thumbnailPath(string $path): string
    {
        return preg_replace('/(?<!\.thumb)\.webp$/i', '.thumb.webp', $path);
    }

    private static function isUnsplash(string $url): bool
    {
        return Str::startsWith($url, 'https://images.unsplash.com/');
    }

    /** Re-request an Unsplash image at a given width, dropping any fixed-height crop. */
    private static function unsplashAtWidth(string $url, int $width): string
    {
        [$base, $query] = array_pad(explode('?', $url, 2), 2, '');
        parse_str($query, $params);
        unset($params['h'], $params['fit']);
        $params = array_merge($params, ['w' => $width, 'q' => 80, 'auto' => 'format']);

        return $base.'?'.http_build_query($params);
    }
}
