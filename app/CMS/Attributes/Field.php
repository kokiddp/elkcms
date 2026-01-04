<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    /**
     * Create a new Field attribute instance.
     *
     * @param  string  $type  Field type (string, text, pagebuilder, integer, boolean, date, datetime, image, file, json, etc.)
     * @param  string|null  $label  Human-readable label for the field (defaults to property name)
     * @param  bool  $required  Whether this field is required
     * @param  bool  $translatable  Whether this field supports translations
     * @param  int|null  $maxLength  Maximum length for string fields
     * @param  int|null  $minLength  Minimum length for string/text fields
     * @param  mixed  $default  Default value for the field
     * @param  array  $validation  Additional Laravel validation rules
     * @param  string|null  $helpText  Help text to display in admin forms
     * @param  string|null  $placeholder  Placeholder text for input fields
     * @param  array  $options  Options for select/radio fields (key => label)
     * @param  bool  $unique  Whether this field must be unique
     * @param  bool  $indexed  Whether to create a database index
     * @param  bool  $nullable  Whether NULL values are allowed in database
     * @param  bool  $fillable  Whether this field is mass-assignable (default: true)
     */
    public function __construct(
        public string $type,
        public ?string $label = null,
        public bool $required = false,
        public bool $translatable = false,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public mixed $default = null,
        public array $validation = [],
        public ?string $helpText = null,
        public ?string $placeholder = null,
        public array $options = [],
        public bool $unique = false,
        public bool $indexed = false,
        public bool $nullable = true,
        public bool $fillable = true,
    ) {
    }

    /**
     * Get the full validation rules for this field.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific validation
        match ($this->type) {
            'string' => $rules[] = 'string',
            'text' => $rules[] = 'string',
            'pagebuilder' => $rules[] = 'string',
            'integer' => $rules[] = 'integer',
            'boolean' => $rules[] = 'boolean',
            'date' => $rules[] = 'date',
            'datetime' => $rules[] = 'date',
            'email' => $rules[] = 'email',
            'url' => $rules[] = 'url',
            'image' => $rules[] = 'image',
            'file' => $rules[] = 'file',
            'json' => $rules[] = 'json',
            default => null,
        };

        // Length validation
        if ($this->maxLength !== null && in_array($this->type, ['string', 'text'])) {
            $rules[] = 'max:'.$this->maxLength;
        }

        if ($this->minLength !== null && in_array($this->type, ['string', 'text'])) {
            $rules[] = 'min:'.$this->minLength;
        }

        // Unique validation
        if ($this->unique) {
            $rules[] = 'unique';
        }

        // Merge custom validation rules
        if (! empty($this->validation)) {
            $rules = array_merge($rules, $this->validation);
        }

        return $rules;
    }

    /**
     * Get the database column type for this field.
     *
     * @return string
     */
    public function getDatabaseType(): string
    {
        return match ($this->type) {
            'string' => $this->maxLength ? "string:{$this->maxLength}" : 'string',
            'text' => 'text',
            'pagebuilder' => 'text',
            'integer' => 'integer',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'decimal' => 'decimal',
            'float' => 'float',
            'json' => 'json',
            'image', 'file' => 'string', // Store file path
            default => 'string',
        };
    }

    /**
     * Check if this field should be cast to a specific type.
     *
     * @return string|null
     */
    public function getCastType(): ?string
    {
        return match ($this->type) {
            'boolean' => 'boolean',
            'integer' => 'integer',
            'float' => 'float',
            'decimal' => 'decimal',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'json' => 'array',
            default => null,
        };
    }
}
