<?php

namespace App\Mart;

use App\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MartProject extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_projects';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'main_project_id',
    ];

    /**
     * Get the main project from the main database.
     * Note: This is a cross-database query, not a true Eloquent relationship.
     */
    public function mainProject(): ?Project
    {
        return Project::find($this->main_project_id);
    }

    /**
     * Get the schedules for this MART project.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(MartSchedule::class, 'mart_project_id');
    }

    /**
     * Get the pages for this MART project.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(MartPage::class, 'mart_project_id');
    }

    /**
     * Get the stats for this MART project.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(MartStat::class, 'mart_project_id');
    }
}