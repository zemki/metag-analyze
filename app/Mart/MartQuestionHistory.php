<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MartQuestionHistory extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_question_history';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'question_uuid',
        'version',
        'text',
        'type',
        'config',
        'is_mandatory',
        'changed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'config' => 'array',
        'is_mandatory' => 'boolean',
        'version' => 'integer',
        'changed_at' => 'datetime',
    ];

    /**
     * Get the question this history belongs to.
     * Note: This references UUID, not a traditional foreign key relationship.
     */
    public function question(): ?MartQuestion
    {
        return MartQuestion::find($this->question_uuid);
    }
}