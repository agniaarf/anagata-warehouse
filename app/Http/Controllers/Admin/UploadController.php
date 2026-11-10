<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use App\Services\VideoUploadService;
use App\Support\Cms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UploadController extends Controller
{
    public function store(Request $request, ImageUploadService $imageService, VideoUploadService $videoService): JsonResponse
    {
        // Deliberately not using Laravel's 'image'/'mimes:' rules here — both need
        // the fileinfo PHP extension to guess a MIME type, which isn't available
        // on every host. ImageUploadService::store() already verifies the file is
        // a real JPEG/PNG via getimagesize() (harder to spoof than MIME sniffing
        // anyway), so that check is the real gatekeeper — this just bounds size.
        $request->validate([
            'image' => ['nullable', 'required_without:video', 'file', 'max:2048'],
            'video' => ['nullable', 'required_without:image', 'file', 'max:20480'],
            'folder' => ['nullable', 'string', 'alpha_dash'],
        ], ['image.max' => 'Ukuran foto maksimal 2 MB.']);

        if ($request->hasFile('image') && $request->hasFile('video')) {
            throw ValidationException::withMessages([
                'image' => 'Upload either an image or a video, not both.',
                'video' => 'Upload either an image or a video, not both.',
            ]);
        }

        $folder = 'cms/'.($request->input('folder') ?: 'misc');

        if ($request->hasFile('video')) {
            try {
                $result = $videoService->store($request->file('video'), $folder);
            } catch (RuntimeException $e) {
                throw ValidationException::withMessages(['video' => $e->getMessage()]);
            }

            return response()->json([
                'path' => $result['path'],
                'format' => $result['format'],
                'media_type' => 'video',
                'url' => Cms::imageUrl($result['path']),
            ]);
        }

        try {
            $result = $imageService->store($request->file('image'), $folder);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['image' => $e->getMessage()]);
        }

        return response()->json([
            'path' => $result['path'],
            'width' => $result['width'],
            'height' => $result['height'],
            'media_type' => 'image',
            'url' => Cms::imageUrl($result['path']),
        ]);
    }
}
