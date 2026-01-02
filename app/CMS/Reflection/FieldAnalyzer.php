<?php

namespace App\CMS\Reflection;

use App\CMS\Attributes\Field;

class FieldAnalyzer
{
    /**
     * Analyze a field definition and return enhanced metadata.
     *
     * @param  array  $fieldDefinition  Field definition from ModelScanner
     * @return array Enhanced field metadata
     */
    public function analyze(array $fieldDefinition): array
    {
        return [
            ...$fieldDefinition,
            'isTranslatable' => $this->isTranslatable($fieldDefinition),
            'isRequired' => $this->isRequired($fieldDefinition),
            'isUnique' => $this->isUnique($fieldDefinition),
            'isIndexed' => $this->isIndexed($fieldDefinition),
            'isNullable' => $this->isNullable($fieldDefinition),
            'isFillable' => $this->isFillable($fieldDefinition),
            'formFieldType' => $this->getFormFieldType($fieldDefinition),
            'migrationMethod' => $this->getMigrationMethod($fieldDefinition),
        ];
    }

    /**
     * Check if field is translatable.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isTranslatable(array $fieldDefinition): bool
    {
        return $fieldDefinition['translatable'] ?? false;
    }

    /**
     * Check if field is required.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isRequired(array $fieldDefinition): bool
    {
        return $fieldDefinition['required'] ?? false;
    }

    /**
     * Check if field is unique.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isUnique(array $fieldDefinition): bool
    {
        return $fieldDefinition['unique'] ?? false;
    }

    /**
     * Check if field is indexed.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isIndexed(array $fieldDefinition): bool
    {
        return $fieldDefinition['indexed'] ?? false;
    }

    /**
     * Check if field is nullable.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isNullable(array $fieldDefinition): bool
    {
        return $fieldDefinition['nullable'] ?? true;
    }

    /**
     * Check if field is fillable.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    protected function isFillable(array $fieldDefinition): bool
    {
        return $fieldDefinition['fillable'] ?? true;
    }

    /**
     * Determine the form field type for admin UI.
     *
     * @param  array  $fieldDefinition
     * @return string
     */
    protected function getFormFieldType(array $fieldDefinition): string
    {
        $type = $fieldDefinition['type'] ?? 'string';

        return match ($type) {
            'string' => 'text',
            'text' => 'textarea',
            'integer' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'email' => 'email',
            'url' => 'url',
            'image' => 'file-image',
            'file' => 'file',
            'json' => 'json-editor',
            'select' => 'select',
            'radio' => 'radio',
            'wysiwyg' => 'wysiwyg',
            default => 'text',
        };
    }

    /**
     * Get the Laravel migration method for this field type.
     *
     * @param  array  $fieldDefinition
     * @return string
     */
    protected function getMigrationMethod(array $fieldDefinition): string
    {
        $type = $fieldDefinition['type'] ?? 'string';
        $name = $fieldDefinition['name'] ?? 'field';

        $method = match ($type) {
            'string' => isset($fieldDefinition['maxLength']) && $fieldDefinition['maxLength']
                ? "\$table->string('{$name}', {$fieldDefinition['maxLength']})"
                : "\$table->string('{$name}')",
            'text' => "\$table->text('{$name}')",
            'integer' => "\$table->integer('{$name}')",
            'boolean' => "\$table->boolean('{$name}')",
            'date' => "\$table->date('{$name}')",
            'datetime' => "\$table->datetime('{$name}')",
            'timestamp' => "\$table->timestamp('{$name}')",
            'decimal' => "\$table->decimal('{$name}')",
            'float' => "\$table->float('{$name}')",
            'json' => "\$table->json('{$name}')",
            'image', 'file' => "\$table->string('{$name}')",
            default => "\$table->string('{$name}')",
        };

        // Add nullable modifier
        if ($this->isNullable($fieldDefinition)) {
            $method .= '->nullable()';
        }

        // Add unique modifier
        if ($this->isUnique($fieldDefinition)) {
            $method .= '->unique()';
        }

        // Add index modifier
        if ($this->isIndexed($fieldDefinition)) {
            $method .= '->index()';
        }

        // Add default value
        if (isset($fieldDefinition['default']) && $fieldDefinition['default'] !== null) {
            $default = is_string($fieldDefinition['default'])
                ? "'{$fieldDefinition['default']}'"
                : var_export($fieldDefinition['default'], true);
            $method .= "->default({$default})";
        }

        return $method.';';
    }

    /**
     * Get validation rules formatted for Laravel request validation.
     *
     * @param  array  $fieldDefinition
     * @return string
     */
    public function getValidationString(array $fieldDefinition): string
    {
        $rules = $fieldDefinition['validation'] ?? [];

        return implode('|', $rules);
    }

    /**
     * Determine if this field should be included in fillable array.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    public function shouldBeFillable(array $fieldDefinition): bool
    {
        // Don't include in fillable if explicitly set to false
        if (isset($fieldDefinition['fillable']) && $fieldDefinition['fillable'] === false) {
            return false;
        }

        // Don't include timestamps in fillable
        $name = $fieldDefinition['name'] ?? '';
        if (in_array($name, ['created_at', 'updated_at', 'deleted_at'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if this field should be included in casts array.
     *
     * @param  array  $fieldDefinition
     * @return bool
     */
    public function shouldBeCast(array $fieldDefinition): bool
    {
        return isset($fieldDefinition['castType']) && $fieldDefinition['castType'] !== null;
    }

    /**
     * Get the cast type for Eloquent model.
     *
     * @param  array  $fieldDefinition
     * @return string|null
     */
    public function getCastType(array $fieldDefinition): ?string
    {
        return $fieldDefinition['castType'] ?? null;
    }
}
