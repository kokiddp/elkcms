<?php

namespace Tests\Unit\CMS\Traits;

use App\CMS\ContentModels\TestPost;
use Tests\TestCase;

class OptimizedQueriesTest extends TestCase
{
    protected TestPost $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestPost();
    }

    public function test_get_cache_key_returns_string(): void
    {
        $this->model->id = 123;

        $cacheKey = $this->model->getCacheKey();

        $this->assertIsString($cacheKey);
    }

    public function test_get_cache_key_includes_table_name(): void
    {
        $this->model->id = 123;

        $cacheKey = $this->model->getCacheKey();

        $this->assertStringContainsString('test_posts', $cacheKey);
    }

    public function test_get_cache_key_includes_model_id(): void
    {
        $this->model->id = 123;

        $cacheKey = $this->model->getCacheKey();

        $this->assertStringContainsString('123', $cacheKey);
    }

    public function test_get_cache_key_includes_prefix(): void
    {
        config(['cms.cache.prefix' => 'test_']);
        $this->model->id = 123;

        $cacheKey = $this->model->getCacheKey();

        $this->assertStringStartsWith('test_', $cacheKey);
    }

    public function test_get_cache_ttl_returns_integer(): void
    {
        $ttl = $this->model->getCacheTTL();

        $this->assertIsInt($ttl);
    }

    public function test_get_cache_ttl_returns_value_from_config(): void
    {
        config(['cms.cache.content_ttl' => 7200]);

        $ttl = $this->model->getCacheTTL();

        $this->assertEquals(7200, $ttl);
    }

    public function test_get_cache_ttl_falls_back_to_general_ttl(): void
    {
        config(['cms.cache.content_ttl' => null, 'cms.cache.ttl' => 1800]);

        $ttl = $this->model->getCacheTTL();

        $this->assertEquals(1800, $ttl);
    }

    public function test_flush_cache_does_not_throw_exception(): void
    {
        config(['cms.cache.enabled' => false]);

        $this->model->flushCache();

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_scope_with_common_relations_returns_query(): void
    {
        $query = TestPost::query();
        $result = $query->withCommonRelations();

        $this->assertSame($query, $result);
    }

    public function test_scope_with_translations_returns_query(): void
    {
        $query = TestPost::query();
        $result = $query->withTranslations('en');

        $this->assertSame($query, $result);
    }

    public function test_scope_with_seo_returns_query(): void
    {
        $query = TestPost::query();
        $result = $query->withSEO();

        $this->assertSame($query, $result);
    }

    public function test_scope_optimized_returns_query(): void
    {
        $query = TestPost::query();
        $result = $query->optimized();

        $this->assertSame($query, $result);
    }
}
