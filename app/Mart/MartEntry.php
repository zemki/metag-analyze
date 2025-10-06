<?php

namespace App\Mart;

use App\Entry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MartEntry extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_entries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'main_entry_id',
        'schedule_id',
        'questionnaire_id',
        'participant_id',
        'user_id',
        'started_at',
        'completed_at',
        'duration_ms',
        'timezone',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_ms' => 'integer',
        'timestamp' => 'integer',
    ];

    /**
     * Get the main entry from the main database.
     * Note: This is a cross-database query, not a true Eloquent relationship.
     */
    public function mainEntry(): ?Entry
    {
        return Entry::find($this->main_entry_id);
    }

    /**
     * Get the schedule that this entry belongs to.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MartSchedule::class, 'schedule_id');
    }

    /**
     * Get the answers for this entry.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(MartAnswer::class, 'entry_id');
    }

    /**
     * Scope to get entries for a specific participant.
     */
    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participant_id', $participantId);
    }

    /**
     * Scope to get entries for a specific questionnaire.
     */
    public function scopeForQuestionnaire($query, $questionnaireId)
    {
        return $query->where('questionnaire_id', $questionnaireId);
    }
}