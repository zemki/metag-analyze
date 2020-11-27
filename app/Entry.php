<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Entry
 *
 * @property int $id
 * @property string $begin
 * @property string $end
 * @property string|null $inputs
 * @property int $case_id
 * @property int $media_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Cases $cases
 * @property-read \App\Media $media
 * @property-read \App\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereBegin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereInputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entry whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $inputs_graph
 */
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
