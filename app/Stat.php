<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stat extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId',
        'projectId',
        'participantId',
        'timestamp',
        'timezone',
        'iosScreenTime',
        'iosActivations',
        'androidUsageStats',
        'androidEventStats',
        'iosStats',
        'deviceID',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'androidUsageStats' => 'array',
        'androidEventStats' => 'array',
        'iosStats' => 'array',
        'timestamp' => 'integer',
        'iosScreenTime' => 'integer',
        'iosActivations' => 'integer',
    ];

    /**
     * Get the project that owns the stat.
     *
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId');
    }

    /**
     * Get the user that owns the stat.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'email');
    }

    /**
     * Get the case associated with the stat.
     *
     * @return BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'participantId', 'name');
    }

    /**
     * Scope a query to only include stats for a specific project.
     *
     * @param  Builder  $query
     * @param  int  $projectId
     * @return Builder
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('projectId', $projectId);
    }

    /**
     * Scope a query to only include stats for a specific participant.
     *
     * @param  Builder  $query
     * @param  string  $participantId
     * @return Builder
     */
    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participantId', $participantId);
    }

    /**
     * Get stats within a date range.
     *
     * @param  Builder  $query
     * @param  int  $startTimestamp
     * @param  int  $endTimestamp
     * @return Builder
     */
    public function scopeInDateRange($query, $startTimestamp, $endTimestamp)
    {
        return $query->whereBetween('timestamp', [$startTimestamp, $endTimestamp]);
    }
}
