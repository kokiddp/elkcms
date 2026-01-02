<?php

namespace App\CMS\ContentModels;

use App\CMS\Attributes\ContentModel;
use App\CMS\Attributes\Field;
use App\CMS\Attributes\SEO;
use Illuminate\Database\Eloquent\Model;

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
class TestPost extends Model
{
    #[Field(
        type: 'string',
        label: 'Post Title',
        required: true,
        translatable: true,
        maxLength: 200
    )]
    public string $title;

    #[Field(
        type: 'text',
        label: 'Post Content',
        translatable: true
    )]
    public string $content;

    #[Field(
        type: 'image',
        label: 'Featured Image',
        required: false
    )]
    public ?string $featured_image;

    #[Field(
        type: 'datetime',
        label: 'Published At'
    )]
    public ?\DateTime $published_at;
}
