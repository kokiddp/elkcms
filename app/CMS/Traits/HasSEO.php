<?php

namespace App\CMS\Traits;

use App\CMS\Attributes\SEO;
use App\CMS\Reflection\ModelScanner;

trait HasSEO
{
    /**
     * Cached SEO attribute (array from ModelScanner)
     */
    protected ?array $seoAttributeCache = null;

    /**
     * Get SEO title with fallback to title field
     *
     * @return string
     */
    public function getSEOTitle(): string
    {
        // Try seo_title field first
        if ($this->getAttribute('seo_title')) {
            return $this->getAttribute('seo_title');
        }

        // Fallback to title field
        if ($this->getAttribute('title')) {
            return $this->getAttribute('title');
        }

        return '';
    }

    /**
     * Get SEO description with fallback to excerpt or truncated content
     *
     * @return string
     */
    public function getSEODescription(): string
    {
        // Try seo_description field first
        if ($this->getAttribute('seo_description')) {
            return $this->getAttribute('seo_description');
        }

        // Fallback to excerpt
        if ($this->getAttribute('excerpt')) {
            return $this->getAttribute('excerpt');
        }

        // Fallback to truncated content
        if ($this->getAttribute('content')) {
            return \Illuminate\Support\Str::limit(strip_tags($this->getAttribute('content')), 160);
        }

        return '';
    }

    /**
     * Get SEO keywords
     *
     * @return array
     */
    public function getSEOKeywords(): array
    {
        $keywords = $this->getAttribute('seo_keywords');

        if (is_array($keywords)) {
            return $keywords;
        }

        if (is_string($keywords)) {
            return array_map('trim', explode(',', $keywords));
        }

        return [];
    }

    /**
     * Get SEO image URL
     *
     * @return string|null
     */
    public function getSEOImage(): ?string
    {
        // Try seo_image field first
        if ($this->getAttribute('seo_image')) {
            return $this->getAttribute('seo_image');
        }

        // Fallback to featured_image
        if ($this->getAttribute('featured_image')) {
            return $this->getAttribute('featured_image');
        }

        return null;
    }

    /**
     * Get canonical URL
     *
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        $seoAttr = $this->getSEOAttribute();

        if ($seoAttr && ! empty($seoAttr['canonicalUrl'])) {
            return $seoAttr['canonicalUrl'];
        }

        // Generate URL from slug
        if ($this->getAttribute('slug')) {
            return url('/'.$this->getAttribute('slug'));
        }

        return url('/');
    }

    /**
     * Generate Schema.org JSON-LD markup
     *
     * @return array
     */
    public function getSchemaMarkup(): array
    {
        $seoAttr = $this->getSEOAttribute();

        if (! $seoAttr) {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $seoAttr['schemaType'],
            'name' => $this->getSEOTitle(),
            'description' => $this->getSEODescription(),
            'url' => $this->getCanonicalUrl(),
        ];

        // Add custom properties defined in SEO attribute
        if (! empty($seoAttr['schemaProperties'])) {
            foreach ($seoAttr['schemaProperties'] as $property) {
                if ($value = $this->getAttribute($property)) {
                    $schema[$property] = $value;
                }
            }
        }

        // Add image if available
        if ($image = $this->getSEOImage()) {
            $schema['image'] = $image;
        }

        return $schema;
    }

    /**
     * Get sitemap priority
     *
     * @return float
     */
    public function getSitemapPriority(): float
    {
        $seoAttr = $this->getSEOAttribute();

        if ($seoAttr && isset($seoAttr['sitemapPriority'])) {
            return (float) $seoAttr['sitemapPriority'];
        }

        return 0.5;
    }

    /**
     * Get sitemap change frequency
     *
     * @return string
     */
    public function getSitemapChangeFreq(): string
    {
        $seoAttr = $this->getSEOAttribute();

        if ($seoAttr && isset($seoAttr['sitemapChangeFreq'])) {
            return $seoAttr['sitemapChangeFreq'];
        }

        return 'monthly';
    }

    /**
     * Check if content should be included in sitemap
     *
     * @return bool
     */
    public function shouldIncludeInSitemap(): bool
    {
        // Only include published content
        if (method_exists($this, 'isPublished') && ! $this->isPublished()) {
            return false;
        }

        // Check if noindex meta is set
        if ($this->getAttribute('seo_noindex')) {
            return false;
        }

        return true;
    }

    /**
     * Get SEO attribute from model class
     *
     * @return array|null
     */
    protected function getSEOAttribute(): ?array
    {
        if ($this->seoAttributeCache !== null) {
            return $this->seoAttributeCache;
        }

        $scanner = new ModelScanner();
        $modelData = $scanner->scan(static::class);

        $this->seoAttributeCache = $modelData['seo'] ?? null;

        return $this->seoAttributeCache;
    }
}
