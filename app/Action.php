<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Action
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $url
 * @property int|null $user_id
 * @property string $time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Action onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Action withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Action withoutTrashed()
 * @mixin \Eloquent
 */
class Action extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'url', 'name', 'description',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
