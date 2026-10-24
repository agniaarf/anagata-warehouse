<?php

namespace Tests\Feature\Analytics;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_start_persists_only_minimal_anonymous_metadata(): void
    {
        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile',
            'X-Forwarded-For' => '203.0.113.18',
        ])->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'session_start', null, [
            'path' => '/?email=private@example.com',
            'referrer_host' => 'instagram.com',
            'utm_source' => 'ig-bio',
            'utm_medium' => 'social',
        ]))->assertNoContent();

        $session = AnalyticsSession::firstOrFail();
        $this->assertSame($visitorId, $session->visitor_key);
        $this->assertSame('/', $session->landing_path);
        $this->assertSame('instagram.com', $session->referrer_domain);
        $this->assertSame('mobile', $session->device_type);
        $this->assertSame('ig-bio', $session->utm_source);
        $this->assertSame('social', $session->utm_medium);
        $this->assertFalse(Schema::hasColumn('analytics_sessions', 'ip_address'));
        $this->assertFalse(Schema::hasColumn('analytics_sessions', 'user_agent'));
        $this->assertStringNotContainsString('private@example.com', json_encode($session->getAttributes()));
    }

    public function test_section_and_contact_events_are_allowlisted_and_idempotent(): void
    {
        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();
        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'session_start'))->assertNoContent();

        $sectionEventId = (string) Str::uuid();
        $sectionPayload = $this->payload($visitorId, $sessionId, 'section_view', 'hero', ['event_id' => $sectionEventId]);
        $this->postJson(route('analytics.collect'), $sectionPayload)->assertNoContent();
        $this->postJson(route('analytics.collect'), $sectionPayload)->assertNoContent();
        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'contact_click', 'whatsapp'))
            ->assertNoContent();

        $this->assertDatabaseCount('analytics_events', 2);
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'section_view', 'target' => 'hero']);
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'contact_click', 'target' => 'whatsapp']);
    }

    public function test_invalid_event_targets_are_rejected(): void
    {
        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();
        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'session_start'))->assertNoContent();

        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'contact_click', 'email-body'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target');

        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'section_view', 'not-a-section'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target');
    }

    public function test_non_scalar_event_fields_are_rejected_as_validation_errors(): void
    {
        $payload = $this->payload((string) Str::uuid(), (string) Str::uuid(), 'session_start');
        $payload['utm_campaign'] = ['unexpected', 'array'];

        $this->postJson(route('analytics.collect'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('utm_campaign');
    }

    public function test_tracking_ignores_authenticated_admins_and_known_bots(): void
    {
        $payload = $this->payload((string) Str::uuid(), (string) Str::uuid(), 'session_start');

        $this->actingAs(User::factory()->create())
            ->postJson(route('analytics.collect'), $payload)
            ->assertNoContent();
        $this->assertDatabaseCount('analytics_sessions', 0);
        auth()->logout();

        $this->withHeader('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)')
            ->postJson(route('analytics.collect'), $payload)
            ->assertNoContent();
        $this->assertDatabaseCount('analytics_sessions', 0);
    }

    public function test_landing_page_configures_tracking_for_visitors_but_disables_it_for_admins(): void
    {
        $this->seed(CmsContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-analytics-enabled="true"', false)
            ->assertSee('data-analytics-endpoint="/analytics/collect"', false)
            ->assertSee('data-analytics-session-timeout="30"', false)
            ->assertSee('data-analytics-visitor-days="90"', false)
            ->assertSee('data-analytics-section="hero"', false)
            ->assertSee('data-analytics-contact="whatsapp"', false)
            ->assertSee('analytics-disclosure');

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('data-analytics-enabled="false"', false);
    }

    public function test_events_from_a_session_idle_for_more_than_thirty_minutes_are_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-17 05:00:00', 'UTC'));
        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();
        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'session_start'))->assertNoContent();

        Carbon::setTestNow(Carbon::parse('2026-09-17 05:31:00', 'UTC'));
        $this->postJson(route('analytics.collect'), $this->payload($visitorId, $sessionId, 'section_view', 'about'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('session_id');
        $this->assertDatabaseCount('analytics_events', 0);

        Carbon::setTestNow();
    }

    public function test_collection_endpoint_is_rate_limited(): void
    {
        $payload = $this->payload((string) Str::uuid(), (string) Str::uuid(), 'session_start');

        for ($request = 0; $request < 60; $request++) {
            $this->postJson(route('analytics.collect'), $payload)->assertNoContent();
        }

        $this->postJson(route('analytics.collect'), $payload)->assertTooManyRequests();
    }

    public function test_prune_command_deletes_sessions_and_cascades_events_older_than_ninety_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $expired = $this->createSession(Carbon::now()->subDays(91));
        $recent = $this->createSession(Carbon::now()->subDays(89));
        $expired->events()->create([
            'event_key' => (string) Str::uuid(),
            'event_type' => 'contact_click',
            'target' => 'whatsapp',
            'created_at' => Carbon::now()->subDays(91),
        ]);

        $this->artisan('analytics:prune')->assertExitCode(0);

        $this->assertDatabaseMissing('analytics_sessions', ['id' => $expired->id]);
        $this->assertDatabaseHas('analytics_sessions', ['id' => $recent->id]);
        $this->assertDatabaseCount('analytics_events', 0);
        Carbon::setTestNow();
    }

    private function payload(string $visitorId, string $sessionId, string $type, ?string $target = null, array $overrides = []): array
    {
        return array_merge([
            'event_id' => (string) Str::uuid(),
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'type' => $type,
            'target' => $target,
            'path' => '/',
        ], $overrides);
    }

    private function createSession(Carbon $startedAt): AnalyticsSession
    {
        return AnalyticsSession::create([
            'session_key' => (string) Str::uuid(),
            'visitor_key' => (string) Str::uuid(),
            'started_at' => $startedAt,
            'last_activity_at' => $startedAt,
            'landing_path' => '/',
            'device_type' => 'desktop',
        ]);
    }
}
