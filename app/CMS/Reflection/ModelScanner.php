<?php

namespace App\CMS\Reflection;

use App\CMS\Attributes\ContentModel;
use App\CMS\Attributes\Field;
use App\CMS\Attributes\Relationship;
use App\CMS\Attributes\SEO;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionProperty;

class ModelScanner
{
    /**
     * Scan a content model class and extract all attribute metadata.
     *
     * @param  string  $modelClass  Fully qualified class name
     * @param  bool  $useCache  Whether to use cached results (default: true)
     * @return array Model definition with all metadata
     *
     * @throws \ReflectionException
     */
    public function scan(string $modelClass, bool $useCache = true): array
    {
        $cacheEnabled = config('cms.cache.enabled', true);
        $cacheKey = 'cms_model_scan_'.md5($modelClass);

        if ($useCache && $cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $reflection = new ReflectionClass($modelClass);

        $definition = [
            'class' => $modelClass,
            'shortName' => $reflection->getShortName(),
            'namespace' => $reflection->getNamespaceName(),
            'contentModel' => $this->extractContentModelAttribute($reflection),
            'seo' => $this->extractSEOAttribute($reflection),
            'fields' => $this->extractFieldAttributes($reflection),
            'relationships' => $this->extractRelationshipAttributes($reflection),
        ];

        // Cache the result for 1 hour
        if ($useCache && $cacheEnabled) {
            Cache::put($cacheKey, $definition, now()->addHour());
        }

        return $definition;
    }

    /**
     * Extract ContentModel attribute from class.
     *
     * @param  ReflectionClass  $reflection
     * @return array|null
     */
    protected function extractContentModelAttribute(ReflectionClass $reflection): ?array
    {
        $attributes = $reflection->getAttributes(ContentModel::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var ContentModel $contentModel */
        $contentModel = $attributes[0]->newInstance();

        return [
            'label' => $contentModel->label,
            'icon' => $contentModel->icon,
            'supports' => $contentModel->supports,
            'description' => $contentModel->description,
            'public' => $contentModel->public,
            'routePrefix' => $contentModel->routePrefix,
        ];
    }

    /**
     * Extract SEO attribute from class.
     *
     * @param  ReflectionClass  $reflection
     * @return array|null
     */
    protected function extractSEOAttribute(ReflectionClass $reflection): ?array
    {
        $attributes = $reflection->getAttributes(SEO::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var SEO $seo */
        $seo = $attributes[0]->newInstance();

        return [
            'schemaType' => $seo->schemaType,
            'schemaTypeUrl' => $seo->getSchemaTypeUrl(),
            'schemaProperties' => $seo->schemaProperties,
            'sitemapPriority' => $seo->getSitemapPriorityFloat(),
            'sitemapChangeFreq' => $seo->sitemapChangeFreq,
            'includedInSitemap' => $seo->includedInSitemap,
            'metaFields' => $seo->metaFields,
            'enableBreadcrumbs' => $seo->enableBreadcrumbs,
        ];
    }

    /**
     * Extract all Field attributes from class properties.
     *
     * @param  ReflectionClass  $reflection
     * @return array
     */
    protected function extractFieldAttributes(ReflectionClass $reflection): array
    {
        $fields = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(Field::class);

            if (empty($attributes)) {
                continue;
            }

            /** @var Field $field */
            $field = $attributes[0]->newInstance();

            $fields[$property->getName()] = [
                'name' => $property->getName(),
                'type' => $field->type,
                'label' => $field->label ?? ucfirst($property->getName()),
                'required' => $field->required,
                'translatable' => $field->translatable,
                'maxLength' => $field->maxLength,
                'minLength' => $field->minLength,
                'default' => $field->default,
                'validation' => $field->getValidationRules(),
                'helpText' => $field->helpText,
                'placeholder' => $field->placeholder,
                'options' => $field->options,
                'unique' => $field->unique,
                'indexed' => $field->indexed,
                'nullable' => $field->nullable,
                'fillable' => $field->fillable,
                'databaseType' => $field->getDatabaseType(),
                'castType' => $field->getCastType(),
            ];
        }

        return $fields;
    }

    /**
     * Extract all Relationship attributes from class properties.
     *
     * @param  ReflectionClass  $reflection
     * @return array
     */
    protected function extractRelationshipAttributes(ReflectionClass $reflection): array
    {
        $relationships = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(Relationship::class);

            if (empty($attributes)) {
                continue;
            }

            /** @var Relationship $relationship */
            $relationship = $attributes[0]->newInstance();

            $relationships[$property->getName()] = [
                'name' => $property->getName(),
                'type' => $relationship->type,
                'model' => $relationship->model,
                'foreignKey' => $relationship->foreignKey,
                'localKey' => $relationship->localKey,
                'pivotTable' => $relationship->pivotTable,
                'label' => $relationship->label ?? ucfirst($property->getName()),
                'eager' => $relationship->eager,
                'pivotFields' => $relationship->pivotFields,
                'isToMany' => $relationship->isToMany(),
                'isToOne' => $relationship->isToOne(),
                'requiresPivot' => $relationship->requiresPivot(),
                'eloquentMethod' => $relationship->getEloquentMethod(),
            ];
        }

        return $relationships;
    }

    /**
     * Clear cached model definition.
     *
     * @param  string  $modelClass
     * @return void
     */
    public function clearCache(string $modelClass): void
    {
        $cacheKey = 'cms_model_scan_'.md5($modelClass);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cached model definitions.
     *
     * WARNING: This method calls Cache::flush() which will clear ALL application cache,
     * not just CMS model scans. This includes session cache, route cache, config cache, etc.
     * Use with caution in production. For selective cache clearing, use clearCache($modelClass) instead.
     *
     * TODO: Implement cache tags (Redis/Memcached) or maintain a registry of scanned models
     * to enable selective clearing of only CMS model scan cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        // ⚠️ This clears ALL cache, not just model scans
        Cache::flush();
    }
}
