<?php

namespace Tests\Unit\CMS\Traits;

use App\CMS\ContentModels\TestPost;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    use DatabaseMigrations;

    protected TestPost $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new TestPost();
    }

    public function test_can_get_translatable_fields(): void
    {
        $fields = $this->model->getTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertContains('title', $fields);
        $this->assertContains('content', $fields);
    }

    public function test_is_translatable_returns_true_for_translatable_field(): void
    {
        $this->assertTrue($this->model->isTranslatable('title'));
    }

    public function test_is_translatable_returns_false_for_non_translatable_field(): void
    {
        $this->assertFalse($this->model->isTranslatable('published_at'));
    }

    public function test_translate_returns_original_value_for_default_locale(): void
    {
        $this->model->setAttribute('title', 'Test Title');

        $result = $this->model->translate('title', 'en');

        $this->assertEquals('Test Title', $result);
    }

    public function test_set_translation_for_default_locale_sets_attribute(): void
    {
        $this->model->setTranslation('title', 'en', 'New Title');

        $this->assertEquals('New Title', $this->model->getAttribute('title'));
    }

    public function test_set_translation_throws_exception_for_non_translatable_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Field 'published_at' is not translatable");

        $this->model->setTranslation('published_at', 'it', 'some value');
    }

    public function test_has_translation_returns_true_for_default_locale_with_value(): void
    {
        $this->model->setAttribute('title', 'Test Title');

        $this->assertTrue($this->model->hasTranslation('title', 'en'));
    }

    public function test_has_translation_returns_false_for_non_translatable_field(): void
    {
        $this->assertFalse($this->model->hasTranslation('published_at', 'en'));
    }

    public function test_get_translations_returns_array_with_default_locale(): void
    {
        $this->model->setAttribute('title', 'Test Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save(); // Save model before accessing translations

        $translations = $this->model->getTranslations('title');

        $this->assertIsArray($translations);
        $this->assertArrayHasKey('en', $translations);
        $this->assertEquals('Test Title', $translations['en']);
    }

    public function test_get_translations_returns_empty_array_for_non_translatable_field(): void
    {
        $translations = $this->model->getTranslations('published_at');

        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }

    public function test_get_all_translations_returns_nested_array(): void
    {
        $this->model->setAttribute('title', 'Test Title');
        $this->model->setAttribute('content', 'Test Content');
        $this->model->setAttribute('status', 'published');
        $this->model->save(); // Save model before accessing translations

        $allTranslations = $this->model->getAllTranslations();

        $this->assertIsArray($allTranslations);
        $this->assertArrayHasKey('title', $allTranslations);
        $this->assertArrayHasKey('content', $allTranslations);
        $this->assertEquals('Test Title', $allTranslations['title']['en']);
        $this->assertEquals('Test Content', $allTranslations['content']['en']);
    }

    public function test_delete_translations_returns_self(): void
    {
        $this->model->setAttribute('title', 'Test Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save(); // Save model before deleting translations

        $result = $this->model->deleteTranslations('title');

        $this->assertSame($this->model, $result);
    }

    public function test_translatable_fields_are_cached(): void
    {
        $fields1 = $this->model->getTranslatableFields();
        $fields2 = $this->model->getTranslatableFields();

        $this->assertSame($fields1, $fields2);
    }

    public function test_can_set_and_retrieve_translation_for_non_default_locale(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        // Set Italian translation
        $this->model->setTranslation('title', 'it', 'Titolo Italiano');

        // Retrieve Italian translation
        $translation = $this->model->translate('title', 'it');

        $this->assertEquals('Titolo Italiano', $translation);
    }

    public function test_translate_falls_back_to_default_locale_when_translation_missing(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        // Try to get German translation (doesn't exist)
        $translation = $this->model->translate('title', 'de');

        // Should fall back to English
        $this->assertEquals('English Title', $translation);
    }

    public function test_has_translation_returns_true_for_existing_translation(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->model->setTranslation('title', 'it', 'Titolo Italiano');

        $this->assertTrue($this->model->hasTranslation('title', 'it'));
    }

    public function test_has_translation_returns_false_for_missing_translation(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->assertFalse($this->model->hasTranslation('title', 'de'));
    }

    public function test_get_translations_includes_all_locales(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->model->setTranslation('title', 'it', 'Titolo Italiano');
        $this->model->setTranslation('title', 'de', 'Deutscher Titel');

        $translations = $this->model->getTranslations('title');

        $this->assertCount(3, $translations); // en, it, de
        $this->assertEquals('English Title', $translations['en']);
        $this->assertEquals('Titolo Italiano', $translations['it']);
        $this->assertEquals('Deutscher Titel', $translations['de']);
    }

    public function test_can_update_existing_translation(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        // Set initial Italian translation
        $this->model->setTranslation('title', 'it', 'Titolo Italiano');

        // Update it
        $this->model->setTranslation('title', 'it', 'Nuovo Titolo');

        $translation = $this->model->translate('title', 'it');

        $this->assertEquals('Nuovo Titolo', $translation);
    }

    public function test_delete_specific_field_translations(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('content', 'English Content');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->model->setTranslation('title', 'it', 'Titolo Italiano');
        $this->model->setTranslation('content', 'it', 'Contenuto Italiano');

        // Delete only title translations
        $this->model->deleteTranslations('title');

        $this->assertFalse($this->model->hasTranslation('title', 'it'));
        $this->assertTrue($this->model->hasTranslation('content', 'it'));
    }

    public function test_delete_all_translations(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('content', 'English Content');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->model->setTranslation('title', 'it', 'Titolo Italiano');
        $this->model->setTranslation('content', 'it', 'Contenuto Italiano');

        // Delete all translations
        $this->model->deleteTranslations();

        $this->assertFalse($this->model->hasTranslation('title', 'it'));
        $this->assertFalse($this->model->hasTranslation('content', 'it'));
    }

    public function test_set_translation_throws_exception_for_unsaved_model(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Model must be saved before adding translations');

        // Try to set translation without saving first
        $this->model->setTranslation('title', 'it', 'Titolo Italiano');
    }

    public function test_translations_are_deleted_when_model_is_deleted(): void
    {
        $this->model->setAttribute('title', 'English Title');
        $this->model->setAttribute('status', 'published');
        $this->model->save();

        $this->model->setTranslation('title', 'it', 'Titolo Italiano');
        $modelId = $this->model->id;

        // Delete the model
        $this->model->delete();

        // Check that translations were deleted
        $translationCount = \App\Models\Translation::where('translatable_id', $modelId)
            ->where('translatable_type', \App\CMS\ContentModels\TestPost::class)
            ->count();

        $this->assertEquals(0, $translationCount);
    }
}
