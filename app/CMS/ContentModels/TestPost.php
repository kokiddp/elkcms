<?php

namespace App\CMS\ContentModels;

use App\CMS\Attributes\ContentModel;
use App\CMS\Attributes\Field;
use App\CMS\Attributes\SEO;

#[ContentModel(
    label: 'Test Posts',
    icon: 'edit',
    supports: ['translations', 'seo', 'media']
)]
#[SEO(
    schemaType: 'Article',
    schemaProperties: ['author', 'datePublished', 'image'],
    sitemapPriority: '0.8',
    sitemapChangeFreq: 'weekly'
)]
class TestPost extends BaseContent
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title', 'content', 'featured_image', 'published_at', 'status'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    #[Field(
        type: 'string',
        label: 'Post Title',
        required: true,
        translatable: true,
        maxLength: 200
    )]
    protected string $title;

    #[Field(
        type: 'text',
        label: 'Post Content',
        translatable: true
    )]
    protected string $content;

    #[Field(
        type: 'image',
        label: 'Featured Image',
        required: false
    )]
    protected ?string $featured_image = null;

    #[Field(
        type: 'datetime',
        label: 'Published At'
    )]
    protected ?\DateTime $published_at = null;
}
