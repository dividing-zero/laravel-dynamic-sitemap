<?php

namespace DividingZero\LaravelDynamicSitemap\Traits;

use Carbon\Carbon;

trait Sitemappable
{
    /**
     * Define the sitemap URL for this model instance.
     * 
     * @return string
     */
    abstract public function getSitemapUrl(): string;

    /**
     * Get the last modification date for the sitemap entry.
     * 
     * @return \Carbon\Carbon
     */
    public function getSitemapModifiedDate(): Carbon
    {
        return $this->updated_at ?? Carbon::parse(config('dynamic-sitemap.default_modified_date'));
    }

    /**
     * Get the change frequency for the sitemap entry.
     * 
     * @return string (e.g., 'daily', 'weekly')
     */
    public function getSitemapChangeFreqency(): string
    {
        // Use config default if not overridden
        return config('dynamic-sitemap.default_change_frequency');
    }

    /**
     * Get the priority for the sitemap entry.
     * 
     * @return float
     */
    public function getSitemapPriority(): float
    {
        // Use config default if not overridden
        return config('dynamic-sitemap.default_priority');
    }

    /**
     * Scope a query to only include records that should be in the sitemap.
     * Returns unscoped query with all records by default.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSitemapQuery($query)
    {
        return $query;
    }
}
