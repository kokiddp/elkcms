<?php

namespace App\CMS\Builders;

use App\CMS\Attributes\Field;
use App\CMS\Attributes\Relationship;
use App\CMS\Reflection\ModelScanner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * FormBuilder - Dynamic form generation from content model attributes
 *
 * Automatically generates admin forms from Field attributes defined on content models.
 * Supports all field types, validation rules, translation tabs, and relationships.
 */
class FormBuilder
{
    public function __construct(
        protected ModelScanner $scanner
    ) {}

    /**
     * Build complete form HTML for a content model
     *
     * @param string $modelClass Fully qualified model class name
     * @param Model|null $instance Existing model instance for edit mode
     * @param array $options Additional form options
     * @return string Complete form HTML
     */
    public function buildForm(string $modelClass, ?Model $instance = null, array $options = []): string
    {
        $metadata = $this->scanner->scan($modelClass);
        $fields = $metadata['fields'] ?? [];

        $html = '';

        foreach ($fields as $fieldName => $fieldMeta) {
            $value = $instance?->{$fieldName} ?? old($fieldName);
            $html .= $this->buildField($fieldName, $value, $fieldMeta);
        }

        return $html;
    }

    /**
     * Build a single field HTML
     *
     * @param string $name Field name
     * @param mixed $value Field value
     * @param array $fieldMeta Field metadata from scanner
     * @return string Field HTML
     */
    public function buildField(string $name, mixed $value, array $fieldMeta): string
    {
        $type = $fieldMeta['type'] ?? 'string';

        return match ($type) {
            'string', 'email', 'url' => $this->renderTextField($name, $value, $fieldMeta),
            'text' => $this->renderTextareaField($name, $value, $fieldMeta),
            'integer', 'float', 'decimal', 'number' => $this->renderNumberField($name, $value, $fieldMeta),
            'boolean' => $this->renderBooleanField($name, $value, $fieldMeta),
            'select' => $this->renderSelectField($name, $value, $fieldMeta),
            'image', 'file' => $this->renderFileField($name, $value, $fieldMeta),
            'date' => $this->renderDateField($name, $value, $fieldMeta),
            'datetime' => $this->renderDateTimeField($name, $value, $fieldMeta),
            'wysiwyg' => $this->renderWysiwygField($name, $value, $fieldMeta),
            'json' => $this->renderJsonField($name, $value, $fieldMeta),
            'pagebuilder' => $this->renderPageBuilderField($name, $value, $fieldMeta),
            default => $this->renderTextField($name, $value, $fieldMeta),
        };
    }

    /**
     * Extract validation rules from model metadata
     *
     * @param string $modelClass Fully qualified model class name
     * @return array Laravel validation rules
     */
    public function buildValidationRules(string $modelClass): array
    {
        $metadata = $this->scanner->scan($modelClass);
        $fields = $metadata['fields'] ?? [];
        $rules = [];

        foreach ($fields as $fieldName => $fieldMeta) {
            if (!empty($fieldMeta['validation'])) {
                $rules[$fieldName] = $fieldMeta['validation'];
            } else {
                // Build rules from field metadata
                $fieldRules = [];

                if ($fieldMeta['required'] ?? false) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Type-based validation
                $fieldRules = array_merge($fieldRules, $this->getTypeValidation($fieldMeta));

                if (!empty($fieldRules)) {
                    $rules[$fieldName] = implode('|', $fieldRules);
                }
            }
        }

        return $rules;
    }

    /**
     * Get validation rules based on field type
     *
     * @param array $fieldMeta Field metadata
     * @return array Validation rules
     */
    protected function getTypeValidation(array $fieldMeta): array
    {
        $type = $fieldMeta['type'] ?? 'string';
        $rules = [];

        switch ($type) {
            case 'string':
            case 'text':
                $rules[] = 'string';
                if (isset($fieldMeta['maxLength'])) {
                    $rules[] = "max:{$fieldMeta['maxLength']}";
                }
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'integer':
                $rules[] = 'integer';
                break;

            case 'float':
            case 'decimal':
                $rules[] = 'numeric';
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'datetime':
                $rules[] = 'date';
                break;

            case 'image':
                $rules[] = 'image';
                $rules[] = 'max:5120'; // 5MB default
                break;

            case 'file':
                $rules[] = 'file';
                $rules[] = 'max:10240'; // 10MB default
                break;

            case 'json':
                $rules[] = 'json';
                break;
        }

        return $rules;
    }

