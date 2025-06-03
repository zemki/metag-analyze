<?php

namespace App\Models;

use App\Cases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'case_id',
        'android_usage_stats',
        'android_event_stats',
        'ios_activations',
        'ios_screen_time',
        'timestamp',
        'timezone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'android_usage_stats' => 'array',
        'android_event_stats' => 'array',
        'timestamp' => 'integer',
    ];

    /**
     * Get the case that owns the stats.
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }
}
