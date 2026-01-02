<?php

namespace Tests\Unit\CMS\Attributes;

use App\CMS\Attributes\SEO;
use PHPUnit\Framework\TestCase;

class SEOAttributeTest extends TestCase
{
    public function test_can_create_seo_attribute(): void
    {
        $seo = new SEO(
            schemaType: 'Article',
            schemaProperties: ['author', 'datePublished'],
            sitemapPriority: '0.8',
            sitemapChangeFreq: 'weekly'
        );

        $this->assertEquals('Article', $seo->schemaType);
        $this->assertEquals(['author', 'datePublished'], $seo->schemaProperties);
        $this->assertEquals('0.8', $seo->sitemapPriority);
        $this->assertEquals('weekly', $seo->sitemapChangeFreq);
    }

    public function test_seo_has_default_values(): void
    {
        $seo = new SEO();

        $this->assertEquals('Thing', $seo->schemaType);
        $this->assertEquals([], $seo->schemaProperties);
        $this->assertEquals('0.5', $seo->sitemapPriority);
        $this->assertEquals('monthly', $seo->sitemapChangeFreq);
        $this->assertTrue($seo->includedInSitemap);
        $this->assertTrue($seo->enableBreadcrumbs);
    }

    public function test_validates_sitemap_priority_min(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sitemap priority must be between 0.0 and 1.0');

        new SEO(sitemapPriority: '-0.1');
    }

    public function test_validates_sitemap_priority_max(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sitemap priority must be between 0.0 and 1.0');

        new SEO(sitemapPriority: '1.1');
    }

    public function test_validates_sitemap_change_frequency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sitemap change frequency must be one of');

        new SEO(sitemapChangeFreq: 'invalid');
    }

    public function test_accepts_valid_change_frequencies(): void
    {
        $validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

        foreach ($validFreqs as $freq) {
            $seo = new SEO(sitemapChangeFreq: $freq);
            $this->assertEquals($freq, $seo->sitemapChangeFreq);
        }
    }

    public function test_get_sitemap_priority_float(): void
    {
        $seo = new SEO(sitemapPriority: '0.8');
        $this->assertIsFloat($seo->getSitemapPriorityFloat());
        $this->assertEquals(0.8, $seo->getSitemapPriorityFloat());
    }

    public function test_get_schema_type_url(): void
    {
        $seo = new SEO(schemaType: 'Article');
        $this->assertEquals('https://schema.org/Article', $seo->getSchemaTypeUrl());
    }

    public function test_has_schema_property(): void
    {
        $seo = new SEO(
            schemaProperties: ['author', 'datePublished', 'image']
        );

        $this->assertTrue($seo->hasSchemaProperty('author'));
        $this->assertTrue($seo->hasSchemaProperty('datePublished'));
        $this->assertTrue($seo->hasSchemaProperty('image'));
        $this->assertFalse($seo->hasSchemaProperty('publisher'));
    }
}
