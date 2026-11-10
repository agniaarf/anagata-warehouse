<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VideoUploadService
{
    /**
     * Store a CMS intro video after checking the actual container signature.
     * Videos are not transcoded on the web host; editors upload an optimized
     * WebM or MP4 that is preserved byte-for-byte.
     */
    public function store(UploadedFile $file, string $folder): array
    {
        $path = $file->getRealPath();
        $handle = @fopen($path, 'rb');
        $header = $handle ? fread($handle, 16) : false;
        if (is_resource($handle)) {
            fclose($handle);
        }

        if (! is_string($header) || strlen($header) < 8) {
            throw new RuntimeException('Video tidak dapat dibaca.');
        }

        $extension = match (true) {
            substr($header, 0, 4) === "\x1A\x45\xDF\xA3" => 'webm',
            substr($header, 4, 4) === 'ftyp' => 'mp4',
            default => throw new RuntimeException('Format video harus WebM atau MP4 yang valid.'),
        };

        $relativePath = trim($folder, '/').'/'.Str::random(24).'.'.$extension;
        $stream = @fopen($path, 'rb');
        if (! is_resource($stream) || ! Storage::disk('public')->put($relativePath, $stream)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new RuntimeException('Video tidak dapat disimpan.');
        }
        if (is_resource($stream)) {
            fclose($stream);
        }

        return ['path' => $relativePath, 'format' => $extension];
    }
}
