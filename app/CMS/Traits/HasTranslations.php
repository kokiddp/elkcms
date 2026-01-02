<?php

namespace App\CMS\Traits;

use App\CMS\Reflection\ModelScanner;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Cached translatable fields
     */
    protected ?array $translatableFieldsCache = null;

    /**
     * Get translated value for a field
     *
     * @param  string  $field  Field name
     * @param  string|null  $locale  Locale code (defaults to current locale)
     * @return mixed Translated value or original field value
     */
    public function translate(string $field, string $locale = null): mixed
    {
        $locale = $locale ?? App::getLocale();

        // If requesting default locale or field is not translatable, return original
        if ($locale === config('languages.default', 'en') || ! $this->isTranslatable($field)) {
            return $this->getAttribute($field);
        }

        // TODO: This will be implemented when Translation model exists (Phase 3)
        // For now, return the original field value
        return $this->getAttribute($field);
    }

    /**
     * Set translation for a field
     *
     * @param  string  $field  Field name
     * @param  string  $locale  Locale code
     * @param  mixed  $value  Translation value
     * @return self
     */
    public function setTranslation(string $field, string $locale, mixed $value): self
    {
        if (! $this->isTranslatable($field)) {
            throw new \InvalidArgumentException("Field '{$field}' is not translatable");
        }

        // TODO: This will be implemented when Translation model exists (Phase 3)
        // For now, just set the attribute if it's the default locale
        if ($locale === config('languages.default', 'en')) {
            $this->setAttribute($field, $value);
        }

        return $this;
    }

    /**
     * Check if translation exists for a field
     *
     * @param  string  $field  Field name
     * @param  string  $locale  Locale code
     * @return bool
     */
    public function hasTranslation(string $field, string $locale): bool
    {
        if (! $this->isTranslatable($field)) {
            return false;
        }

        // If default locale, check if attribute exists
        if ($locale === config('languages.default', 'en')) {
            return ! is_null($this->getAttribute($field));
        }

        // TODO: This will be implemented when Translation model exists (Phase 3)
        return false;
    }

    /**
     * Get all translations for a field
     *
     * @param  string  $field  Field name
     * @return array Array of locale => value
     */
    public function getTranslations(string $field): array
    {
        if (! $this->isTranslatable($field)) {
            return [];
        }

        // TODO: This will be implemented when Translation model exists (Phase 3)
        // For now, return only the default locale value
        return [
            config('languages.default', 'en') => $this->getAttribute($field),
        ];
    }

    /**
     * Get all translations for all translatable fields
     *
     * @return array Nested array of field => [locale => value]
     */
    public function getAllTranslations(): array
    {
        $translations = [];

        foreach ($this->getTranslatableFields() as $field) {
            $translations[$field] = $this->getTranslations($field);
        }

        return $translations;
    }

    /**
     * Delete translations for a field or all fields
     *
     * @param  string|null  $field  Field name (null = all fields)
     * @return self
     */
    public function deleteTranslations(string $field = null): self
    {
        // TODO: This will be implemented when Translation model exists (Phase 3)
        return $this;
    }

    /**
     * Check if a field is translatable
     *
     * @param  string  $field  Field name
     * @return bool
     */
    public function isTranslatable(string $field): bool
    {
        return in_array($field, $this->getTranslatableFields());
    }

    /**
     * Get all translatable field names for this model
     *
     * @return array Array of field names
     */
    public function getTranslatableFields(): array
    {
        if ($this->translatableFieldsCache !== null) {
            return $this->translatableFieldsCache;
        }

        $scanner = new ModelScanner();
        $modelData = $scanner->scan(static::class);

        $translatableFields = [];

        foreach ($modelData['fields'] as $fieldName => $fieldData) {
            if ($fieldData['translatable'] ?? false) {
                $translatableFields[] = $fieldName;
            }
        }

        $this->translatableFieldsCache = $translatableFields;

        return $translatableFields;
    }
}
