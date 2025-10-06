<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MartAnswer extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_answers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'entry_id',
        'question_uuid',
        'question_version',
        'answer_value',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'question_version' => 'integer',
    ];

    /**
     * Get the entry that owns this answer.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(MartEntry::class, 'entry_id');
    }

    /**
     * Get the question this answer is for.
     * Note: This references UUID, not a traditional foreign key.
     */
    public function question(): ?MartQuestion
    {
        return MartQuestion::find($this->question_uuid);
    }

    /**
     * Get the decoded answer value.
     * Attempts to decode JSON, returns raw value if not JSON.
     */
    public function getDecodedAnswerAttribute()
    {
        $decoded = json_decode($this->answer_value, true);
        return $decoded !== null ? $decoded : $this->answer_value;
    }
}