    /**
     * Build translation tabs for multilingual fields
     *
     * @param string $modelClass Fully qualified model class name
     * @param Model|null $instance Existing model instance
     * @param array $locales Available locales
     * @return string Translation tabs HTML
     */
    public function buildTranslationTabs(string $modelClass, ?Model $instance = null, array $locales = []): string
    {
        if (empty($locales)) {
            $locales = array_keys(config('languages.supported', ['en' => 'English']));
        }

        $metadata = $this->scanner->scan($modelClass);
        $translatableFields = array_filter(
            $metadata['fields'] ?? [],
            fn($field) => ($field['translatable'] ?? false)
        );

        if (empty($translatableFields)) {
            return '';
        }

        $html = '<div class="translation-tabs mb-4">';
        $html .= '<ul class="nav nav-tabs" role="tablist">';

        foreach ($locales as $locale) {
            $active = $locale === 'en' ? 'active' : '';
            $languageConfig = config("languages.supported.{$locale}", []);
            $languageName = is_array($languageConfig) ? ($languageConfig['name'] ?? strtoupper($locale)) : $languageConfig;
            $html .= sprintf(
                '<li class="nav-item" role="presentation"><button class="nav-link %s" data-bs-toggle="tab" data-bs-target="#translation-%s" type="button">%s</button></li>',
                $active,
                $locale,
                $languageName
            );
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content border border-top-0 p-3">';

        foreach ($locales as $locale) {
            $active = $locale === 'en' ? 'show active' : '';
            $html .= sprintf('<div class="tab-pane fade %s" id="translation-%s">', $active, $locale);

            foreach ($translatableFields as $fieldName => $fieldMeta) {
                $translationValue = $instance?->getTranslation($fieldName, $locale) ?? '';
                $translationFieldName = "translations[{$locale}][{$fieldName}]";

                $html .= $this->buildField($translationFieldName, $translationValue, $fieldMeta);
            }

            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Render text input field
     */
    protected function renderTextField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $placeholder = $fieldMeta['placeholder'] ?? '';
        $helpText = $fieldMeta['helpText'] ?? '';
        $maxLength = $fieldMeta['maxLength'] ?? '';
        $type = match ($fieldMeta['type'] ?? 'string') {
            'email' => 'email',
            'url' => 'url',
            default => 'text',
        };

        $errorInfo = $this->getFieldErrors($name);

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<input type="%s" class="form-control%s" id="%s" name="%s" value="%s" %s %s %s>',
            $type,
            $errorInfo['errorClass'],
            $id,
            $name,
            htmlspecialchars($value ?? '', ENT_QUOTES),
            $required,
            $placeholder ? "placeholder=\"{$placeholder}\"" : '',
            $maxLength ? "maxlength=\"{$maxLength}\"" : ''
        );

        $html .= $this->renderFieldError($name);

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render textarea field
     */
    protected function renderTextareaField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $placeholder = $fieldMeta['placeholder'] ?? '';
        $helpText = $fieldMeta['helpText'] ?? '';
        $rows = $fieldMeta['rows'] ?? 5;

        $errorInfo = $this->getFieldErrors($name);

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<textarea class="form-control%s" id="%s" name="%s" rows="%d" %s %s>%s</textarea>',
            $errorInfo['errorClass'],
            $id,
            $name,
            $rows,
            $required,
            $placeholder ? "placeholder=\"{$placeholder}\"" : '',
            htmlspecialchars($value ?? '', ENT_QUOTES)
        );

        $html .= $this->renderFieldError($name);

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render number input field
     */
    protected function renderNumberField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $placeholder = $fieldMeta['placeholder'] ?? '';
        $helpText = $fieldMeta['helpText'] ?? '';
        $min = isset($fieldMeta['min']) ? "min=\"{$fieldMeta['min']}\"" : '';
        $max = isset($fieldMeta['max']) ? "max=\"{$fieldMeta['max']}\"" : '';
        $step = $fieldMeta['type'] === 'integer' ? '1' : '0.01';

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<input type="number" class="form-control" id="%s" name="%s" value="%s" step="%s" %s %s %s %s>',
            $id,
            $name,
            $value ?? '',
            $step,
            $required,
            $placeholder ? "placeholder=\"{$placeholder}\"" : '',
            $min,
            $max
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render boolean checkbox field
     */
    protected function renderBooleanField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $helpText = $fieldMeta['helpText'] ?? '';
        $checked = $value ? 'checked' : '';

        $html = '<div class="form-group mb-3">';
        $html .= '<div class="form-check">';
        $html .= sprintf(
            '<input type="checkbox" class="form-check-input" id="%s" name="%s" value="1" %s>',
            $id,
            $name,
            $checked
        );
        $html .= sprintf('<label class="form-check-label" for="%s">%s</label>', $id, $label);
        $html .= '</div>';

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render select dropdown field
     */
    protected function renderSelectField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';
        $options = $fieldMeta['options'] ?? [];

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf('<select class="form-select" id="%s" name="%s" %s>', $id, $name, $required);

        $html .= '<option value="">-- Select --</option>';
        foreach ($options as $optionValue => $optionLabel) {
            $selected = $value == $optionValue ? 'selected' : '';
            $html .= sprintf(
                '<option value="%s" %s>%s</option>',
                htmlspecialchars($optionValue, ENT_QUOTES),
                $selected,
                htmlspecialchars($optionLabel, ENT_QUOTES)
            );
        }

        $html .= '</select>';

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render file/image upload field
     */
    protected function renderFileField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';
        $accept = $fieldMeta['type'] === 'image' ? 'accept="image/*"' : '';

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );

