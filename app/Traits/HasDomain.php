<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait HasDomain
{
    /**
     * Boot the HasDomain trait.
     * Automatically sets the `domain` column on model creation.
     */
    public static function bootHasDomain()
    {
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'domain') && empty($model->domain)) {
                $model->domain = \Helper::getDomain();
            }
        });
    }

    /**
     * Initialize the HasDomain trait (appends domain_display).
     */
    public function initializeHasDomain()
    {
        if (!in_array('domain_display', $this->appends)) {
            $this->appends[] = 'domain_display';
        }
    }

    /**
     * Get the domain to display with priority:
     * 1. Stored domain on this record
     * 2. User's registered domain (fallback)
     * 3. 'N/A' (final fallback)
     */
    public function getDomainDisplayAttribute()
    {
        // 1. Domain recorded at time of purchase/action (highest priority)
        if (!empty($this->domain)) {
            return $this->domain;
        }

        // 2. Registered Domain of the associated user (fallback)
        if (!empty($this->user) && !empty($this->user->domain)) {
            return $this->user->domain;
        }

        // 3. Final fallback
        return 'N/A';
    }
}
