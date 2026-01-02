<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ContentModel
{
    /**
     * Create a new ContentModel attribute instance.
     *
     * @param  string  $label  Human-readable label for the content model (e.g., "Blog Posts")
     * @param  string  $icon  Icon identifier for admin UI (e.g., "edit", "file-text")
     * @param  array  $supports  Array of features this model supports (e.g., ['translations', 'seo', 'media', 'blocks'])
     * @param  string|null  $description  Optional description of the content model
     * @param  bool  $public  Whether this content is publicly accessible (default: true)
     * @param  string|null  $routePrefix  Custom route prefix (default: pluralized model name)
     */
    public function __construct(
        public string $label,
        public string $icon = 'file',
        public array $supports = [],
        public ?string $description = null,
        public bool $public = true,
        public ?string $routePrefix = null,
    ) {
    }

    /**
     * Check if the content model supports a specific feature.
     *
     * @param  string  $feature
     * @return bool
     */
    public function supports(string $feature): bool
    {
        return in_array($feature, $this->supports, true);
    }

    /**
     * Get all supported features.
     *
     * @return array
     */
    public function getSupports(): array
    {
        return $this->supports;
    }
}
