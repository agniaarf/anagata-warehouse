<?php

namespace App\Console\Commands;

use App\Models\AnalyticsSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneAnalytics extends Command
{
    protected $signature = 'analytics:prune';

    protected $description = 'Hapus sesi analytics yang sudah melewati masa retensi.';

    public function handle(): int
    {
        $cutoff = Carbon::now('UTC')->subDays(config('analytics.retention_days', 90));
        $deleted = AnalyticsSession::query()->where('started_at', '<', $cutoff)->delete();

        $this->info("{$deleted} sesi analytics dihapus.");

        return self::SUCCESS;
    }
}
