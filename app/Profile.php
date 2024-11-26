<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Profile was meant to extended the User model, but it was never used, beside for the device_id used for notifications, that's why we keep it.
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
