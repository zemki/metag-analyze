<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MartPage extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_pages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mart_project_id',
        'name',
        'content',
        'show_on_first_app_start',
        'button_text',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'show_on_first_app_start' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the MART project that owns the page.
     */
    public function martProject(): BelongsTo
    {
        return $this->belongsTo(MartProject::class, 'mart_project_id');
    }

    /**
     * Scope to get pages ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get pages for a specific MART project.
     */
    public function scopeForProject($query, $martProjectId)
    {
        return $query->where('mart_project_id', $martProjectId);
    }
}