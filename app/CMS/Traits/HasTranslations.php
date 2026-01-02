<?php

namespace App\CMS\Traits;

use App\CMS\Reflection\ModelScanner;
use App\Models\Translation;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Cached translatable fields
     */
    protected ?array $translatableFieldsCache = null;

    /**
     * Boot the HasTranslations trait
     */
    protected static function bootHasTranslations(): void
    {
        // Delete translations when model is deleted
        static::deleting(function ($model) {
            $model->translations()->delete();
        });
    }

    /**
     * Get all translations for this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

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

        // Try to get translation from database
        $translation = $this->translations()
            ->forLocaleAndField($locale, $field)
            ->first();

        if ($translation) {
            return $translation->value;
        }

        // Fallback to default locale value
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

        // Validate locale is supported
        $supportedLocales = array_keys(config('languages.supported', []));
        if (! in_array($locale, $supportedLocales)) {
            throw new \InvalidArgumentException("Locale '{$locale}' is not supported");
        }

        // Validate value type (must be scalar or null)
        if (! is_scalar($value) && ! is_null($value)) {
            throw new \InvalidArgumentException('Translation value must be a scalar type or null');
        }

        // If default locale, set the attribute directly
        if ($locale === config('languages.default', 'en')) {
            $this->setAttribute($field, $value);
            return $this;
        }

        // Make sure model exists in database before adding translations
        if (! $this->exists) {
            throw new \RuntimeException('Model must be saved before adding translations');
        }

        // Update or create translation
        $this->translations()->updateOrCreate(
            [
                'locale' => $locale,
                'field' => $field,
            ],
            [
                'value' => $value,
            ]
        );

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

        // Check if translation exists in database
        return $this->translations()
            ->forLocaleAndField($locale, $field)
            ->exists();
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

        $translations = [
            config('languages.default', 'en') => $this->getAttribute($field),
        ];

        if (! $this->exists) {
            return $translations;
        }

        // Check if translations are already eager loaded (prevents N+1 queries)
        if ($this->relationLoaded('translations')) {
            $dbTranslations = $this->translations->where('field', $field);
        } else {
            // Lazy load only this field's translations with minimal columns
            $dbTranslations = $this->translations()
                ->forField($field)
                ->select('locale', 'field', 'value')
                ->get();
        }

        foreach ($dbTranslations as $translation) {
            $translations[$translation->locale] = $translation->value;
        }

        return $translations;
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
        if ($field === null) {
            // Delete all translations
            $this->translations()->delete();
        } else {
            // Delete translations for specific field
            $this->translations()->forField($field)->delete();
        }

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
