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

    public function test_editor_prioritizes_current_content_and_hides_irrelevant_section_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.content.index'))->assertOk()
            ->assertSee('Kolase foto &amp; video', false)
            ->assertSee('SEO &amp; Google', false)
            ->assertSee('Konten versi sebelumnya')
            ->assertDontSee('Konten desain baru');

        $this->get(route('admin.content.show', ['anagata_site', 'section']))->assertOk()
            ->assertSee('Nama utama')->assertSee('Nama kedua')->assertSee('Teks tombol');
    }

    public function test_seo_settings_are_visible_and_update_the_public_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.settings.show', 'seo'))->assertOk()->assertSee('Pencarian Google');
        $this->put(route('admin.settings.update', 'seo'), [
            'meta_title' => 'Judul SEO Uji', 'meta_description' => 'Deskripsi SEO Uji',
            'meta_keywords' => 'gudang', 'og_title' => 'Judul Berbagi',
            'og_description' => 'Deskripsi Berbagi', 'image_alt_suffix' => '',
        ])->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertSee('Judul SEO Uji')->assertSee('Deskripsi SEO Uji');
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
