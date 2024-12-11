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
}
