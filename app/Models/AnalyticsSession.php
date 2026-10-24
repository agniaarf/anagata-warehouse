<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsSession extends Model
{
    protected $fillable = [
        'session_key',
        'visitor_key',
        'started_at',
        'last_activity_at',
        'landing_path',
        'referrer_domain',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'device_type',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }
}
