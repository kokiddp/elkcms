<?php

namespace Tests\Unit\CMS\Traits;

use App\CMS\ContentModels\TestPost;
use Tests\TestCase;

class HasSEOTest extends TestCase
{
    protected TestPost $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestPost();
    }

    public function test_get_seo_title_returns_seo_title_field_if_set(): void
    {
        $this->model->setAttribute('seo_title', 'SEO Title');
        $this->model->setAttribute('title', 'Regular Title');

        $this->assertEquals('SEO Title', $this->model->getSEOTitle());
    }

    public function test_get_seo_title_falls_back_to_title(): void
    {
        $this->model->setAttribute('title', 'Regular Title');

        $this->assertEquals('Regular Title', $this->model->getSEOTitle());
    }

    public function test_get_seo_title_returns_empty_string_if_no_title(): void
    {
        $this->assertEquals('', $this->model->getSEOTitle());
    }

    public function test_get_seo_description_returns_seo_description_field_if_set(): void
    {
        $this->model->setAttribute('seo_description', 'SEO Description');
        $this->model->setAttribute('excerpt', 'Excerpt');
        $this->model->setAttribute('content', 'Content');

        $this->assertEquals('SEO Description', $this->model->getSEODescription());
    }

    public function test_get_seo_description_falls_back_to_excerpt(): void
    {
        $this->model->setAttribute('excerpt', 'Excerpt');
        $this->model->setAttribute('content', 'Content');

        $this->assertEquals('Excerpt', $this->model->getSEODescription());
    }

    public function test_get_seo_description_falls_back_to_truncated_content(): void
    {
        $this->model->setAttribute('content', 'This is a very long content that should be truncated to 160 characters maximum. '.
            'It contains multiple sentences and should be cut off at some point to ensure the description is not too long.');

        $description = $this->model->getSEODescription();

        $this->assertLessThanOrEqual(163, strlen($description)); // 160 + '...'
    }

    public function test_get_seo_description_strips_html_tags(): void
    {
        $this->model->setAttribute('content', '<p>Content with <strong>HTML</strong> tags</p>');

        $description = $this->model->getSEODescription();

        $this->assertStringNotContainsString('<p>', $description);
        $this->assertStringNotContainsString('<strong>', $description);
    }

    public function test_get_seo_description_returns_empty_string_if_no_content(): void
    {
        $this->assertEquals('', $this->model->getSEODescription());
    }

    public function test_get_seo_keywords_returns_array_from_string(): void
    {
        $this->model->setAttribute('seo_keywords', 'keyword1, keyword2, keyword3');

        $keywords = $this->model->getSEOKeywords();

        $this->assertIsArray($keywords);
        $this->assertCount(3, $keywords);
        $this->assertEquals(['keyword1', 'keyword2', 'keyword3'], $keywords);
    }

    public function test_get_seo_keywords_returns_array_if_already_array(): void
    {
        $this->model->setAttribute('seo_keywords', ['keyword1', 'keyword2']);

        $keywords = $this->model->getSEOKeywords();

        $this->assertIsArray($keywords);
        $this->assertEquals(['keyword1', 'keyword2'], $keywords);
    }

    public function test_get_seo_keywords_returns_empty_array_if_not_set(): void
    {
        $keywords = $this->model->getSEOKeywords();

        $this->assertIsArray($keywords);
        $this->assertEmpty($keywords);
    }

    public function test_get_seo_image_returns_seo_image_if_set(): void
    {
        $this->model->setAttribute('seo_image', 'seo-image.jpg');
        $this->model->setAttribute('featured_image', 'featured-image.jpg');

        $this->assertEquals('seo-image.jpg', $this->model->getSEOImage());
    }

    public function test_get_seo_image_falls_back_to_featured_image(): void
    {
        $this->model->setAttribute('featured_image', 'featured-image.jpg');

        $this->assertEquals('featured-image.jpg', $this->model->getSEOImage());
    }

    public function test_get_seo_image_returns_null_if_not_set(): void
    {
        $this->assertNull($this->model->getSEOImage());
    }

    public function test_get_canonical_url_generates_from_slug(): void
    {
        $this->model->setAttribute('slug', 'test-post');

        $url = $this->model->getCanonicalUrl();

        $this->assertStringContainsString('test-post', $url);
    }

    public function test_get_schema_markup_returns_array(): void
    {
        $this->model->setAttribute('title', 'Test Post');
        $this->model->setAttribute('content', 'Test Content');
        $this->model->setAttribute('slug', 'test-post');

        $schema = $this->model->getSchemaMarkup();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('@context', $schema);
        $this->assertArrayHasKey('@type', $schema);
        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('Article', $schema['@type']);
    }

    public function test_get_schema_markup_includes_title_and_description(): void
    {
        $this->model->setAttribute('title', 'Test Post');
        $this->model->setAttribute('content', 'Test Content');
        $this->model->setAttribute('slug', 'test-post');

        $schema = $this->model->getSchemaMarkup();

        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('description', $schema);
        $this->assertEquals('Test Post', $schema['name']);
    }

    public function test_get_schema_markup_includes_image_if_available(): void
    {
        $this->model->setAttribute('title', 'Test Post');
        $this->model->setAttribute('featured_image', 'image.jpg');
        $this->model->setAttribute('slug', 'test-post');

        $schema = $this->model->getSchemaMarkup();

        $this->assertArrayHasKey('image', $schema);
        $this->assertEquals('image.jpg', $schema['image']);
    }

    public function test_get_sitemap_priority_returns_value_from_attribute(): void
    {
        $priority = $this->model->getSitemapPriority();

        $this->assertEquals(0.8, $priority);
    }

    public function test_get_sitemap_change_freq_returns_value_from_attribute(): void
    {
        $freq = $this->model->getSitemapChangeFreq();

        $this->assertEquals('weekly', $freq);
    }

    public function test_should_include_in_sitemap_returns_true_for_published(): void
    {
        $this->model->setAttribute('status', 'published');

        $this->assertTrue($this->model->shouldIncludeInSitemap());
    }

    public function test_should_include_in_sitemap_returns_false_for_draft(): void
    {
        $this->model->setAttribute('status', 'draft');

        $this->assertFalse($this->model->shouldIncludeInSitemap());
    }

    public function test_should_include_in_sitemap_returns_false_if_noindex(): void
    {
        $this->model->setAttribute('status', 'published');
        $this->model->setAttribute('seo_noindex', true);

        $this->assertFalse($this->model->shouldIncludeInSitemap());
    }
}
