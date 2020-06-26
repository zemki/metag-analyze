<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    protected $table = 'entries';
    protected $guarded = [];

    /**
     * Cases is intended to be CASE
     * but CASE is a reserved keyword in most programming languages
     * @return BelongsTo
     */
    public function cases()
    {
        return $this->belongsTo(Cases::class,'cases_id','id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
