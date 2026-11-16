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
 * - 'display': Display text only (no answer, itemId is null in API). With
 *   config.displaySelection ('radio' or 'checkbox') it also shows a choice's
 *   options, disabled: it gets that choice's scale, and config holds options
 *   like a choice. Otherwise its scaleId is null too.
 * - 'number': Numeric input field (sends type='number' with minValue/maxValue to mobile)
 * - 'range': Range slider (sends type='range' with rangeOptions to mobile)
 * - 'range values': Slider with a label per step (sends type='rangeValues'
 *   with rangeValueOptions to mobile; config holds options like a choice)
 * - 'text': Single-line text input field
 * - 'textarea': Multi-line text input field
 * - 'one choice': Single selection (radio buttons)
 * - 'multiple choice': Multiple selection (checkboxes)
 * - 'photo' / 'audio' / 'video': Uploads (sent as photoUpload / audioUpload /
 *   videoUpload with their *UploadOptions)
 * - 'file': Any file (sent as fileUpload; config holds fileUploadOptions
 *   {maxFileSize, allowedFormats})
 *
 * Item Options (stored inside `config`, emitted by QuestionSheetResource as
 * the item's `options` block per martTypes.ts):
 * - 'randomizationGroupId': Integer grouping questions for randomized presentation
 * - 'randomizeAnswers': Boolean to randomize answer options (radio/checkbox)
 * - 'itemGroup': String grouping questions to display together on same page
 * - 'randomPositionWithinQuestionnaire': Boolean, randomises the item's position
 * - 'posterUrl': String, still image shown before a video plays
 *
 * 'noValueAllowed' is not stored in config. It is the inverse of the
 * `is_mandatory` column, and the resource derives it on the way out.
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
     * How a display item can show answer options, disabled: as the scale of
     * this choice type. The app greys out the options of any item without
     * an itemId, and sends no answer for it.
     */
    public const DISPLAY_SELECTIONS = [
        'radio' => 'one choice',
        'checkbox' => 'multiple choice',
    ];

    /**
     * The choice type a display item's options are shown as, or null when
     * its config has no displaySelection we know.
     */
    public static function displaySelectionType($config): ?string
    {
        $selection = is_array($config) ? ($config['displaySelection'] ?? null) : null;

        return is_string($selection) ? (self::DISPLAY_SELECTIONS[$selection] ?? null) : null;
    }

    /**
     * The question as plain text, for pages and exports: the editor can
     * store HTML such as <br>, which becomes a space.
     */
    public function plainText(): string
    {
        $spaced = preg_replace('/<br\s*\/?>/i', ' ', (string) $this->text);
        $text = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * What the "other" option of a choice is called, as the app shows it.
     */
    public function otherOptionLabel(): string
    {
        $label = $this->config['otherOptionLabel'] ?? null;

        return ! empty($label) ? (string) $label : 'Other';
    }

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
        // A URL sent as null clears it (the editor sends null for a cleared
        // field); a URL left out is kept.
        if (array_key_exists('image_url', $newData)) {
            $this->image_url = $newData['image_url'];
        }
        if (array_key_exists('video_url', $newData)) {
            $this->video_url = $newData['video_url'];
        }
        $this->type = $newData['type'] ?? $this->type;
        $this->config = $newData['config'] ?? $this->config;
        $this->is_mandatory = $newData['is_mandatory'] ?? $this->is_mandatory;

        return $this->save();
    }

}