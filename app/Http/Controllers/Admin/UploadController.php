<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use App\Support\Cms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UploadController extends Controller
{
    public function store(Request $request, ImageUploadService $service): JsonResponse
    {
        // Deliberately not using Laravel's 'image'/'mimes:' rules here — both need
        // the fileinfo PHP extension to guess a MIME type, which isn't available
        // on every host. ImageUploadService::store() already verifies the file is
        // a real JPEG/PNG via getimagesize() (harder to spoof than MIME sniffing
        // anyway), so that check is the real gatekeeper — this just bounds size.
        $request->validate([
            'image' => ['required', 'file', 'max:2048'],
            'folder' => ['nullable', 'string', 'alpha_dash'],
        ]);

        $folder = 'cms/'.($request->input('folder') ?: 'misc');

        try {
            $result = $service->store($request->file('image'), $folder);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['image' => $e->getMessage()]);
        }

        return response()->json([
            'path' => $result['path'],
            'width' => $result['width'],
            'height' => $result['height'],
            'url' => Cms::imageUrl($result['path']),
        ]);
    }
}
