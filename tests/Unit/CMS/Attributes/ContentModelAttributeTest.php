<?php

namespace Tests\Unit\CMS\Attributes;

use App\CMS\Attributes\ContentModel;
use PHPUnit\Framework\TestCase;

class ContentModelAttributeTest extends TestCase
{
    public function test_can_create_content_model_attribute(): void
    {
        $attr = new ContentModel(
            label: 'Test Posts',
            icon: 'edit',
            supports: ['translations', 'seo']
        );

        $this->assertEquals('Test Posts', $attr->label);
        $this->assertEquals('edit', $attr->icon);
        $this->assertEquals(['translations', 'seo'], $attr->supports);
    }

    public function test_content_model_has_default_values(): void
    {
        $attr = new ContentModel(label: 'Pages');

        $this->assertEquals('file', $attr->icon);
        $this->assertEquals([], $attr->supports);
        $this->assertNull($attr->description);
        $this->assertTrue($attr->public);
        $this->assertNull($attr->routePrefix);
    }

    public function test_supports_method_checks_feature(): void
    {
        $attr = new ContentModel(
            label: 'Posts',
            supports: ['translations', 'seo', 'media']
        );

        $this->assertTrue($attr->supports('translations'));
        $this->assertTrue($attr->supports('seo'));
        $this->assertTrue($attr->supports('media'));
        $this->assertFalse($attr->supports('blocks'));
    }

    public function test_get_supports_returns_all_features(): void
    {
        $features = ['translations', 'seo', 'media'];
        $attr = new ContentModel(
            label: 'Posts',
            supports: $features
        );

        $this->assertEquals($features, $attr->getSupports());
    }

    public function test_can_set_custom_route_prefix(): void
    {
        $attr = new ContentModel(
            label: 'Articles',
            routePrefix: 'blog-posts'
        );

        $this->assertEquals('blog-posts', $attr->routePrefix);
    }

    public function test_can_set_private_content(): void
    {
        $attr = new ContentModel(
            label: 'Private Notes',
            public: false
        );

        $this->assertFalse($attr->public);
    }
}
