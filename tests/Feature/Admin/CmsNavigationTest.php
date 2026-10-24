<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CmsNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_url_opens_the_homepage_editor(): void
    {
        $this->seed(CmsContentSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/bojong/dashboard')
            ->assertRedirect('/bojong/website');

        $this->actingAs($user)
            ->get('/bojong/website')
            ->assertOk()
            ->assertSee('Halaman utama')
            ->assertSee('Banner utama')
            ->assertDontSee('Dashboard');
    }

    public function test_content_page_includes_the_google_services_list(): void
    {
        $this->seed(CmsContentSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.content.index'))
            ->assertOk()
            ->assertSee('Layanan untuk Google')
            ->assertSee('Pencarian Google');

        $this->actingAs($user)
            ->get(route('admin.content.show', ['seo', 'offer']))
            ->assertOk()
            ->assertSee('Inbound & Penerimaan Barang');
    }

    public function test_content_items_have_icon_order_controls_outside_the_edit_panel(): void
    {
        $this->seed(CmsContentSeeder::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.content.show', ['hero', 'slide']))
            ->assertOk()
            ->assertSee('Kembali')
            ->assertSee('class="btn btn-admin-ghost btn-sm">Kembali</a>', false)
            ->assertSee('Atur posisi dengan ikon di samping item.')
            ->assertSee('Pindahkan item ke atas')
            ->assertSee('Pindahkan item ke bawah')
            ->assertDontSee('content-list-chevron')
            ->assertDontSee('>Naik<')
            ->assertDontSee('>Turun<');

        $html = $response->getContent();
        $firstItemEnd = strpos($html, '</details>');
        $orderControls = strpos($html, 'aria-label="Atur urutan item"');

        $this->assertNotFalse($firstItemEnd);
        $this->assertNotFalse($orderControls);
        $this->assertGreaterThan($firstItemEnd, $orderControls);
    }

    public function test_upload_rejects_webp_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('admin.uploads.store'), [
                'image' => UploadedFile::fake()->createWithContent('banner.webp', 'webp'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }
}
