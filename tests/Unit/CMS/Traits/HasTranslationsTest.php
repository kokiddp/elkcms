<?php

namespace Tests\Unit\CMS\Traits;

use App\CMS\ContentModels\TestPost;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
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

        $allTranslations = $this->model->getAllTranslations();

        $this->assertIsArray($allTranslations);
        $this->assertArrayHasKey('title', $allTranslations);
        $this->assertArrayHasKey('content', $allTranslations);
        $this->assertEquals('Test Title', $allTranslations['title']['en']);
        $this->assertEquals('Test Content', $allTranslations['content']['en']);
    }

    public function test_delete_translations_returns_self(): void
    {
        $result = $this->model->deleteTranslations('title');

        $this->assertSame($this->model, $result);
    }

    public function test_translatable_fields_are_cached(): void
    {
        $fields1 = $this->model->getTranslatableFields();
        $fields2 = $this->model->getTranslatableFields();

        $this->assertSame($fields1, $fields2);
    }
}
