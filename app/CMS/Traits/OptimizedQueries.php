<?php

namespace App\CMS\Traits;

use App\CMS\Reflection\ModelScanner;
use Illuminate\Support\Facades\Cache;

trait OptimizedQueries
{
    /**
     * Scope to eager load common relationships based on ContentModel supports
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCommonRelations($query)
    {
        $scanner = new ModelScanner();
        $modelData = $scanner->scan(static::class);
        $contentModel = $modelData['contentModel'] ?? null;

        if (! $contentModel) {
            return $query;
        }

        $supports = $contentModel['supports'] ?? [];
        $relations = [];

        // Build array of relations to eager load based on supports
        if (in_array('translations', $supports)) {
            $relations[] = 'translations';
        }

        if (in_array('media', $supports)) {
            $relations[] = 'media';
        }

        if (in_array('blocks', $supports)) {
            $relations[] = 'blocks';
        }

        // Add relationships defined in model
        foreach ($modelData['relationships'] as $relationName => $relationData) {
            if ($relationData['eagerLoad'] ?? false) {
                $relations[] = $relationName;
            }
        }

        return $query->with($relations);
    }

    /**
     * Scope to eager load translations for specific locale
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTranslations($query, string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        // TODO: This will be implemented when Translation model exists (Phase 3)
        // For now, just return the query
        return $query;
    }

    /**
     * Scope to eager load SEO data
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSEO($query)
    {
        // TODO: This will be implemented when SEO data is stored separately (Phase 5)
        // For now, SEO data is in the model attributes, so just return the query
        return $query;
    }

    /**
     * Scope to load all common optimizations
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOptimized($query)
    {
        return $query->withCommonRelations()
            ->withTranslations()
            ->withSEO();
    }

    /**
     * Get unique cache key for this model instance
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        $prefix = config('cms.cache.prefix', 'cms_');
        $table = $this->getTable();
        $id = $this->getKey();

        return $prefix.$table.'_'.$id;
    }

    /**
     * Get cache TTL in seconds
     *
     * @return int
     */
    public function getCacheTTL(): int
    {
        return (int) (config('cms.cache.content_ttl') ?? config('cms.cache.ttl') ?? 3600);
    }

    /**
     * Clear cache for this model instance
     *
     * @return void
     */
    public function flushCache(): void
    {
        if (config('cms.cache.enabled', true)) {
            Cache::forget($this->getCacheKey());
        }
    }

    /**
     * Boot the OptimizedQueries trait
     */
    protected static function bootOptimizedQueries(): void
    {
        // Clear cache on model update
        static::updated(function ($model) {
            $model->flushCache();
        });

        // Clear cache on model deletion
        static::deleted(function ($model) {
            $model->flushCache();
        });
    }
}
