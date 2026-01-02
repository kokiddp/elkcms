<?php

namespace App\CMS\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the HasSlug trait
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            // If slug is dirty (manually changed), ensure it's unique
            if ($model->isDirty('slug') && ! empty($model->slug)) {
                $model->slug = $model->ensureUniqueSlug($model->slug);
            }
        });
    }

    /**
     * Generate a slug from title or custom source
     *
     * @param  string|null  $source  Custom source text (defaults to getSlugSource())
     * @return string Generated slug
     */
    public function generateSlug(string $source = null): string
    {
        $source = $source ?? $this->getAttribute($this->getSlugSource());

        if (empty($source)) {
            // Fallback to a random string if no source available
            $source = 'content-'.Str::random(8);
        }

        $slug = Str::slug($source);

        return $this->slugShouldBeUnique() ? $this->ensureUniqueSlug($slug) : $slug;
    }

    /**
     * Ensure slug is unique by appending -1, -2, etc.
     *
     * @param  string  $slug  Base slug
     * @return string Unique slug
     */
    public function ensureUniqueSlug(string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists in database
     *
     * @param  string  $slug  Slug to check
     * @return bool
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        // Exclude current model if updating
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }

    /**
     * Get the field to generate slug from
     *
     * @return string Field name (default: 'title')
     */
    public function getSlugSource(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'title';
    }

    /**
     * Whether slugs must be unique
     *
     * @return bool
     */
    public function slugShouldBeUnique(): bool
    {
        return property_exists($this, 'slugUnique') ? $this->slugUnique : true;
    }
}
