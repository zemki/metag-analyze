<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use HasFactory;

    protected $table = 'entries';

    protected $guarded = [];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Handle setting entity_id -> media_id for V2 API
        static::saving(function ($model) {
            if (isset($model->attributes['entity_id'])) {
                $model->attributes['media_id'] = $model->attributes['entity_id'];
                unset($model->attributes['entity_id']);
            }
        });
    }

    /**
     * @return BelongsTo
     */
    public function cases()
    {
        return $this->belongsTo(Cases::class, 'cases_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Entity relationship (same as media but with V2 naming)
     * Uses media_id column for database compatibility
     *
     * @return BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
