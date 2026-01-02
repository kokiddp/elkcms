<?php

namespace Tests\Unit\CMS\Services;

use App\CMS\ContentModels\TestPost;
use App\CMS\Services\TranslationService;
use App\Models\Translation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TranslationService();
    }

    public function test_can_translate_model_with_multiple_fields(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        $translations = [
            'title' => 'Titolo Italiano',
            'content' => 'Contenuto Italiano',
        ];

        $this->service->translateModel($post, $translations, 'it');

        $this->assertEquals('Titolo Italiano', $post->translate('title', 'it'));
        $this->assertEquals('Contenuto Italiano', $post->translate('content', 'it'));
    }

    public function test_get_model_translations_returns_all_fields(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $translations = $this->service->getModelTranslations($post, 'it');

        $this->assertArrayHasKey('title', $translations);
        $this->assertArrayHasKey('content', $translations);
        $this->assertEquals('Titolo Italiano', $translations['title']);
        $this->assertEquals('Contenuto Italiano', $translations['content']);
    }

    public function test_get_model_translations_without_locale_returns_all_locales(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'status' => 'published',
        ]);

        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('title', 'de', 'Deutscher Titel');

        $translations = $this->service->getModelTranslations($post);

        $this->assertArrayHasKey('title', $translations);
        $this->assertCount(3, $translations['title']); // en, it, de
    }

    public function test_copy_translations_copies_all_fields(): void
    {
        $source = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        $source->setTranslation('title', 'it', 'Titolo Italiano');
        $source->setTranslation('content', 'it', 'Contenuto Italiano');

        $target = TestPost::create([
            'title' => 'Another Title',
            'status' => 'published',
        ]);

        $this->service->copyTranslations($source, $target, 'it');

        $this->assertEquals('Titolo Italiano', $target->translate('title', 'it'));
        $this->assertEquals('Contenuto Italiano', $target->translate('content', 'it'));
    }

    public function test_bulk_translate_translates_multiple_models(): void
    {
        $posts = collect([
            TestPost::create(['title' => 'Post 1', 'status' => 'published']),
            TestPost::create(['title' => 'Post 2', 'status' => 'published']),
            TestPost::create(['title' => 'Post 3', 'status' => 'published']),
        ]);

        // Set Italian translations
        foreach ($posts as $post) {
            $post->setTranslation('title', 'it', "Articolo {$post->id}");
        }

        // Bulk translate from Italian to German using a translator function
        $count = $this->service->bulkTranslate($posts, 'it', 'de', function ($value) {
            // Simple mock translator
            return str_replace('Articolo', 'Artikel', $value);
        });

        $this->assertEquals(3, $count);

        foreach ($posts as $post) {
            $post->refresh();
            $this->assertEquals("Artikel {$post->id}", $post->translate('title', 'de'));
        }
    }

    public function test_get_translation_progress_calculates_percentage(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        // Translate only title (1 out of 2 translatable fields)
        $post->setTranslation('title', 'it', 'Titolo Italiano');

        $progress = $this->service->getTranslationProgress($post);

        $this->assertArrayHasKey('it', $progress);
        $this->assertEquals(50, $progress['it']['percentage']); // 1/2 = 50%
        $this->assertEquals(2, $progress['it']['total']);
        $this->assertEquals(1, $progress['it']['translated']);
    }

    public function test_get_missing_translations_returns_untranslated_models(): void
    {
        $post1 = TestPost::create(['title' => 'Post 1', 'status' => 'published']);
        $post2 = TestPost::create(['title' => 'Post 2', 'status' => 'published']);

        // Only translate post1 partially (only title, not content)
        $post1->setTranslation('title', 'it', 'Articolo 1');

        $missing = $this->service->getMissingTranslations('it');

        // Should include both posts:
        // - post1 has partial translation (title but not content = 50%)
        // - post2 has no Italian translations at all (0%)
        $missingIds = $missing->pluck('id')->toArray();
        $this->assertContains($post1->id, $missingIds);
        $this->assertContains($post2->id, $missingIds);
        $this->assertCount(2, $missing);
    }

    public function test_cache_translations_stores_in_cache(): void
    {
        config(['cms.cache.enabled' => true]);

        $post = TestPost::create([
            'title' => 'English Title',
            'status' => 'published',
        ]);

        $post->setTranslation('title', 'it', 'Titolo Italiano');

        Cache::shouldReceive('put')
            ->once()
            ->with(
                $this->stringContains('translations'),
                $this->anything(),
                $this->anything()
            );

        $this->service->cacheTranslations($post, 'it');
    }

    public function test_clear_translation_cache_removes_cache(): void
    {
        config(['cms.cache.enabled' => true]);

        $post = TestPost::create([
            'title' => 'English Title',
            'status' => 'published',
        ]);

        Cache::shouldReceive('forget')
            ->once()
            ->with($this->stringContains('translations'));

        $this->service->clearTranslationCache($post, 'it');
    }

    public function test_validate_translations_returns_empty_array_for_valid_data(): void
    {
        $translations = [
            'title' => 'Valid Title',
            'content' => 'Valid Content',
        ];

        $errors = $this->service->validateTranslations($translations);

        $this->assertEmpty($errors);
    }

    public function test_validate_translations_returns_errors_for_invalid_data(): void
    {
        $translations = [
            'title' => ['array' => 'not allowed'], // Invalid: array
            'content' => null, // Valid
        ];

        $errors = $this->service->validateTranslations($translations);

        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayNotHasKey('content', $errors);
    }

    public function test_can_translate_checks_field_and_locale(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'status' => 'published',
        ]);

        // Can translate translatable field with supported locale
        $this->assertTrue($this->service->canTranslate($post, 'title', 'it'));

        // Cannot translate non-translatable field
        $this->assertFalse($this->service->canTranslate($post, 'status', 'it'));

        // Cannot translate with unsupported locale
        $this->assertFalse($this->service->canTranslate($post, 'title', 'xx'));
    }

    public function test_export_translations_as_json(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        $post->setTranslation('title', 'it', 'Titolo Italiano');
        $post->setTranslation('content', 'it', 'Contenuto Italiano');

        $json = $this->service->exportTranslations($post, 'json');

        $data = json_decode($json, true);

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('content', $data);
        $this->assertEquals('Titolo Italiano', $data['title']['it']);
    }

    public function test_import_translations_from_json(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
            'status' => 'published',
        ]);

        $json = json_encode([
            'model_type' => TestPost::class,
            'model_id' => $post->id,
            'locale' => 'it',
            'translations' => [
                'title' => 'Titolo Importato',
                'content' => 'Contenuto Importato',
            ],
        ]);

        $result = $this->service->importTranslations('json', $json);

        $post->refresh();
        $this->assertEquals('Titolo Importato', $post->translate('title', 'it'));
        $this->assertEquals('Contenuto Importato', $post->translate('content', 'it'));
        $this->assertArrayHasKey('imported', $result);
        $this->assertEquals(2, $result['imported']);
    }

    public function test_get_translation_stats_returns_statistics(): void
    {
        TestPost::create(['title' => 'Post 1', 'status' => 'published'])
            ->setTranslation('title', 'it', 'Articolo 1');

        TestPost::create(['title' => 'Post 2', 'status' => 'published'])
            ->setTranslation('title', 'it', 'Articolo 2');

        $stats = $this->service->getTranslationStats();

        $this->assertArrayHasKey('total_translations', $stats);
        $this->assertArrayHasKey('by_locale', $stats);
        $this->assertGreaterThanOrEqual(2, $stats['total_translations']);
        $this->assertArrayHasKey('it', $stats['by_locale']);
    }

    public function test_import_rejects_unauthorized_model_class(): void
    {
        // Attempt to import with a non-whitelisted model class
        $json = json_encode([
            'model_type' => 'App\\Models\\User', // Not a content model
            'model_id' => 1,
            'locale' => 'it',
            'translations' => [
                'name' => 'Hacker',
            ],
        ]);

        $result = $this->service->importTranslations('json', $json);

        // Should fail with error message
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Invalid or unauthorized model class', $result['errors'][0]);
        $this->assertEquals(0, $result['imported']);
    }

    public function test_import_accepts_whitelisted_model_class(): void
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'status' => 'published',
        ]);

        // TestPost should be in the whitelist (it's in app/CMS/ContentModels)
        $json = json_encode([
            'model_type' => TestPost::class,
            'model_id' => $post->id,
            'locale' => 'it',
            'translations' => [
                'title' => 'Titolo Sicuro',
            ],
        ]);

        $result = $this->service->importTranslations('json', $json);

        // Should succeed
        $this->assertEmpty($result['errors'], 'Should not have any errors');
        $this->assertEquals(1, $result['imported']);
        $post->refresh();
        $this->assertEquals('Titolo Sicuro', $post->translate('title', 'it'));
    }
}
