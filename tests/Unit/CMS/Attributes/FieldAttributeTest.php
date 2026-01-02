<?php

namespace Tests\Unit\CMS\Attributes;

use App\CMS\Attributes\Field;
use PHPUnit\Framework\TestCase;

class FieldAttributeTest extends TestCase
{
    public function test_can_create_field_attribute(): void
    {
        $field = new Field(
            type: 'string',
            label: 'Title',
            required: true,
            maxLength: 200
        );

        $this->assertEquals('string', $field->type);
        $this->assertEquals('Title', $field->label);
        $this->assertTrue($field->required);
        $this->assertEquals(200, $field->maxLength);
    }

    public function test_field_has_default_values(): void
    {
        $field = new Field(type: 'text');

        $this->assertNull($field->label);
        $this->assertFalse($field->required);
        $this->assertFalse($field->translatable);
        $this->assertTrue($field->nullable);
        $this->assertTrue($field->fillable);
        $this->assertFalse($field->unique);
        $this->assertFalse($field->indexed);
    }

    public function test_generates_validation_rules_for_required_string(): void
    {
        $field = new Field(
            type: 'string',
            required: true,
            maxLength: 100,
            minLength: 5
        );

        $rules = $field->getValidationRules();

        $this->assertContains('required', $rules);
        $this->assertContains('string', $rules);
        $this->assertContains('max:100', $rules);
        $this->assertContains('min:5', $rules);
    }

    public function test_generates_validation_rules_for_nullable_field(): void
    {
        $field = new Field(
            type: 'text',
            required: false
        );

        $rules = $field->getValidationRules();

        $this->assertContains('nullable', $rules);
        $this->assertContains('string', $rules);
    }

    public function test_generates_validation_rules_for_email(): void
    {
        $field = new Field(type: 'email', required: true);

        $rules = $field->getValidationRules();

        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
    }

    public function test_generates_validation_rules_with_unique(): void
    {
        $field = new Field(
            type: 'string',
            unique: true
        );

        $rules = $field->getValidationRules();

        $this->assertContains('unique', $rules);
    }

    public function test_merges_custom_validation_rules(): void
    {
        $field = new Field(
            type: 'string',
            validation: ['alpha_dash', 'lowercase']
        );

        $rules = $field->getValidationRules();

        $this->assertContains('alpha_dash', $rules);
        $this->assertContains('lowercase', $rules);
    }

    public function test_get_database_type_for_string(): void
    {
        $field = new Field(type: 'string', maxLength: 200);
        $this->assertEquals('string:200', $field->getDatabaseType());

        $field = new Field(type: 'string');
        $this->assertEquals('string', $field->getDatabaseType());
    }

    public function test_get_database_type_for_various_types(): void
    {
        $this->assertEquals('text', (new Field(type: 'text'))->getDatabaseType());
        $this->assertEquals('integer', (new Field(type: 'integer'))->getDatabaseType());
        $this->assertEquals('boolean', (new Field(type: 'boolean'))->getDatabaseType());
        $this->assertEquals('date', (new Field(type: 'date'))->getDatabaseType());
        $this->assertEquals('datetime', (new Field(type: 'datetime'))->getDatabaseType());
        $this->assertEquals('json', (new Field(type: 'json'))->getDatabaseType());
        $this->assertEquals('string', (new Field(type: 'image'))->getDatabaseType());
        $this->assertEquals('string', (new Field(type: 'file'))->getDatabaseType());
    }

    public function test_get_cast_type_for_various_types(): void
    {
        $this->assertEquals('boolean', (new Field(type: 'boolean'))->getCastType());
        $this->assertEquals('integer', (new Field(type: 'integer'))->getCastType());
        $this->assertEquals('float', (new Field(type: 'float'))->getCastType());
        $this->assertEquals('date', (new Field(type: 'date'))->getCastType());
        $this->assertEquals('datetime', (new Field(type: 'datetime'))->getCastType());
        $this->assertEquals('array', (new Field(type: 'json'))->getCastType());
        $this->assertNull((new Field(type: 'string'))->getCastType());
    }
}
