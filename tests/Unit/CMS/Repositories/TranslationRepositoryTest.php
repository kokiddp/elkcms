<?php

namespace Tests\Unit\CMS\Repositories;

use App\CMS\ContentModels\TestPost;
use App\CMS\Repositories\TranslationRepository;
use App\Models\Translation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected TranslationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TranslationRepository();
    }

    public function test_get_by_model_returns_all_translations(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $translations = $this->repository->getByModel($post);

        $this->assertCount(3, $translations);
    }

    public function test_get_by_model_and_locale_filters_by_locale(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $translations = $this->repository->getByModelAndLocale($post, 'it');

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->every(fn ($t) => $t->locale === 'it'));
    }

    public function test_get_by_model_and_field_filters_by_field(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $translations = $this->repository->getByModelAndField($post, 'title');

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->every(fn ($t) => $t->field === 'title'));
    }

    public function test_find_translation_returns_specific_translation(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');

        $translation = $this->repository->findTranslation($post, 'title', 'it');

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('title', $translation->field);
        $this->assertEquals('it', $translation->locale);
        $this->assertEquals('Titolo Italiano', $translation->value);
    }

    public function test_find_translation_returns_null_if_not_found(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);

        $translation = $this->repository->findTranslation($post, 'title', 'xx');

        $this->assertNull($translation);
    }

    public function test_get_by_locale_returns_all_translations_for_locale(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo 1');
        $post1->setTranslation('title', 'de', 'Artikel 1');
        $post2->setTranslation('title', 'it', 'Articolo 2');

        $translations = $this->repository->getByLocale('it');

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->every(fn ($t) => $t->locale === 'it'));
    }

    public function test_get_by_model_type_returns_translations_for_model_class(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo 1');
        $post2->setTranslation('title', 'it', 'Articolo 2');

        $translations = $this->repository->getByModelType(TestPost::class);

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->every(fn ($t) => $t->translatable_type === TestPost::class));
    }

    public function test_count_by_locale_returns_translation_counts(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo 1');
        $post1->setTranslation('content', 'it', 'Contenuto 1');
        $post2->setTranslation('title', 'it', 'Articolo 2');
        $post2->setTranslation('title', 'de', 'Artikel 2');

        $counts = $this->repository->countByLocale();

        $this->assertEquals(3, $counts['it']);
        $this->assertEquals(1, $counts['de']);
    }

    public function test_count_by_model_type_returns_model_counts(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo 1');
        $post2->setTranslation('title', 'it', 'Articolo 2');

        $counts = $this->repository->countByModelType();

        $this->assertEquals(2, $counts[TestPost::class]);
    }

    public function test_delete_by_model_removes_all_translations(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $this->assertDatabaseCount('cms_translations', 3);

        $deleted = $this->repository->deleteByModel($post);

        $this->assertEquals(3, $deleted);
        $this->assertDatabaseCount('cms_translations', 0);
    }

    public function test_delete_by_model_and_locale_removes_locale_translations(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $this->assertDatabaseCount('cms_translations', 3);

        $deleted = $this->repository->deleteByModelAndLocale($post, 'it');

        $this->assertEquals(2, $deleted);
        $this->assertDatabaseCount('cms_translations', 1);

        // German translation should remain
        $remaining = Translation::first();
        $this->assertEquals('de', $remaining->locale);
    }

    public function test_delete_by_model_and_field_removes_field_translations(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $this->assertDatabaseCount('cms_translations', 3);

        $deleted = $this->repository->deleteByModelAndField($post, 'title');

        $this->assertEquals(2, $deleted);
        $this->assertDatabaseCount('cms_translations', 1);

        // Content translation should remain
        $remaining = Translation::first();
        $this->assertEquals('content', $remaining->field);
    }

    public function test_bulk_update_updates_multiple_translations(): void
    {
        $post = TestPost::create(['title' => 'English Title', 'status' => 'published']);
        $post->setTranslation('title', 'it', 'Old Title');
        $post->setTranslation('content', 'it', 'Old Content');

        $updates = [
            ['field' => 'title', 'locale' => 'it', 'value' => 'New Title'],
            ['field' => 'content', 'locale' => 'it', 'value' => 'New Content'],
        ];

        $updated = $this->repository->bulkUpdate($post, $updates);

        $this->assertEquals(2, $updated);

        $titleTranslation = $this->repository->findTranslation($post, 'title', 'it');
        $this->assertEquals('New Title', $titleTranslation->value);

        $contentTranslation = $this->repository->findTranslation($post, 'content', 'it');
        $this->assertEquals('New Content', $contentTranslation->value);
    }

    public function test_search_by_value_finds_translations(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo Importante');
        $post2->setTranslation('title', 'it', 'Altro Articolo');

        $results = $this->repository->searchByValue('Importante');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('Importante', $results->first()->value);
    }

    public function test_search_by_value_with_locale_filters_results(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);

        $post1->setTranslation('title', 'it', 'Articolo Importante');
        $post1->setTranslation('title', 'de', 'Wichtiger Artikel');

        $results = $this->repository->searchByValue('Articolo', 'it');

        $this->assertCount(1, $results);
        $this->assertEquals('it', $results->first()->locale);
    }
}
