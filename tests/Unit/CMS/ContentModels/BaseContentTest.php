<?php

namespace Tests\Unit\CMS\ContentModels;

use App\CMS\ContentModels\BaseContent;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class BaseContentTest extends TestCase
{
    protected BaseContent $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create anonymous class for testing
        $this->model = new class extends BaseContent
        {
            protected $table = 'test_contents';

            protected $fillable = ['title', 'content', 'status', 'slug'];
        };
    }

    public function test_extends_eloquent_model(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function test_uses_has_translations_trait(): void
    {
        $this->assertTrue(method_exists($this->model, 'translate'));
        $this->assertTrue(method_exists($this->model, 'setTranslation'));
        $this->assertTrue(method_exists($this->model, 'getTranslatableFields'));
    }

    public function test_uses_has_slug_trait(): void
    {
        $this->assertTrue(method_exists($this->model, 'generateSlug'));
        $this->assertTrue(method_exists($this->model, 'ensureUniqueSlug'));
    }

    public function test_uses_has_seo_trait(): void
    {
        $this->assertTrue(method_exists($this->model, 'getSEOTitle'));
        $this->assertTrue(method_exists($this->model, 'getSEODescription'));
        $this->assertTrue(method_exists($this->model, 'getSchemaMarkup'));
    }

    public function test_uses_optimized_queries_trait(): void
    {
        $this->assertTrue(method_exists($this->model, 'getCacheKey'));
        $this->assertTrue(method_exists($this->model, 'flushCache'));
    }

    public function test_has_status_constants(): void
    {
        $this->assertEquals('draft', BaseContent::STATUS_DRAFT);
        $this->assertEquals('published', BaseContent::STATUS_PUBLISHED);
        $this->assertEquals('archived', BaseContent::STATUS_ARCHIVED);
    }

    public function test_is_published_returns_true_for_published_status(): void
    {
        $this->model->status = BaseContent::STATUS_PUBLISHED;
        $this->assertTrue($this->model->isPublished());
    }

    public function test_is_published_returns_false_for_draft_status(): void
    {
        $this->model->status = BaseContent::STATUS_DRAFT;
        $this->assertFalse($this->model->isPublished());
    }

    public function test_is_draft_returns_true_for_draft_status(): void
    {
        $this->model->status = BaseContent::STATUS_DRAFT;
        $this->assertTrue($this->model->isDraft());
    }

    public function test_is_draft_returns_false_for_published_status(): void
    {
        $this->model->status = BaseContent::STATUS_PUBLISHED;
        $this->assertFalse($this->model->isDraft());
    }

    public function test_is_archived_returns_true_for_archived_status(): void
    {
        $this->model->status = BaseContent::STATUS_ARCHIVED;
        $this->assertTrue($this->model->isArchived());
    }

    public function test_is_archived_returns_false_for_published_status(): void
    {
        $this->model->status = BaseContent::STATUS_PUBLISHED;
        $this->assertFalse($this->model->isArchived());
    }

    public function test_guarded_includes_id(): void
    {
        $this->assertContains('id', $this->model->getGuarded());
    }
}
