<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = (string) $request->query('days', '30');
        $days = in_array($period, ['7', '30', '90'], true) ? (int) $period : 30;
        $timezone = config('analytics.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->startOfDay();
        $from = $today->copy()->subDays($days - 1);
        $to = $today->copy()->endOfDay();
        $previousTo = $from->copy()->subSecond();
        $previousFrom = $previousTo->copy()->startOfDay()->subDays($days - 1);

        $currentStats = $this->statsFor($from, $to);
        $previousStats = $this->statsFor($previousFrom, $previousTo);
        $series = $this->dailyVisitors($from, $to, $days, $timezone);

        $sessions = AnalyticsSession::query()
            ->whereBetween('started_at', [$from->copy()->utc(), $to->copy()->utc()])
            ->with(['events' => fn ($query) => $query
                ->whereIn('event_type', ['section_view', 'contact_click'])
                ->orderBy('created_at')])
            ->orderByDesc('started_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.analytics.index', [
            'days' => $days,
            'timezone' => $timezone,
            'from' => $from,
            'to' => $today,
            'metrics' => [
                'visitors' => $this->metric($currentStats['visitors'], $previousStats['visitors']),
                'sessions' => $this->metric($currentStats['sessions'], $previousStats['sessions']),
                'contacts' => $this->metric($currentStats['contacts'], $previousStats['contacts']),
            ],
            'series' => $series,
            'chartPoints' => $this->chartPoints($series),
            'chartMax' => max(1, (int) $series->max('visitors')),
            'sessionsList' => $sessions,
        ]);
    }

    private function statsFor(Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->utc();
        $end = $to->copy()->utc();
        $base = AnalyticsSession::query()->whereBetween('started_at', [$start, $end]);

        return [
            'visitors' => (clone $base)->distinct()->count('visitor_key'),
            'sessions' => (clone $base)->count(),
            'contacts' => AnalyticsEvent::query()
                ->where('event_type', 'contact_click')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    private function dailyVisitors(Carbon $from, Carbon $to, int $days, string $timezone): Collection
    {
        $rows = AnalyticsSession::query()
            ->whereBetween('started_at', [$from->copy()->utc(), $to->copy()->utc()])
            ->get(['visitor_key', 'started_at']);
        $daily = [];

        foreach ($rows as $row) {
            $date = $row->started_at->copy()->timezone($timezone)->toDateString();
            $daily[$date][$row->visitor_key] = true;
        }

        return collect(range(0, $days - 1))->map(function (int $offset) use ($from, $daily): array {
            $date = $from->copy()->addDays($offset);
            $key = $date->toDateString();

            return [
                'date' => $key,
                'label' => $date->format('d/m'),
                'visitors' => isset($daily[$key]) ? count($daily[$key]) : 0,
            ];
        });
    }

    private function chartPoints(Collection $series): string
    {
        $max = max(1, (int) $series->max('visitors'));
        $lastIndex = max(1, $series->count() - 1);

        return $series->values()->map(function (array $day, int $index) use ($max, $lastIndex): string {
            $x = 40 + (920 * $index / $lastIndex);
            $y = 218 - (180 * $day['visitors'] / $max);

            return round($x, 1).','.round($y, 1);
        })->implode(' ');
    }

    private function metric(int $current, int $previous): array
    {
        return [
            'value' => $current,
            'change' => $previous === 0
                ? ($current === 0 ? 0 : null)
                : (int) round((($current - $previous) / $previous) * 100),
        ];
    }
}
