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
    /**
     * Page type constants for special purpose pages.
     * Only one page per type is allowed per project.
     */
    public const PAGE_TYPE_SUCCESS = 'success';
    public const PAGE_TYPE_ANDROID_STATS_PERMISSION = 'android_stats_permission';
    public const PAGE_TYPE_ANDROID_NOTIFICATION_PERMISSION = 'android_notification_permission';
    public const PAGE_TYPE_IOS_NOTIFICATION_PERMISSION = 'ios_notification_permission';

    protected $fillable = [
        'mart_project_id',
        'name',
        'content',
        'show_on_first_app_start',
        'button_text',
        'sort_order',
        'is_success_page',
        'show_in_menu',
        'page_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'show_on_first_app_start' => 'boolean',
        'is_success_page' => 'boolean',
        'show_in_menu' => 'boolean',
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

    /**
     * Mark this page as the success page for its project.
     * Automatically unmarks any other success pages in the same project.
     *
     * @return bool
     */
    public function markAsSuccessPage(): bool
    {
        $this->is_success_page = true;
        return $this->save();
    }

    /**
     * Unmark this page as the success page.
     *
     * @return bool
     */
    public function unmarkAsSuccessPage(): bool
    {
        $this->is_success_page = false;
        return $this->save();
    }
}