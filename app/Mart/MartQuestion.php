<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MartQuestion extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_questions';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'uuid';

    /**
     * The "type" of the primary key.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'schedule_id',
        'position',
        'text',
        'type',
        'config',
        'is_mandatory',
        'version',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'config' => 'array',
        'is_mandatory' => 'boolean',
        'version' => 'integer',
    ];

    /**
     * Boot function to auto-generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the schedule that owns this question.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MartSchedule::class, 'schedule_id');
    }

    /**
     * Get the history entries for this question.
     */
    public function history(): HasMany
    {
        return $this->hasMany(MartQuestionHistory::class, 'question_uuid', 'uuid')->orderBy('version', 'desc');
    }

    /**
     * Get the answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(MartAnswer::class, 'question_uuid', 'uuid');
    }

    /**
     * Update the question and save the old version to history.
     * This automatically increments the version number.
     *
     * @param array $newData Array of new question data (text, type, config, is_mandatory)
     * @return bool
     */
    public function updateQuestion(array $newData): bool
    {
        // Save current version to history
        MartQuestionHistory::create([
            'question_uuid' => $this->uuid,
            'version' => $this->version,
            'text' => $this->text,
            'type' => $this->type,
            'config' => $this->config,
            'is_mandatory' => $this->is_mandatory,
            'changed_at' => now(),
        ]);

        // Increment version
        $this->version++;

        // Update question with new data
        $this->text = $newData['text'] ?? $this->text;
        $this->type = $newData['type'] ?? $this->type;
        $this->config = $newData['config'] ?? $this->config;
        $this->is_mandatory = $newData['is_mandatory'] ?? $this->is_mandatory;

        return $this->save();
    }
}