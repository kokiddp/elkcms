<?php

namespace App\CMS\ContentModels;

use App\CMS\Traits\HasSEO;
use App\CMS\Traits\HasSlug;
use App\CMS\Traits\HasTranslations;
use App\CMS\Traits\OptimizedQueries;
use Illuminate\Database\Eloquent\Model;

abstract class BaseContent extends Model
{
    use HasTranslations;
    use HasSlug;
    use HasSEO;
    use OptimizedQueries;

    /**
     * Content status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    /**
     * Scope query to only published content
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope query to only draft content
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope query to only archived content
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Check if content is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if content is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if content is archived
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Publish content
     */
    public function publish(): bool
    {
        $this->status = self::STATUS_PUBLISHED;

        return $this->save();
    }

    /**
     * Unpublish content (set to draft)
     */
    public function unpublish(): bool
    {
        $this->status = self::STATUS_DRAFT;

        return $this->save();
    }

    /**
     * Archive content
     */
    public function archive(): bool
    {
        $this->status = self::STATUS_ARCHIVED;

        return $this->save();
    }
}
