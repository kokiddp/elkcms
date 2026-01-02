<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SEO
{
    /**
     * Create a new SEO attribute instance.
     *
     * @param  string  $schemaType  Schema.org type (e.g., "Article", "WebPage", "CreativeWork", "Event")
     * @param  array  $schemaProperties  Properties to include in Schema.org markup (e.g., ['author', 'dateCreated', 'image'])
     * @param  string  $sitemapPriority  Sitemap priority (0.0 to 1.0 as string, e.g., "0.8")
     * @param  string  $sitemapChangeFreq  Sitemap change frequency (always, hourly, daily, weekly, monthly, yearly, never)
     * @param  bool  $includedInSitemap  Whether to include in XML sitemap (default: true)
     * @param  array  $metaFields  Additional meta fields to generate
     * @param  bool  $enableBreadcrumbs  Whether to generate breadcrumb schema (default: true)
     */
    public function __construct(
        public string $schemaType = 'Thing',
        public array $schemaProperties = [],
        public string $sitemapPriority = '0.5',
        public string $sitemapChangeFreq = 'monthly',
        public bool $includedInSitemap = true,
        public array $metaFields = [],
        public bool $enableBreadcrumbs = true,
    ) {
        // Validate sitemap priority
        if ((float) $this->sitemapPriority < 0 || (float) $this->sitemapPriority > 1) {
            throw new \InvalidArgumentException('Sitemap priority must be between 0.0 and 1.0');
        }

        // Validate change frequency
        $validFrequencies = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
        if (! in_array($this->sitemapChangeFreq, $validFrequencies, true)) {
            throw new \InvalidArgumentException(
                'Sitemap change frequency must be one of: '.implode(', ', $validFrequencies)
            );
        }
    }

    /**
     * Get the sitemap priority as a float.
     *
     * @return float
     */
    public function getSitemapPriorityFloat(): float
    {
        return (float) $this->sitemapPriority;
    }

    /**
     * Get the Schema.org type URL.
     *
     * @return string
     */
    public function getSchemaTypeUrl(): string
    {
        return 'https://schema.org/'.$this->schemaType;
    }

    /**
     * Check if a specific schema property is enabled.
     *
     * @param  string  $property
     * @return bool
     */
    public function hasSchemaProperty(string $property): bool
    {
        return in_array($property, $this->schemaProperties, true);
    }
}
