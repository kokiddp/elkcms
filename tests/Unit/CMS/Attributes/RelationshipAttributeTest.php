<?php

namespace Tests\Unit\CMS\Attributes;

use App\CMS\Attributes\Relationship;
use PHPUnit\Framework\TestCase;

class RelationshipAttributeTest extends TestCase
{
    public function test_can_create_relationship_attribute(): void
    {
        $rel = new Relationship(
            type: 'belongsTo',
            model: 'App\Models\Category',
            foreignKey: 'category_id'
        );

        $this->assertEquals('belongsTo', $rel->type);
        $this->assertEquals('App\Models\Category', $rel->model);
        $this->assertEquals('category_id', $rel->foreignKey);
    }

    public function test_relationship_has_default_values(): void
    {
        $rel = new Relationship(
            type: 'hasMany',
            model: 'App\Models\Comment'
        );

        $this->assertNull($rel->foreignKey);
        $this->assertNull($rel->localKey);
        $this->assertNull($rel->pivotTable);
        $this->assertNull($rel->label);
        $this->assertFalse($rel->eager);
        $this->assertEquals([], $rel->pivotFields);
    }

    public function test_is_to_many_relationship(): void
    {
        $this->assertTrue((new Relationship(type: 'hasMany', model: 'Test'))->isToMany());
        $this->assertTrue((new Relationship(type: 'belongsToMany', model: 'Test'))->isToMany());
        $this->assertTrue((new Relationship(type: 'morphMany', model: 'Test'))->isToMany());
        $this->assertTrue((new Relationship(type: 'hasManyThrough', model: 'Test'))->isToMany());
    }

    public function test_is_not_to_many_relationship(): void
    {
        $this->assertFalse((new Relationship(type: 'hasOne', model: 'Test'))->isToMany());
        $this->assertFalse((new Relationship(type: 'belongsTo', model: 'Test'))->isToMany());
        $this->assertFalse((new Relationship(type: 'morphOne', model: 'Test'))->isToMany());
    }

    public function test_is_to_one_relationship(): void
    {
        $this->assertTrue((new Relationship(type: 'hasOne', model: 'Test'))->isToOne());
        $this->assertTrue((new Relationship(type: 'belongsTo', model: 'Test'))->isToOne());
        $this->assertTrue((new Relationship(type: 'morphOne', model: 'Test'))->isToOne());
    }

    public function test_is_not_to_one_relationship(): void
    {
        $this->assertFalse((new Relationship(type: 'hasMany', model: 'Test'))->isToOne());
        $this->assertFalse((new Relationship(type: 'belongsToMany', model: 'Test'))->isToOne());
    }

    public function test_requires_pivot_for_belongs_to_many(): void
    {
        $rel = new Relationship(type: 'belongsToMany', model: 'App\Models\Tag');
        $this->assertTrue($rel->requiresPivot());
    }

    public function test_does_not_require_pivot_for_other_types(): void
    {
        $this->assertFalse((new Relationship(type: 'hasMany', model: 'Test'))->requiresPivot());
        $this->assertFalse((new Relationship(type: 'belongsTo', model: 'Test'))->requiresPivot());
        $this->assertFalse((new Relationship(type: 'hasOne', model: 'Test'))->requiresPivot());
    }

    public function test_get_eloquent_method_returns_type(): void
    {
        $rel = new Relationship(type: 'hasMany', model: 'Test');
        $this->assertEquals('hasMany', $rel->getEloquentMethod());
    }

    public function test_can_set_pivot_table_and_fields(): void
    {
        $rel = new Relationship(
            type: 'belongsToMany',
            model: 'App\Models\Tag',
            pivotTable: 'post_tag',
            pivotFields: [
                ['name' => 'order', 'type' => 'integer'],
                ['name' => 'featured', 'type' => 'boolean'],
            ]
        );

        $this->assertEquals('post_tag', $rel->pivotTable);
        $this->assertCount(2, $rel->pivotFields);
    }
}