        // Show current file if exists
        if ($value) {
            $html .= '<div class="mb-2">';
            if ($fieldMeta['type'] === 'image') {
                $html .= sprintf('<img src="%s" alt="Current image" class="img-thumbnail" style="max-width: 200px;">', $value);
            } else {
                $html .= sprintf('<div class="alert alert-info">Current file: %s</div>', basename($value));
            }
            $html .= '</div>';
        }

        $html .= sprintf(
            '<input type="file" class="form-control" id="%s" name="%s" %s %s>',
            $id,
            $name,
            $required && !$value ? 'required' : '', // Only required if no existing value
            $accept
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render date picker field
     */
    protected function renderDateField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';

        // Format date value
        $formattedValue = '';
        if ($value) {
            if ($value instanceof \DateTime) {
                $formattedValue = $value->format('Y-m-d');
            } elseif (is_string($value)) {
                $formattedValue = date('Y-m-d', strtotime($value));
            }
        }

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<input type="date" class="form-control" id="%s" name="%s" value="%s" %s>',
            $id,
            $name,
            $formattedValue,
            $required
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render datetime picker field
     */
    protected function renderDateTimeField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';

        // Format datetime value
        $formattedValue = '';
        if ($value) {
            if ($value instanceof \DateTime) {
                $formattedValue = $value->format('Y-m-d\TH:i');
            } elseif (is_string($value)) {
                $formattedValue = date('Y-m-d\TH:i', strtotime($value));
            }
        }

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<input type="datetime-local" class="form-control" id="%s" name="%s" value="%s" %s>',
            $id,
            $name,
            $formattedValue,
            $required
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render WYSIWYG editor field
     */
    protected function renderWysiwygField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<textarea class="form-control wysiwyg-editor" id="%s" name="%s" rows="10" %s>%s</textarea>',
            $id,
            $name,
            $required,
            htmlspecialchars($value ?? '', ENT_QUOTES)
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        // Note: Actual WYSIWYG initialization would be done via JavaScript
        $html .= sprintf('<script>/* Initialize WYSIWYG for #%s */</script>', $id);

        $html .= '</div>';

        return $html;
    }

    /**
     * Render JSON editor field
     */
    protected function renderJsonField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';

        // Format JSON value
        $formattedValue = '';
        if ($value) {
            if (is_array($value) || is_object($value)) {
                $formattedValue = json_encode($value, JSON_PRETTY_PRINT);
            } else {
                $formattedValue = $value;
            }
        }

        $html = '<div class="form-group mb-3">';
        $html .= sprintf('<label for="%s" class="form-label">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );
        $html .= sprintf(
            '<textarea class="form-control font-monospace" id="%s" name="%s" rows="10" %s>%s</textarea>',
            $id,
            $name,
            $required,
            htmlspecialchars($formattedValue, ENT_QUOTES)
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render page builder field (GrapesJS)
     */
    protected function renderPageBuilderField(string $name, mixed $value, array $fieldMeta): string
    {
        $id = $this->getFieldId($name);
        $label = $fieldMeta['label'] ?? ucfirst($name);
        $required = ($fieldMeta['required'] ?? false) ? 'required' : '';
        $helpText = $fieldMeta['helpText'] ?? '';

        $html = '<div class="form-group mb-4">';
        $html .= sprintf('<label for="%s" class="form-label fw-bold">%s%s</label>',
            $id,
            $label,
            $required ? ' <span class="text-danger">*</span>' : ''
        );

        // GrapesJS editor container
        $grapesjsId = 'gjs-' . Str::slug($name);
        $html .= sprintf('<div id="%s" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden; min-height: 500px;"></div>', $grapesjsId);

        // Hidden textarea to store HTML
        $html .= sprintf(
            '<textarea id="%s" name="%s" data-field-type="pagebuilder" class="d-none" %s>%s</textarea>',
            $id,
            $name,
            $required,
            htmlspecialchars($value ?? '', ENT_QUOTES)
        );

        if ($helpText) {
            $html .= sprintf('<div class="form-text">%s</div>', $helpText);
        }

        // Note: GrapesJS initialization would be done via JavaScript
        $html .= sprintf(
            '<script>/* Initialize GrapesJS for #%s, sync with #%s */</script>',
            $grapesjsId,
            $id
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate field ID from field name
     */
    protected function getFieldId(string $name): string
    {
        return 'field-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    }

    /**
     * Get validation error information for a field
     *
     * @param string $name Field name
     * @return array ['hasError' => bool, 'errorClass' => string, 'errorMessage' => string|null]
     */
    protected function getFieldErrors(string $name): array
    {
        $errors = session()->get('errors');
        $hasError = $errors && $errors->has($name);

        return [
            'hasError' => $hasError,
            'errorClass' => $hasError ? ' is-invalid' : '',
            'errorMessage' => $hasError ? $errors->first($name) : null,
        ];
    }

    /**
     * Render validation error message for a field
     *
     * @param string $name Field name
     * @return string Error HTML or empty string
     */
    protected function renderFieldError(string $name): string
    {
        $errorInfo = $this->getFieldErrors($name);

        if (!$errorInfo['hasError']) {
            return '';
        }

        return sprintf(
            '<div class="invalid-feedback d-block">%s</div>',
            htmlspecialchars($errorInfo['errorMessage'])
        );
    }
}
