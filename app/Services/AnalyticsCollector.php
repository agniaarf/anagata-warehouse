<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnalyticsCollector
{
    public function isBot(?string $userAgent): bool
    {
        return (bool) preg_match(
            '/bot|crawler|spider|slurp|preview|facebookexternalhit|bingpreview|headless|lighthouse/i',
            substr($userAgent ?? '', 0, 500)
        );
    }

    public function deviceType(?string $userAgent): string
    {
        $agent = substr($userAgent ?? '', 0, 500);

        if (preg_match('/ipad|tablet|kindle|silk|playbook/i', $agent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|iphone|ipod|android/i', $agent)) {
            return 'mobile';
        }

        return $agent === '' ? 'unknown' : 'desktop';
    }

    /** @param array<string, mixed> $data */
    public function collect(array $data, ?string $userAgent): void
    {
        $this->validateTarget($data['type'], $data['target'] ?? null);

        DB::transaction(function () use ($data, $userAgent): void {
            $now = Carbon::now('UTC');
            $session = AnalyticsSession::query()->where('session_key', $data['session_id'])->first();

            if ($data['type'] === 'session_start') {
                if ($session) {
                    $this->assertVisitorMatches($session, $data['visitor_id']);

                    // Session-start requests are idempotent; a stale tab must
                    // create a fresh UUID in the browser instead of reviving it.
                    return;
                }

                AnalyticsSession::create([
                    'session_key' => $data['session_id'],
                    'visitor_key' => $data['visitor_id'],
                    'started_at' => $now,
                    'last_activity_at' => $now,
                    'landing_path' => $this->cleanPath($data['path'] ?? '/'),
                    'referrer_domain' => $this->cleanHost($data['referrer_host'] ?? null),
                    'utm_source' => $this->cleanCampaign($data['utm_source'] ?? null),
                    'utm_medium' => $this->cleanCampaign($data['utm_medium'] ?? null),
                    'utm_campaign' => $this->cleanCampaign($data['utm_campaign'] ?? null),
                    'device_type' => $this->deviceType($userAgent),
                ]);

                return;
            }

            if (! $session) {
                throw ValidationException::withMessages(['session_id' => 'Sesi analytics tidak ditemukan.']);
            }

            $this->assertVisitorMatches($session, $data['visitor_id']);

            if ($session->last_activity_at->lt($now->copy()->subMinutes(config('analytics.session_timeout_minutes', 30)))) {
                throw ValidationException::withMessages(['session_id' => 'Sesi analytics sudah berakhir.']);
            }

            $session->last_activity_at = $now;
            $session->save();

            AnalyticsEvent::firstOrCreate(
                ['event_key' => $data['event_id']],
                [
                    'analytics_session_id' => $session->id,
                    'event_type' => $data['type'],
                    'target' => $data['target'],
                    'created_at' => $now,
                ]
            );
        });
    }

    private function validateTarget(string $type, ?string $target): void
    {
        if ($type === 'session_start') {
            return;
        }

        $allowed = $type === 'section_view'
            ? config('analytics.sections', [])
            : config('analytics.contact_channels', []);

        if (! $target || ! in_array($target, $allowed, true)) {
            throw ValidationException::withMessages(['target' => 'Aktivitas analytics tidak valid.']);
        }
    }

    private function assertVisitorMatches(AnalyticsSession $session, string $visitorId): void
    {
        if (! hash_equals($session->visitor_key, $visitorId)) {
            throw ValidationException::withMessages(['visitor_id' => 'ID pengunjung tidak sesuai dengan sesi.']);
        }
    }

    private function cleanPath(string $path): string
    {
        $clean = parse_url($path, PHP_URL_PATH);

        if (! is_string($clean) || ! str_starts_with($clean, '/') || str_starts_with($clean, '//')) {
            return '/';
        }

        return substr($clean, 0, 255) ?: '/';
    }

    private function cleanHost(?string $host): ?string
    {
        if (! $host) {
            return null;
        }

        $host = strtolower(trim($host));

        return preg_match('/\A(?=.{1,253}\z)[a-z0-9.-]+\z/i', $host) ? $host : null;
    }

    private function cleanCampaign(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(strip_tags($value));
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '';

        return $value === '' ? null : mb_substr($value, 0, 100);
    }
}
