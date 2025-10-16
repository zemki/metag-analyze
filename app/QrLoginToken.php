<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QrLoginToken extends Model
{
    protected $fillable = [
        'case_id',
        'encrypted_credential',
        'expires_at',
        'notify_on_use',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'notify_on_use' => 'boolean',
    ];

    /**
     * Get the case that owns this QR token
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * Get the user who created this QR token
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the token has expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false; // No expiration
        }

        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if the token is valid (active and not expired)
     */
    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Increment the usage count and update last used timestamp
     */
    public function incrementUsage()
    {
        $this->forceFill([
            'usage_count' => $this->usage_count + 1,
            'last_used_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Revoke this QR token
     */
    public function revoke()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope a query to only include active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include non-expired tokens
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }
}
