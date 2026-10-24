<?php

namespace Tests\Feature\Security;

use App\Models\Content;
use App\Models\User;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_and_every_cms_view_render_with_expected_access_control(): void
    {
        $this->seed(CmsContentSeeder::class);
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->get('/')->assertOk()->assertSee('Anagata Warehouse');

        $adminPages = [
            route('admin.website.homepage'),
            route('admin.content.index'),
            route('admin.analytics'),
            route('admin.settings.show', 'branding'),
            route('admin.settings.show', 'contact'),
            route('admin.settings.show', 'seo'),
        ];

        foreach (array_keys(config('page_sections')) as $slug) {
            $adminPages[] = route('admin.website.section.edit', $slug);
        }

        foreach (content_collections() as $collection) {
            $adminPages[] = route('admin.content.show', [$collection['section'], $collection['group']]);
        }

        foreach ($adminPages as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
        $this->postJson(route('admin.uploads.store'))->assertUnauthorized();

        $admin = User::factory()->create();
        foreach ($adminPages as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_login_and_password_recovery_screens_render_without_account_enumeration(): void
    {
        Notification::fake();

        $this->get(route('login'))->assertOk();
        $this->get(route('password.request'))->assertOk();

        $admin = User::factory()->create();
        $message = 'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirim.';

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertRedirect()
            ->assertSessionHas('status', $message)
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($admin, ResetPassword::class);

        $this->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', $message)
            ->assertSessionHasNoErrors();

        $this->post(route('password.email'), ['email' => "admin@example.test\r\nBcc: attacker@example.test"])
            ->assertSessionHasErrors('email');
    }

    public function test_security_headers_and_admin_no_store_policy_are_present(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'");

        $this->actingAs(User::factory()->create())
            ->get(route('admin.website.homepage'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
    }

    public function test_host_headers_cannot_be_used_to_change_the_public_canonical_origin(): void
    {
        $this->seed(CmsContentSeeder::class);

        $this->get('http://attacker.invalid/')->assertNotFound();

        $this->get('/', ['X-Forwarded-Host' => 'attacker.invalid'])
            ->assertOk()
            ->assertDontSee('attacker.invalid', false);
    }

    public function test_cms_rich_text_escapes_active_markup_and_keeps_only_expected_formatting(): void
    {
        $this->seed(CmsContentSeeder::class);
        Content::putField('hero', 'headline', 'Safe <img src=x onerror=alert(1)> <em>format</em><br>next');

        $this->get('/')
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertSee('<em>format</em><br>next', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_unsafe_cta_urls_and_invalid_brand_colors_are_rejected_without_saving(): void
    {
        $this->seed(CmsContentSeeder::class);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.website.section.update', 'hero'), [
                'cta_primary_href' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('cta_primary_href');

        $this->assertSame('#contact', Content::where('section', 'hero')->where('item_key', 'cta_primary_href')->value('value'));

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'branding'), [
                'brand_color_primary' => '#fff;}</style><script>alert(1)</script>',
            ])
            ->assertSessionHasErrors('brand_color_primary');
    }

    public function test_cms_updates_accept_safe_copy_and_reject_unsafe_image_references(): void
    {
        $this->seed(CmsContentSeeder::class);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.website.section.update', 'hero'), [
                'headline' => 'Safe <em>headline</em>',
                'cta_primary_href' => '#contact',
            ])
            ->assertRedirect();

        $this->assertSame('#contact', Content::where('section', 'hero')->where('item_key', 'cta_primary_href')->value('value'));

        $this->actingAs($admin)
            ->post(route('admin.content.items.store', ['gallery', 'item']), [
                'src' => 'data:text/html,<script>alert(1)</script>',
                'caption' => 'Unsafe source',
            ])
            ->assertSessionHasErrors('src');

        $this->actingAs($admin)
            ->post(route('admin.content.items.store', ['gallery', 'item']), [
                'src' => 'https://images.example.test/audited.webp',
                'caption' => 'Safe source',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(13, Content::where('section', 'gallery')->where('type', Content::TYPE_ITEM)->count());
        $this->assertDatabaseHas('cms_contents', [
            'section' => 'gallery',
            'item_key' => 'item_13',
        ]);
    }
}
