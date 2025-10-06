<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MartStat extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_stats';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mart_project_id',
        'participant_id',
        'user_id',
        'android_usage_stats',
        'android_event_stats',
        'ios_activations',
        'ios_screen_time',
        'ios_stats',
        'device_id',
        'timestamp',
        'timezone',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'android_usage_stats' => 'array',
        'android_event_stats' => 'array',
        'ios_stats' => 'array',
        'timestamp' => 'integer',
        'ios_activations' => 'integer',
        'ios_screen_time' => 'integer',
    ];

    /**
     * Get the MART project that owns the stat.
     */
    public function martProject(): BelongsTo
    {
        return $this->belongsTo(MartProject::class, 'mart_project_id');
    }

    /**
     * Scope to get stats for a specific MART project.
     */
    public function scopeForProject($query, $martProjectId)
    {
        return $query->where('mart_project_id', $martProjectId);
    }

    /**
     * Scope to get stats for a specific participant.
     */
    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participant_id', $participantId);
    }

    /**
     * Get stats within a date range.
     */
    public function scopeInDateRange($query, $startTimestamp, $endTimestamp)
    {
        return $query->whereBetween('timestamp', [$startTimestamp, $endTimestamp]);
    }
}