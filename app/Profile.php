<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Profile
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $address
 * @property string|null $birthday
 * @property string|null $phonenumber1
 * @property string|null $phonenumber2
 * @property string|null $workaddress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $newsletter
 * @property-read \App\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereNewsletter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile wherePhonenumber1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile wherePhonenumber2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Profile whereWorkaddress($value)
 *
 * @mixin \Eloquent
 *
 * @property string|null $last_notification_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereLastNotificationAt($value)
 */
class Profile extends Model
{
    protected $table = 'users_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'address', 'workaddress', 'phonenumber1', 'phonenumber2', 'newsletter',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
