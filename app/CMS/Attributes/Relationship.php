<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Relationship
{
    /**
     * Create a new Relationship attribute instance.
     *
     * @param  string  $type  Relationship type (hasOne, hasMany, belongsTo, belongsToMany, morphOne, morphMany, etc.)
     * @param  string  $model  Related model class name (e.g., Category::class)
     * @param  string|null  $foreignKey  Foreign key column name
     * @param  string|null  $localKey  Local key column name
     * @param  string|null  $pivotTable  Pivot table name for belongsToMany
     * @param  string|null  $label  Human-readable label for the relationship
     * @param  bool  $eager  Whether to eager load by default
     * @param  array  $pivotFields  Additional pivot table fields for belongsToMany
     */
    public function __construct(
        public string $type,
        public string $model,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public ?string $pivotTable = null,
        public ?string $label = null,
        public bool $eager = false,
        public array $pivotFields = [],
    ) {
    }

    /**
     * Check if this is a "to many" relationship.
     *
     * @return bool
     */
    public function isToMany(): bool
    {
        return in_array($this->type, ['hasMany', 'belongsToMany', 'morphMany', 'hasManyThrough'], true);
    }

    /**
     * Check if this is a "to one" relationship.
     *
     * @return bool
     */
    public function isToOne(): bool
    {
        return in_array($this->type, ['hasOne', 'belongsTo', 'morphOne'], true);
    }

    /**
     * Check if this relationship requires a pivot table.
     *
     * @return bool
     */
    public function requiresPivot(): bool
    {
        return in_array($this->type, ['belongsToMany'], true);
    }

    /**
     * Get the method name for defining this relationship in Eloquent.
     *
     * @return string
     */
    public function getEloquentMethod(): string
    {
        return $this->type;
    }
}
