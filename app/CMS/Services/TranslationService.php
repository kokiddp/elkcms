<?php

namespace App\CMS\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    /**
     * Translate a model with multiple field values for a specific locale.
     *
     * @param  Model  $model
     * @param  array  $translations  Array of field => value pairs
     * @param  string  $locale
     * @return void
     */
    public function translateModel(Model $model, array $translations, string $locale): void
    {
        foreach ($translations as $field => $value) {
            if ($model->isTranslatable($field)) {
                $model->setTranslation($field, $locale, $value);
            }
        }

        // Clear cache for this model's translations
        $this->clearTranslationCache($model, $locale);
    }

    /**
     * Get all translations for a model.
     *
     * @param  Model  $model
     * @param  string|null  $locale  If null, returns all locales
     * @return array
     */
    public function getModelTranslations(Model $model, string $locale = null): array
    {
        if ($locale) {
            // Get translations for specific locale
            $translations = [];
            foreach ($model->getTranslatableFields() as $field) {
                $translations[$field] = $model->translate($field, $locale);
            }

            return $translations;
        }

        // Get all translations for all locales
        return $model->getAllTranslations();
    }

    /**
     * Copy translations from one model to another.
     *
     * @param  Model  $source
     * @param  Model  $target
     * @param  string  $locale
     * @return void
     */
    public function copyTranslations(Model $source, Model $target, string $locale): void
    {
        $translations = $this->getModelTranslations($source, $locale);

        foreach ($translations as $field => $value) {
            if ($target->isTranslatable($field)) {
                $target->setTranslation($field, $locale, $value);
            }
        }

        $this->clearTranslationCache($target, $locale);
    }

    /**
     * Bulk translate multiple models from one locale to another.
     *
     * @param  Collection  $models
     * @param  string  $sourceLocale
     * @param  string  $targetLocale
     * @param  callable|null  $translator  Function to translate values
     * @return int  Number of models translated
     */
    public function bulkTranslate(
        Collection $models,
        string $sourceLocale,
        string $targetLocale,
        callable $translator = null
    ): int {
        $count = 0;

        DB::transaction(function () use ($models, $sourceLocale, $targetLocale, $translator, &$count) {
            foreach ($models as $model) {
                $sourceTranslations = $this->getModelTranslations($model, $sourceLocale);

                foreach ($sourceTranslations as $field => $value) {
                    $translatedValue = $translator ? $translator($value) : $value;
                    $model->setTranslation($field, $targetLocale, $translatedValue);
                }

                $count++;
            }
        });

        return $count;
    }

    /**
     * Get translation progress for a model.
     *
     * @param  Model  $model
     * @return array  Array of locale => [percentage, total, translated]
     */
    public function getTranslationProgress(Model $model): array
    {
        $progress = [];
        $totalFields = count($model->getTranslatableFields());

        if ($totalFields === 0) {
            return $progress;
        }

        $supportedLocales = array_keys(config('languages.supported', []));

        foreach ($supportedLocales as $locale) {
            if ($locale === config('languages.default', 'en')) {
                continue; // Skip default locale
            }

            $translatedFields = 0;

            foreach ($model->getTranslatableFields() as $field) {
                if ($model->hasTranslation($field, $locale)) {
                    $translatedFields++;
                }
            }

            $percentage = $totalFields > 0
                ? round(($translatedFields / $totalFields) * 100)
                : 0;

            $progress[$locale] = [
                'percentage' => $percentage,
                'total' => $totalFields,
                'translated' => $translatedFields,
            ];
        }

        return $progress;
    }

    /**
     * Get models missing translations for a specific locale.
     *
     * @param  string  $locale
     * @return Collection
     */
    public function getMissingTranslations(string $locale): Collection
    {
        // Get all models with translations trait
        $modelClass = config('cms.models.namespace', 'App\\CMS\\ContentModels').'\\TestPost';

        if (! class_exists($modelClass)) {
            return collect();
        }

        $allModels = $modelClass::all();
        $missing = collect();

        foreach ($allModels as $model) {
            $progress = $this->getTranslationProgress($model);

            // Include if locale progress < 100% OR locale not in progress (no translations at all)
            if (! isset($progress[$locale]) || $progress[$locale]['percentage'] < 100) {
                $missing->push($model);
            }
        }

        return $missing;
    }

    /**
     * Cache translations for a model and locale.
     *
     * @param  Model  $model
     * @param  string  $locale
     * @return void
     */
    public function cacheTranslations(Model $model, string $locale): void
    {
        if (! config('cms.cache.enabled', true)) {
            return;
        }

        $cacheKey = $this->getTranslationCacheKey($model, $locale);
        $translations = $this->getModelTranslations($model, $locale);
        $ttl = config('cms.cache.translations_ttl', 7200);

        Cache::put($cacheKey, $translations, $ttl);
    }

    /**
     * Warm translation cache for a locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function warmTranslationCache(string $locale): void
    {
        $modelClass = config('cms.models.namespace', 'App\\CMS\\ContentModels').'\\TestPost';

        if (! class_exists($modelClass)) {
            return;
        }

        $models = $modelClass::all();

        foreach ($models as $model) {
            $this->cacheTranslations($model, $locale);
        }
    }

    /**
     * Clear translation cache for a model.
     *
     * @param  Model|null  $model  If null, clears all translation cache
     * @param  string|null  $locale  If null, clears for all locales
     * @return void
     */
    public function clearTranslationCache(Model $model = null, string $locale = null): void
    {
        if (! config('cms.cache.enabled', true)) {
            return;
        }

        if ($model && $locale) {
            $cacheKey = $this->getTranslationCacheKey($model, $locale);
            Cache::forget($cacheKey);
        } elseif ($model) {
            // Clear for all locales of this model
            $supportedLocales = array_keys(config('languages.supported', []));
            foreach ($supportedLocales as $loc) {
                Cache::forget($this->getTranslationCacheKey($model, $loc));
            }
        } else {
            // Clear all translation cache
            Cache::flush(); // Or use tags if available
        }
    }

    /**
     * Validate translation data.
     *
     * @param  array  $translations
     * @return array  Array of field => error message
     */
    public function validateTranslations(array $translations): array
    {
        $errors = [];

        foreach ($translations as $field => $value) {
            if (! is_scalar($value) && ! is_null($value)) {
                $errors[$field] = 'Translation value must be a scalar type or null';
            }
        }

        return $errors;
    }

    /**
     * Check if a model field can be translated to a locale.
     *
     * @param  Model  $model
     * @param  string  $field
     * @param  string  $locale
     * @return bool
     */
    public function canTranslate(Model $model, string $field, string $locale): bool
    {
        // Check if field is translatable
        if (! $model->isTranslatable($field)) {
            return false;
        }

        // Check if locale is supported
        $supportedLocales = array_keys(config('languages.supported', []));

        return in_array($locale, $supportedLocales);
    }

    /**
     * Export translations for a model.
     *
     * @param  Model  $model
     * @param  string  $format  json, csv, array
     * @return mixed
     */
    public function exportTranslations(Model $model, string $format = 'json'): mixed
    {
        $data = $this->getModelTranslations($model);

        return match ($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'array' => $data,
            default => json_encode($data, JSON_PRETTY_PRINT),
        };
    }

    /**
     * Import translations from data.
     *
     * @param  string  $format  json, csv
     * @param  mixed  $data
     * @return array  Result with counts
     */
    public function importTranslations(string $format, mixed $data): array
    {
        $result = [
            'imported' => 0,
            'errors' => [],
        ];

        try {
            $importData = match ($format) {
                'json' => json_decode($data, true),
                default => json_decode($data, true),
            };

            if (! $importData) {
                $result['errors'][] = 'Invalid format';

                return $result;
            }

            $modelClass = $importData['model_type'] ?? null;
            $modelId = $importData['model_id'] ?? null;
            $locale = $importData['locale'] ?? null;
            $translations = $importData['translations'] ?? [];

            if (! $modelClass || ! $modelId || ! $locale) {
                $result['errors'][] = 'Missing required fields';

                return $result;
            }

            $model = $modelClass::find($modelId);

            if (! $model) {
                $result['errors'][] = 'Model not found';

                return $result;
            }

            foreach ($translations as $field => $value) {
                if ($model->isTranslatable($field)) {
                    $model->setTranslation($field, $locale, $value);
                    $result['imported']++;
                }
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get global translation statistics.
     *
     * @return array
     */
    public function getTranslationStats(): array
    {
        $stats = [
            'total_translations' => Translation::count(),
            'by_locale' => [],
            'by_model' => [],
        ];

        // Count by locale
        $byLocale = Translation::select('locale', DB::raw('COUNT(*) as count'))
            ->groupBy('locale')
            ->get();

        foreach ($byLocale as $item) {
            $stats['by_locale'][$item->locale] = $item->count;
        }

        // Count by model type
        $byModel = Translation::select('translatable_type', DB::raw('COUNT(*) as count'))
            ->groupBy('translatable_type')
            ->get();

        foreach ($byModel as $item) {
            $stats['by_model'][$item->translatable_type] = $item->count;
        }

        return $stats;
    }

    /**
     * Get cache key for model translations.
     *
     * @param  Model  $model
     * @param  string  $locale
     * @return string
     */
    protected function getTranslationCacheKey(Model $model, string $locale): string
    {
        return sprintf(
            'cms_translations_%s_%d_%s',
            class_basename($model),
            $model->id,
            $locale
        );
    }
}
