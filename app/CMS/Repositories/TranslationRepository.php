<?php

namespace App\CMS\Repositories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TranslationRepository
{
    /**
     * Get all translations for a model.
     */
    public function getByModel(Model $model): Collection
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->get();
    }

    /**
     * Get translations for a model filtered by locale.
     */
    public function getByModelAndLocale(Model $model, string $locale): Collection
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('locale', $locale)
            ->get();
    }

    /**
     * Get translations for a model filtered by field.
     */
    public function getByModelAndField(Model $model, string $field): Collection
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('field', $field)
            ->get();
    }

    /**
     * Find a specific translation.
     */
    public function findTranslation(Model $model, string $field, string $locale): ?Translation
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('field', $field)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Get all translations for a specific locale.
     */
    public function getByLocale(string $locale): Collection
    {
        return Translation::query()
            ->where('locale', $locale)
            ->get();
    }

    /**
     * Get all translations for a specific model type.
     */
    public function getByModelType(string $modelClass): Collection
    {
        return Translation::query()
            ->where('translatable_type', $modelClass)
            ->get();
    }

    /**
     * Count translations grouped by locale.
     */
    public function countByLocale(): array
    {
        $results = Translation::query()
            ->select('locale', DB::raw('COUNT(*) as count'))
            ->groupBy('locale')
            ->get();

        return $results->pluck('count', 'locale')->toArray();
    }

    /**
     * Count translations grouped by model type.
     */
    public function countByModelType(): array
    {
        $results = Translation::query()
            ->select('translatable_type', DB::raw('COUNT(*) as count'))
            ->groupBy('translatable_type')
            ->get();

        return $results->pluck('count', 'translatable_type')->toArray();
    }

    /**
     * Delete all translations for a model.
     */
    public function deleteByModel(Model $model): int
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->delete();
    }

    /**
     * Delete translations for a model and locale.
     */
    public function deleteByModelAndLocale(Model $model, string $locale): int
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('locale', $locale)
            ->delete();
    }

    /**
     * Delete translations for a model and field.
     */
    public function deleteByModelAndField(Model $model, string $field): int
    {
        return Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('field', $field)
            ->delete();
    }

    /**
     * Bulk update translations for a model.
     *
     * @param  Model  $model
     * @param  array  $updates  Array of ['field' => '', 'locale' => '', 'value' => '']
     * @return int  Number of translations updated
     */
    public function bulkUpdate(Model $model, array $updates): int
    {
        $count = 0;

        DB::transaction(function () use ($model, $updates, &$count) {
            foreach ($updates as $update) {
                $translation = $this->findTranslation(
                    $model,
                    $update['field'],
                    $update['locale']
                );

                if ($translation) {
                    $translation->update(['value' => $update['value']]);
                    $count++;
                } else {
                    // Create if doesn't exist
                    Translation::create([
                        'translatable_type' => get_class($model),
                        'translatable_id' => $model->id,
                        'field' => $update['field'],
                        'locale' => $update['locale'],
                        'value' => $update['value'],
                    ]);
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Search translations by value (case-insensitive).
     *
     * @param  string  $searchTerm
     * @param  string|null  $locale  Optional locale filter
     * @return Collection
     */
    public function searchByValue(string $searchTerm, string $locale = null): Collection
    {
        $query = Translation::query()
            ->where('value', 'LIKE', "%{$searchTerm}%");

        if ($locale) {
            $query->where('locale', $locale);
        }

        return $query->get();
    }
}
