<?php

namespace Tests\Unit\CMS\Repositories;

use App\CMS\ContentModels\TestPost;
use App\CMS\Repositories\ContentRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected ContentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ContentRepository(TestPost::class);
    }

    public function test_find_returns_model_by_id(): void
    {
        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $found = $this->repository->find($post->id);

        $this->assertInstanceOf(TestPost::class, $found);
        $this->assertEquals($post->id, $found->id);
        $this->assertEquals('Test Post', $found->getAttribute('title'));
    }

    public function test_find_returns_null_for_nonexistent_id(): void
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    public function test_find_by_slug_returns_model(): void
    {
        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $found = $this->repository->findBySlug($post->slug);

        $this->assertInstanceOf(TestPost::class, $found);
        $this->assertEquals($post->id, $found->id);
    }

    public function test_find_by_slug_returns_null_for_nonexistent_slug(): void
    {
        $found = $this->repository->findBySlug('nonexistent-slug');

        $this->assertNull($found);
    }

    public function test_all_returns_all_models(): void
    {
        TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        TestPost::create(['title' => 'Post 2', 'status' => 'published']);
        TestPost::create(['title' => 'Post 3', 'status' => 'draft']);

        $all = $this->repository->all();

        $this->assertCount(3, $all);
    }

    public function test_paginate_returns_paginated_results(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            TestPost::create([
                'title' => "Post $i",
                'status' => 'published',
            ]);
        }

        $paginated = $this->repository->paginate(10);

        $this->assertEquals(10, $paginated->count());
        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(3, $paginated->lastPage());
    }

    public function test_where_filters_results(): void
    {
        TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        TestPost::create(['title' => 'Post 2', 'status' => 'published']);
        TestPost::create(['title' => 'Post 3', 'status' => 'draft']);

        $published = $this->repository->where('status', 'published')->get();

        $this->assertCount(2, $published);
    }

    public function test_where_in_filters_results(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'draft']);
        TestPost::create(['title' => 'Post 3', 'status' => 'archived']);

        $results = $this->repository->whereIn('status', ['published', 'draft'])->get();

        $this->assertCount(2, $results);
    }

    public function test_order_by_sorts_results(): void
    {
        TestPost::create(['title' => 'C Post', 'status' => 'published']);
        TestPost::create(['title' => 'A Post', 'status' => 'published']);
        TestPost::create(['title' => 'B Post', 'status' => 'published']);

        $results = $this->repository->orderBy('title', 'asc')->get();

        $this->assertEquals('A Post', $results->first()->getAttribute('title'));
        $this->assertEquals('C Post', $results->last()->getAttribute('title'));
    }

    public function test_with_eager_loads_relationships(): void
    {
        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $post->setTranslation('title', 'it', 'Articolo di Test');

        \DB::enableQueryLog();
        \DB::flushQueryLog();

        $results = $this->repository->with(['translations'])->get();

        $queryLog = \DB::getQueryLog();

        // Should load posts and translations in 2 queries (not N+1)
        $this->assertLessThanOrEqual(2, count($queryLog));
        $this->assertTrue($results->first()->relationLoaded('translations'));
    }

    public function test_create_creates_new_model(): void
    {
        $data = [
            'title' => 'New Post',
            'content' => 'Post content',
            'status' => 'published',
        ];

        $model = $this->repository->create($data);

        $this->assertInstanceOf(TestPost::class, $model);
        $this->assertEquals('New Post', $model->getAttribute('title'));
        $this->assertTrue($model->exists);
        $this->assertDatabaseHas('test_posts', ['title' => 'New Post']);
    }

    public function test_update_updates_existing_model(): void
    {
        $post = TestPost::create([
            'title' => 'Original Title',
            'status' => 'draft',
        ]);

        $updated = $this->repository->update($post->id, [
            'title' => 'Updated Title',
            'status' => 'published',
        ]);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('test_posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'status' => 'published',
        ]);
    }

    public function test_update_returns_false_for_nonexistent_model(): void
    {
        $updated = $this->repository->update(999, ['title' => 'Test']);

        $this->assertFalse($updated);
    }

    public function test_delete_removes_model(): void
    {
        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $deleted = $this->repository->delete($post->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('test_posts', ['id' => $post->id]);
    }

    public function test_delete_returns_false_for_nonexistent_model(): void
    {
        $deleted = $this->repository->delete(999);

        $this->assertFalse($deleted);
    }

    public function test_count_returns_total_count(): void
    {
        TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        TestPost::create(['title' => 'Post 2', 'status' => 'published']);
        TestPost::create(['title' => 'Post 3', 'status' => 'draft']);

        $count = $this->repository->count();

        $this->assertEquals(3, $count);
    }

    public function test_count_with_where_returns_filtered_count(): void
    {
        TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        TestPost::create(['title' => 'Post 2', 'status' => 'published']);
        TestPost::create(['title' => 'Post 3', 'status' => 'draft']);

        $count = $this->repository->where('status', 'published')->count();

        $this->assertEquals(2, $count);
    }

    public function test_cache_stores_result(): void
    {
        config(['cms.cache.enabled' => true]);

        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        // Mock Cache facade
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($post);

        $found = $this->repository->cache('test-key', 60)->find($post->id);

        $this->assertInstanceOf(TestPost::class, $found);
    }

    public function test_fresh_bypasses_cache(): void
    {
        $post = TestPost::create([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        // Should not use cache even if enabled
        $found = $this->repository->fresh()->find($post->id);

        $this->assertInstanceOf(TestPost::class, $found);
    }
}
