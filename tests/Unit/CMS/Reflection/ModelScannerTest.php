<?php

namespace Tests\Unit\CMS\Reflection;

use App\CMS\ContentModels\TestPost;
use App\CMS\Reflection\ModelScanner;
use Tests\TestCase;

class ModelScannerTest extends TestCase
{
    protected ModelScanner $scanner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new ModelScanner();
    }

    public function test_can_scan_content_model(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('shortName', $result);
        $this->assertArrayHasKey('namespace', $result);
        $this->assertArrayHasKey('contentModel', $result);
        $this->assertArrayHasKey('seo', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('relationships', $result);
    }

    public function test_extracts_content_model_attribute(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertNotNull($result['contentModel']);
        $this->assertEquals('Test Posts', $result['contentModel']['label']);
        $this->assertEquals('edit', $result['contentModel']['icon']);
        $this->assertContains('translations', $result['contentModel']['supports']);
        $this->assertContains('seo', $result['contentModel']['supports']);
        $this->assertContains('media', $result['contentModel']['supports']);
    }

    public function test_extracts_seo_attribute(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertNotNull($result['seo']);
        $this->assertEquals('Article', $result['seo']['schemaType']);
        $this->assertEquals('https://schema.org/Article', $result['seo']['schemaTypeUrl']);
        $this->assertEquals(0.8, $result['seo']['sitemapPriority']);
        $this->assertEquals('weekly', $result['seo']['sitemapChangeFreq']);
    }

    public function test_extracts_field_attributes(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertIsArray($result['fields']);
        $this->assertArrayHasKey('title', $result['fields']);
        $this->assertArrayHasKey('content', $result['fields']);
        $this->assertArrayHasKey('featured_image', $result['fields']);
        $this->assertArrayHasKey('published_at', $result['fields']);
    }

    public function test_field_title_has_correct_properties(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);
        $title = $result['fields']['title'];

        $this->assertEquals('string', $title['type']);
        $this->assertEquals('Post Title', $title['label']);
        $this->assertTrue($title['required']);
        $this->assertTrue($title['translatable']);
        $this->assertEquals(200, $title['maxLength']);
        $this->assertEquals('string:200', $title['databaseType']);
    }

    public function test_field_content_has_correct_properties(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);
        $content = $result['fields']['content'];

        $this->assertEquals('text', $content['type']);
        $this->assertEquals('Post Content', $content['label']);
        $this->assertFalse($content['required']);
        $this->assertTrue($content['translatable']);
        $this->assertEquals('text', $content['databaseType']);
    }

    public function test_field_featured_image_has_correct_properties(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);
        $image = $result['fields']['featured_image'];

        $this->assertEquals('image', $image['type']);
        $this->assertEquals('Featured Image', $image['label']);
        $this->assertFalse($image['required']);
        $this->assertFalse($image['translatable']);
        $this->assertEquals('string', $image['databaseType']);
    }

    public function test_generates_validation_rules_for_fields(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertContains('required', $result['fields']['title']['validation']);
        $this->assertContains('string', $result['fields']['title']['validation']);
        $this->assertContains('max:200', $result['fields']['title']['validation']);

        $this->assertContains('nullable', $result['fields']['content']['validation']);
        $this->assertContains('image', $result['fields']['featured_image']['validation']);
    }

    public function test_scanning_without_cache_works(): void
    {
        // Scanning without cache should always work
        $result1 = $this->scanner->scan(TestPost::class, useCache: false);
        $result2 = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEquals($result1['class'], $result2['class']);
    }

    public function test_returns_correct_class_metadata(): void
    {
        $result = $this->scanner->scan(TestPost::class, useCache: false);

        $this->assertEquals(TestPost::class, $result['class']);
        $this->assertEquals('TestPost', $result['shortName']);
        $this->assertEquals('App\CMS\ContentModels', $result['namespace']);
    }

    public function test_can_clear_specific_model_cache(): void
    {
        // Skip if CMS cache is disabled (cache operations won't work)
        if (!config('cms.cache.enabled', true)) {
            $this->markTestSkipped('Cache is disabled in testing environment');
        }

        // Temporarily switch to array cache for testing (no database required)
        config(['cache.default' => 'array']);

        // First, scan and cache the model
        $this->scanner->scan(TestPost::class, useCache: true);

        // Clear specific model cache
        $this->scanner->clearCache(TestPost::class);

        // This should work without errors
        $this->assertTrue(true);
    }

    public function test_clear_all_cache_executes_without_error(): void
    {
        // Skip if CMS cache is disabled (cache operations won't work)
        if (!config('cms.cache.enabled', true)) {
            $this->markTestSkipped('Cache is disabled in testing environment');
        }

        // Temporarily switch to array cache for testing (no database required)
        config(['cache.default' => 'array']);

        // Scan and cache the model
        $this->scanner->scan(TestPost::class, useCache: true);

        // Clear all cache (note: this clears ALL application cache)
        $this->scanner->clearAllCache();

        // This should work without errors
        $this->assertTrue(true);
    }

    public function test_respects_cache_disabled_config(): void
    {
        // When cache is disabled in testing (.env.testing has CMS_CACHE_ENABLED=false)
        // scanning should not use cache even if useCache=true
        config(['cms.cache.enabled' => false]);

        $result1 = $this->scanner->scan(TestPost::class, useCache: true);
        $result2 = $this->scanner->scan(TestPost::class, useCache: true);

        // Both should return valid results
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEquals($result1['class'], $result2['class']);
    }
}
