<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * MartQuestion Model
 *
 * Represents individual questions within MART questionnaires.
 *
 * Question Types:
 * - 'display': Display text only (no answer required, itemId/scaleId are null in API)
 * - 'number': Numeric input field (sends type='number' with minValue/maxValue to mobile)
 * - 'range': Range slider (sends type='range' with rangeOptions to mobile)
 * - 'text': Single-line text input field
 * - 'textarea': Multi-line text input field
 * - 'one choice': Single selection (radio buttons)
 * - 'multiple choice': Multiple selection (checkboxes)
 *
 * Question Options (sent via API, stored at question level):
 * - 'randomizationGroupId': Integer grouping questions for randomized presentation
 * - 'randomizeAnswers': Boolean to randomize answer options (radio/checkbox)
 * - 'itemGroup': String grouping questions to display together on same page
 * - 'noValueAllowed': Boolean to allow skipping the question (inverse of is_mandatory)
 *
 * Note: 'number' and 'range' both store similar config (min, max, step) but are
 * rendered differently on mobile and in the API response format per martTypes.ts.
 */
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
        'image_url',
        'video_url',
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
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'type' => $this->type,
            'config' => $this->config,
            'is_mandatory' => $this->is_mandatory,
            'changed_at' => now(),
        ]);

        // Increment version
        $this->version++;

        // Update question with new data
        $this->text = $newData['text'] ?? $this->text;
        $this->image_url = $newData['image_url'] ?? $this->image_url;
        $this->video_url = $newData['video_url'] ?? $this->video_url;
        $this->type = $newData['type'] ?? $this->type;
        $this->config = $newData['config'] ?? $this->config;
        $this->is_mandatory = $newData['is_mandatory'] ?? $this->is_mandatory;

        return $this->save();
    }

}