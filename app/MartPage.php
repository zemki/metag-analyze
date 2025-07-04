<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MartPage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_pages';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'content', 'show_on_first_app_start', 'button_text', 'project_id', 'sort_order'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'show_on_first_app_start' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the project that owns the page.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope to get pages ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get pages for a specific project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}