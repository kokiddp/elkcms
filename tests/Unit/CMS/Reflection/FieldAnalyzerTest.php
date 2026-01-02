<?php

namespace Tests\Unit\CMS\Reflection;

use App\CMS\Reflection\FieldAnalyzer;
use PHPUnit\Framework\TestCase;

class FieldAnalyzerTest extends TestCase
{
    protected FieldAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new FieldAnalyzer();
    }

    public function test_analyzes_field_definition(): void
    {
        $field = [
            'name' => 'title',
            'type' => 'string',
            'required' => true,
            'translatable' => true,
            'nullable' => false,
            'fillable' => true,
            'unique' => false,
            'indexed' => false,
        ];

        $result = $this->analyzer->analyze($field);

        $this->assertTrue($result['isTranslatable']);
        $this->assertTrue($result['isRequired']);
        $this->assertFalse($result['isUnique']);
        $this->assertFalse($result['isIndexed']);
        $this->assertFalse($result['isNullable']);
        $this->assertTrue($result['isFillable']);
    }

    public function test_get_form_field_type_for_string(): void
    {
        $field = ['type' => 'string', 'name' => 'title'];
        $result = $this->analyzer->analyze($field);
        $this->assertEquals('text', $result['formFieldType']);
    }

    public function test_get_form_field_type_for_text(): void
    {
        $field = ['type' => 'text', 'name' => 'content'];
        $result = $this->analyzer->analyze($field);
        $this->assertEquals('textarea', $result['formFieldType']);
    }

    public function test_get_form_field_type_for_integer(): void
    {
        $field = ['type' => 'integer', 'name' => 'count'];
        $result = $this->analyzer->analyze($field);
        $this->assertEquals('number', $result['formFieldType']);
    }

    public function test_get_form_field_type_for_boolean(): void
    {
        $field = ['type' => 'boolean', 'name' => 'active'];
        $result = $this->analyzer->analyze($field);
        $this->assertEquals('checkbox', $result['formFieldType']);
    }

    public function test_get_form_field_type_for_image(): void
    {
        $field = ['type' => 'image', 'name' => 'photo'];
        $result = $this->analyzer->analyze($field);
        $this->assertEquals('file-image', $result['formFieldType']);
    }

    public function test_get_migration_method_for_string_with_max_length(): void
    {
        $field = [
            'name' => 'title',
            'type' => 'string',
            'maxLength' => 200,
            'nullable' => true,
            'unique' => false,
            'indexed' => false,
        ];

        $result = $this->analyzer->analyze($field);
        $this->assertEquals("\$table->string('title', 200)->nullable();", $result['migrationMethod']);
    }

    public function test_get_migration_method_for_string_without_max_length(): void
    {
        $field = [
            'name' => 'slug',
            'type' => 'string',
            'nullable' => true,
            'unique' => false,
            'indexed' => false,
        ];

        $result = $this->analyzer->analyze($field);
        $this->assertEquals("\$table->string('slug')->nullable();", $result['migrationMethod']);
    }

    public function test_get_migration_method_with_unique(): void
    {
        $field = [
            'name' => 'email',
            'type' => 'string',
            'nullable' => false,
            'unique' => true,
            'indexed' => false,
        ];

        $result = $this->analyzer->analyze($field);
        $this->assertStringContainsString('->unique()', $result['migrationMethod']);
    }

    public function test_get_migration_method_with_index(): void
    {
        $field = [
            'name' => 'status',
            'type' => 'string',
            'nullable' => false,
            'unique' => false,
            'indexed' => true,
        ];

        $result = $this->analyzer->analyze($field);
        $this->assertStringContainsString('->index()', $result['migrationMethod']);
    }

    public function test_get_migration_method_with_default_value(): void
    {
        $field = [
            'name' => 'status',
            'type' => 'string',
            'default' => 'draft',
            'nullable' => false,
            'unique' => false,
            'indexed' => false,
        ];

        $result = $this->analyzer->analyze($field);
        $this->assertStringContainsString("->default('draft')", $result['migrationMethod']);
    }

    public function test_get_validation_string(): void
    {
        $field = [
            'validation' => ['required', 'string', 'max:200'],
        ];

        $string = $this->analyzer->getValidationString($field);
        $this->assertEquals('required|string|max:200', $string);
    }

    public function test_should_be_fillable_returns_true_by_default(): void
    {
        $field = ['name' => 'title'];
        $this->assertTrue($this->analyzer->shouldBeFillable($field));
    }

    public function test_should_be_fillable_respects_explicit_false(): void
    {
        $field = ['name' => 'id', 'fillable' => false];
        $this->assertFalse($this->analyzer->shouldBeFillable($field));
    }

    public function test_should_not_be_fillable_for_timestamps(): void
    {
        $this->assertFalse($this->analyzer->shouldBeFillable(['name' => 'created_at']));
        $this->assertFalse($this->analyzer->shouldBeFillable(['name' => 'updated_at']));
        $this->assertFalse($this->analyzer->shouldBeFillable(['name' => 'deleted_at']));
    }

    public function test_should_be_cast_when_cast_type_present(): void
    {
        $field = ['castType' => 'boolean'];
        $this->assertTrue($this->analyzer->shouldBeCast($field));
    }

    public function test_should_not_be_cast_when_cast_type_null(): void
    {
        $field = ['castType' => null];
        $this->assertFalse($this->analyzer->shouldBeCast($field));
    }

    public function test_get_cast_type(): void
    {
        $field = ['castType' => 'datetime'];
        $this->assertEquals('datetime', $this->analyzer->getCastType($field));
    }
}
