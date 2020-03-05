<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'properties', 'media_group_id'
    ];

    public function path()
    {
        return "/media/{$this->id}";
    }

    public function media_group()
    {
        return $this->belongsTo(Media_group::class);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'media_projects');
    }
}
