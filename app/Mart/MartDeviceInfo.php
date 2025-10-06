<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;

class MartDeviceInfo extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_device_info';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'participant_id',
        'user_id',
        'os',
        'os_version',
        'model',
        'manufacturer',
        'last_updated',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_updated' => 'datetime',
    ];

    /**
     * Scope to get device info for a specific participant.
     */
    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participant_id', $participantId);
    }

    /**
     * Scope to get device info for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}