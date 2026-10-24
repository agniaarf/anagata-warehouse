<?php

namespace Tests\Feature\Admin;

use App\Models\AnalyticsSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_admin_authentication(): void
    {
        $this->get(route('admin.analytics'))->assertRedirect(route('login'));
    }

    public function test_admin_sees_period_metrics_chart_and_session_activity(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-17 12:00:00', 'Asia/Jakarta'));
        $visitorId = (string) Str::uuid();
        $first = $this->createSession($visitorId, '2026-09-16 08:15:00', 'instagram.com');
        $first->events()->create([
            'event_key' => (string) Str::uuid(),
            'event_type' => 'section_view',
            'target' => 'location',
            'created_at' => Carbon::parse('2026-09-16 08:16:00', 'Asia/Jakarta')->utc(),
        ]);
        $first->events()->create([
            'event_key' => (string) Str::uuid(),
            'event_type' => 'contact_click',
            'target' => 'whatsapp',
            'created_at' => Carbon::parse('2026-09-16 08:17:00', 'Asia/Jakarta')->utc(),
        ]);
        $this->createSession($visitorId, '2026-09-17 09:00:00', 'instagram.com');
        $this->createSession($visitorId, '2026-09-11 00:00:00', 'instagram.com');
        $this->createSession((string) Str::uuid(), '2026-09-10 23:30:00', 'google.com');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.analytics', ['days' => 7]))
            ->assertOk()
            ->assertSee('Analitik Pengunjung')
            ->assertSee('Pertumbuhan pengunjung')
            ->assertSee('Aktivitas sesi')
            ->assertSee('instagram.com')
            ->assertSee('Lokasi gudang')
            ->assertSee('WhatsApp')
            ->assertSee('/bojong/analytics');

        $response->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics['visitors']['value'] === 1
                && $metrics['visitors']['change'] === 0
                && $metrics['sessions']['value'] === 3
                && $metrics['sessions']['change'] === 200
                && $metrics['contacts']['value'] === 1
                && $metrics['contacts']['change'] === null;
        });

        $response->assertViewHas('series', function ($series): bool {
            return $series->count() === 7
                && $series->first()['date'] === '2026-09-11'
                && $series->last()['date'] === '2026-09-17'
                && $series->where('date', '2026-09-11')->first()['visitors'] === 1
                && $series->where('date', '2026-09-16')->first()['visitors'] === 1
                && $series->where('date', '2026-09-17')->first()['visitors'] === 1;
        });

        Carbon::setTestNow();
    }

    public function test_unsupported_period_falls_back_to_thirty_days(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.analytics', ['days' => 'all']))
            ->assertOk()
            ->assertViewHas('days', 30);
    }

    private function createSession(string $visitorId, string $localTime, ?string $referrer = null): AnalyticsSession
    {
        $startedAt = Carbon::parse($localTime, 'Asia/Jakarta')->utc();

        return AnalyticsSession::create([
            'session_key' => (string) Str::uuid(),
            'visitor_key' => $visitorId,
            'started_at' => $startedAt,
            'last_activity_at' => $startedAt,
            'landing_path' => '/',
            'referrer_domain' => $referrer,
            'device_type' => 'mobile',
        ]);
    }
}
