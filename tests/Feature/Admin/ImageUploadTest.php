<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\Cms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_png_upload_is_converted_to_webp(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP support is not enabled in this PHP runtime.');
        }

        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('banner.png', 64, 48),
        ])->assertOk();

        $path = $response->json('path');
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_large_upload_also_gets_a_grid_thumbnail(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP support is not enabled in this PHP runtime.');
        }

        Storage::fake('public');
        $user = User::factory()->create();

        $path = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('warehouse.png', 1600, 900),
        ])->assertOk()->json('path');

        $thumb = Cms::thumbnailPath($path);
        Storage::disk('public')->assertExists($thumb);
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($thumb));
        $this->assertSame([720, 405], [$width, $height]);
        $this->assertSame(Storage::disk('public')->url($thumb), Cms::thumbUrl($path));
        $this->assertSame(Storage::disk('public')->url($path), Cms::largeUrl($path));
    }

    public function test_small_upload_has_no_thumbnail_and_thumb_url_falls_back_to_original(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP support is not enabled in this PHP runtime.');
        }

        Storage::fake('public');
        $user = User::factory()->create();

        $path = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('small.png', 400, 300),
        ])->assertOk()->json('path');

        Storage::disk('public')->assertMissing(Cms::thumbnailPath($path));
        $this->assertSame(Storage::disk('public')->url($path), Cms::thumbUrl($path));
    }

    public function test_unsplash_placeholders_are_resized_instead_of_cropped(): void
    {
        $src = 'https://images.unsplash.com/photo-123?w=600&h=600&q=80&auto=format&fit=crop';

        $this->assertSame('https://images.unsplash.com/photo-123?w=720&q=80&auto=format', Cms::thumbUrl($src));
        $this->assertSame('https://images.unsplash.com/photo-123?w=1600&q=80&auto=format', Cms::largeUrl($src));
    }

    public function test_upload_rejects_images_over_two_megabytes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->create('banner.png', 2049, 'image/png'),
        ])->assertUnprocessable();

        $this->assertSame(['validation.max.file'], $response->json('errors.image'));
    }

    public function test_upload_rejects_images_with_excessive_pixel_dimensions_before_decoding(): void
    {
        if (! function_exists('getimagesize')) {
            $this->markTestSkipped('PHP image metadata support is not enabled in this runtime.');
        }

        $path = tempnam(sys_get_temp_dir(), 'cms-image-');
        $pngHeader = "\x89PNG\r\n\x1a\n".pack('N', 13).'IHDR'.pack('NNCCCCC', 4000, 4000, 8, 2, 0, 0, 0).pack('N', 0);
        file_put_contents($path, $pngHeader);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('dimensions exceed');

            app(ImageUploadService::class)->store(
                new UploadedFile($path, 'oversized.png', 'image/png', null, true),
                'cms/test'
            );
        } finally {
            @unlink($path);
        }
    }
}
