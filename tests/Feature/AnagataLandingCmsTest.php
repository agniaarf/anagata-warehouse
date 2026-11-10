<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnagataLandingCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_can_be_edited_and_is_passed_to_the_new_landing(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('admin.content.items.update', ['anagata_site', 'story', 'story_1']), [
            'name' => 'Chapter 1', 'label' => 'A new beginning', 'text' => 'Cerita dari CMS',
            'image' => '/anagata-images/packing.jpg', 'position' => 'left',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('Cerita dari CMS')->assertSee('A new beginning');
        $this->assertSame(24, Content::where('section', 'anagata_site')->where('item_key', 'like', 'photo_%')->count());
    }

    public function test_video_id_rejects_untrusted_urls_and_preview_requires_login(): void
    {
        $this->get('/preview')->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('admin.content.items.update', ['anagata_site', 'section', 'section_4']), [
            'name' => 'Video cerita', 'headline' => 'Judul', 'youtube_id' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('youtube_id');
        $this->get('/preview')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('noindex, nofollow')->assertSee('window.__ANAGATA_CONTENT__', false);
    }

    public function test_collage_slot_can_store_a_valid_youtube_id_and_reject_invalid_input(): void
    {
        $user = User::factory()->create();
        $url = route('admin.content.items.update', ['anagata_site', 'photo', 'photo_1']);
        $photo = ['name' => 'Packing', 'image' => '/anagata-images/packing.jpg', 'youtube_id' => 'https://www.youtube.com/watch?v=TprgJuLRPFY'];
        $this->actingAs($user)->put($url, $photo)->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertSee('TprgJuLRPFY');
        $this->put($url, array_merge($photo, ['youtube_id' => 'javascript:alert(1)']))->assertSessionHasErrors('youtube_id');
        $saved = Content::where('section', 'anagata_site')->where('item_key', 'photo_1')->firstOrFail()->decoded_value;
        $this->assertSame('TprgJuLRPFY', $saved['youtube_id']);
    }

    public function test_new_landing_enables_analytics_for_visitors_and_disables_it_for_admins(): void
    {
        $this->get('/')->assertOk()->assertSee('data-analytics-enabled="true"', false)->assertSee('anagata-analytics.js');
        $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertSee('data-analytics-enabled="false"', false);
    }

    public function test_public_landing_can_stay_on_the_legacy_design_until_switched(): void
    {
        $this->seed(CmsContentSeeder::class);
        config(['landing.anagata_enabled' => false]);
        $this->get('/')->assertOk()->assertViewIs('landing');
        $this->actingAs(User::factory()->create())->get('/preview')->assertOk()->assertViewIs('anagata-landing');
    }

    public function test_preview_payload_escapes_admin_text(): void
    {
        $item = Content::where('section', 'anagata_site')->where('item_key', 'story_1')->firstOrFail();
        $value = $item->decoded_value;
        $value['text'] = '</script><script>alert(1)</script>';
        Content::putItem('anagata_site', 'story_1', $value, $item->order);
        $this->get('/')->assertOk()->assertDontSee('</script><script>alert(1)</script>', false)->assertSee('\u003C', false);
    }

    public function test_no_javascript_fallback_contains_cms_services_and_escapes_content(): void
    {
        $item = Content::where('section', 'anagata_site')->where('item_key', 'service_1')->firstOrFail();
        $value = $item->decoded_value;
        $value['body'] = '<script>alert(1)</script> layanan CMS';
        Content::putItem('anagata_site', 'service_1', $value, $item->order);

        $response = $this->get('/')->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('<noscript>', $html);
        $fallback = explode('</noscript>', explode('<noscript>', $html, 2)[1], 2)[0];
        $this->assertStringContainsString('layanan CMS', $fallback);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $fallback);
        $this->assertStringContainsString('Lokasi', $fallback);
    }

    public function test_existing_edited_content_and_image_paths_are_preserved_by_rename(): void
    {
        $item = Content::where('section', 'anagata_site')->where('item_key', 'story_1')->firstOrFail();
        $value = $item->decoded_value;
        $value['text'] = 'Cerita yang sudah diedit';
        $value['image'] = '/fig'.'ma-images/packing.jpg';
        $item->delete();
        Content::putItem('fig'.'ma_site', 'story_1', $value, 1);
        $migration = require database_path('migrations/2026_11_02_000002_rename_anagata_content.php');
        $migration->up();
        $saved = Content::where('section', 'anagata_site')->where('item_key', 'story_1')->firstOrFail()->decoded_value;
        $this->assertSame('Cerita yang sudah diedit', $saved['text']);
        $this->assertSame('/anagata-images/packing.jpg', $saved['image']);
    }

    public function test_rename_refuses_to_overwrite_conflicting_editor_content(): void
    {
        Content::putItem('fig'.'ma_site', 'story_1', ['text' => 'Tulisan lama'], 1);
        $migration = require database_path('migrations/2026_11_02_000002_rename_anagata_content.php');
        try {
            $migration->up();
            $this->fail('Conflicting content must not be overwritten.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CMS content conflict', $e->getMessage());
        }
        $this->assertSame('Tulisan lama', Content::where('section', 'fig'.'ma_site')->where('item_key', 'story_1')->firstOrFail()->decoded_value['text']);
    }
}
