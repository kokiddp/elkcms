<?php

namespace Tests\Unit\CMS\Traits;

use App\CMS\ContentModels\BaseContent;
use Tests\TestCase;

class HasSlugTest extends TestCase
{
    protected BaseContent $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create anonymous class for testing
        $this->model = new class extends BaseContent
        {
            protected $table = 'test_slugs';

            protected $fillable = ['title', 'slug', 'status'];

            // Override slugExists to avoid database queries in unit tests
            protected function slugExists(string $slug): bool
            {
                return in_array($slug, ['existing-slug', 'test-title']);
            }
        };
    }

    public function test_generate_slug_from_title(): void
    {
        $this->model->title = 'Test Title';

        $slug = $this->model->generateSlug();

        $this->assertEquals('test-title-1', $slug); // -1 because slugExists returns true for 'test-title'
    }

    public function test_generate_slug_converts_to_lowercase(): void
    {
        $this->model->title = 'UPPERCASE TITLE';

        $slug = $this->model->generateSlug();

        $this->assertEquals('uppercase-title', $slug);
    }

    public function test_generate_slug_replaces_spaces_with_hyphens(): void
    {
        $this->model->title = 'Multiple Word Title';

        $slug = $this->model->generateSlug();

        $this->assertEquals('multiple-word-title', $slug);
    }

    public function test_generate_slug_removes_special_characters(): void
    {
        $this->model->title = 'Title with @#$% Special!';

        $slug = $this->model->generateSlug();

        $this->assertStringNotContainsString('@', $slug);
        $this->assertStringNotContainsString('#', $slug);
        $this->assertStringNotContainsString('$', $slug);
        $this->assertStringNotContainsString('!', $slug);
    }

    public function test_generate_slug_from_custom_source(): void
    {
        $slug = $this->model->generateSlug('Custom Source Text');

        $this->assertEquals('custom-source-text', $slug);
    }

    public function test_generate_slug_creates_random_if_no_source(): void
    {
        $slug = $this->model->generateSlug();

        $this->assertStringStartsWith('content-', $slug);
        $this->assertEquals(16, strlen($slug)); // 'content-' + 8 random chars
    }

    public function test_ensure_unique_slug_appends_counter(): void
    {
        $slug = $this->model->ensureUniqueSlug('existing-slug');

        $this->assertEquals('existing-slug-1', $slug);
    }

    public function test_ensure_unique_slug_increments_counter(): void
    {
        // Create a model that returns true for existing-slug, existing-slug-1, existing-slug-2
        $model = new class extends BaseContent
        {
            protected $table = 'test_slugs';

            protected function slugExists(string $slug): bool
            {
                return in_array($slug, ['existing-slug', 'existing-slug-1', 'existing-slug-2']);
            }
        };

        $slug = $model->ensureUniqueSlug('existing-slug');

        $this->assertEquals('existing-slug-3', $slug);
    }

    public function test_ensure_unique_slug_returns_original_if_unique(): void
    {
        $slug = $this->model->ensureUniqueSlug('unique-slug');

        $this->assertEquals('unique-slug', $slug);
    }

    public function test_get_slug_source_returns_title_by_default(): void
    {
        $source = $this->model->getSlugSource();

        $this->assertEquals('title', $source);
    }

    public function test_get_slug_source_returns_custom_source_if_defined(): void
    {
        $model = new class extends BaseContent
        {
            protected $table = 'test_slugs';

            protected $slugSource = 'custom_field';
        };

        $source = $model->getSlugSource();

        $this->assertEquals('custom_field', $source);
    }

    public function test_slug_should_be_unique_returns_true_by_default(): void
    {
        $this->assertTrue($this->model->slugShouldBeUnique());
    }

    public function test_slug_should_be_unique_returns_custom_value_if_defined(): void
    {
        $model = new class extends BaseContent
        {
            protected $table = 'test_slugs';

            protected $slugUnique = false;
        };

        $this->assertFalse($model->slugShouldBeUnique());
    }
}
