# ELKCMS Development Plan

This document tracks the detailed implementation progress for ELKCMS. For the complete implementation plan with all phases and architecture decisions, see the approved plan at:

**Plan File:** `/home/koki/.claude/plans/effervescent-strolling-quiche.md`

## Overview

ELKCMS is a high-performance, attribute-driven PHP CMS built on Laravel 11. The entire system is based on PHP 8 Attributes that auto-generate migrations, forms, routes, and admin interfaces.

## Current Progress

### ✅ Phase 1.1: PHP Attributes Definition (COMPLETED)
**Commit:** `3b15cbb` - "feat: Implement PHP 8 Attributes System (Phase 1.1)"

Created the foundational attribute classes:

- **[ContentModel.php](app/CMS/Attributes/ContentModel.php)** - Defines content model metadata (label, icon, features)
- **[Field.php](app/CMS/Attributes/Field.php)** - Defines field properties, validation, database types
- **[Relationship.php](app/CMS/Attributes/Relationship.php)** - Defines Eloquent relationships
- **[SEO.php](app/CMS/Attributes/SEO.php)** - Defines SEO metadata (Schema.org, sitemap)
- **[TestPost.php](app/CMS/ContentModels/TestPost.php)** - Test model using all attributes

**Features Implemented:**
- 15+ field types (string, text, image, date, json, etc.)
- Validation rule auto-generation
- Database column type mapping
- Eloquent cast type detection
- Schema.org integration
- Sitemap configuration with validation

**Testing:**
- ✅ All attributes parse correctly with PHP Reflection API
- ✅ Validation rules generated correctly
- ✅ Database types mapped correctly
- ✅ 39 unit tests passing (ContentModel, Field, SEO, Relationship)

---

### ✅ Phase 1.2: Model Scanner (Reflection System) (COMPLETED)
**Commit:** `28300cd` - "feat: Implement Model Scanner & Reflection System (Phase 1.2)"

Created the reflection system for scanning content models:

- **[ModelScanner.php](app/CMS/Reflection/ModelScanner.php)** - Scans models using PHP Reflection API
- **[FieldAnalyzer.php](app/CMS/Reflection/FieldAnalyzer.php)** - Analyzes field definitions
- **[AttributeReader.php](app/CMS/Reflection/AttributeReader.php)** - Helper for reading attributes

**Features Implemented:**
- Extract all attribute metadata from content models
- Cache scanned models (1 hour TTL) for performance
- Generate form field types for admin UI
- Generate Laravel migration methods with modifiers
- Validation string formatting
- Determine fillable and cast eligibility
- Find all classes with specific attributes in a namespace

**Testing:**
- ✅ TestPost model scanned successfully
- ✅ ContentModel attribute parsed (label: "Test Posts", icon: "edit")
- ✅ SEO attribute parsed (schema: "Article", priority: 0.8)
- ✅ All 4 fields extracted correctly
- ✅ Migration methods generated with correct syntax
- ✅ Form field types determined correctly
- ✅ 27 unit tests passing (ModelScanner, FieldAnalyzer)

**Example Output:**
```
Class: App\CMS\ContentModels\TestPost
Label: Test Posts
Schema Type: Article (https://schema.org/Article)
Sitemap Priority: 0.8

Fields:
- title: string(200), required, translatable → $table->string('title', 200)->nullable();
- content: text, translatable → $table->text('content')->nullable();
- featured_image: image → $table->string('featured_image')->nullable();
- published_at: datetime → $table->datetime('published_at')->nullable();
```

---

### ✅ Phase 1.3: Migration Generator (COMPLETED)
**Commit:** `685673a` - "feat: Implement Migration Generator (Phase 1.3)"
**Tests:** `33a817a` - "test: Add comprehensive test suite for Phases 1.1-1.3"

Created migration generation system:

- **[MigrationGenerator.php](app/CMS/Reflection/MigrationGenerator.php)** - Auto-generates Laravel migrations

**Features Implemented:**
- Generate timestamped migration files in `database/migrations/cms/`
- Support all field types from Field attribute
- Handle relationships (foreign keys, pivot tables)
- Auto-add slug column for models with SEO support
- Auto-add status column for public models
- Generate pivot table migrations for belongsToMany relationships
- Derive table names from ContentModel routePrefix or model name
- Updated .gitignore to commit migrations per architecture decision

**Testing:**
- ✅ Generated migration for TestPost model
- ✅ Migration file created with correct timestamp and naming
- ✅ Migration executed successfully via `php artisan migrate`
- ✅ Database schema verified via MySQL DESCRIBE command
- ✅ All columns created with correct types (varchar(200), text, datetime)
- ✅ Slug column added with UNIQUE constraint
- ✅ Status column added with default 'draft' and INDEX
- ✅ Timestamps (created_at, updated_at) auto-added
- ✅ 10 unit tests passing (MigrationGenerator)
- ✅ **Total: 73 tests, 209 assertions - ALL PASSING**

**Database Schema Verified:**
```sql
Field          | Type                | Null | Key | Default
id             | bigint unsigned     | NO   | PRI | NULL (auto_increment)
title          | varchar(200)        | YES  |     | NULL
content        | text                | YES  |     | NULL
featured_image | varchar(255)        | YES  |     | NULL
published_at   | datetime            | YES  |     | NULL
slug           | varchar(255)        | NO   | UNI | NULL
status         | varchar(255)        | NO   | MUL | draft
created_at     | timestamp           | YES  |     | NULL
updated_at     | timestamp           | YES  |     | NULL
```

---

### ✅ Phase 1.4: Base Content Model & Traits (COMPLETED - 2026-01-02)

**Files to Create:**

#### 1. BaseContent.php
**Path:** `app/CMS/ContentModels/BaseContent.php`

**Purpose:** Abstract base class that all CMS content models extend

**Requirements:**
- Extend `Illuminate\Database\Eloquent\Model`
- Use all CMS traits (HasTranslations, HasSlug, HasSEO, OptimizedQueries)
- Define base fillable fields
- Define base casts
- Define base hidden fields
- Implement common scopes (published, draft, archived)
- Implement status constants (DRAFT, PUBLISHED, ARCHIVED)
- Implement helper methods:
  - `isPublished()`: Check if content is published
  - `isDraft()`: Check if content is draft
  - `publish()`: Publish content
  - `unpublish()`: Unpublish content
  - `archive()`: Archive content

**Properties:**
- `$guarded = ['id']` (allow mass assignment for all except ID)
- `$casts` (auto-populated from Field attributes)
- `$fillable` (auto-populated from Field attributes)
- `$hidden = ['id']` (hide internal fields from JSON)

**Example Structure:**
```php
abstract class BaseContent extends Model
{
    use HasTranslations;
    use HasSlug;
    use HasSEO;
    use OptimizedQueries;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    protected $guarded = ['id'];

    // Scopes
    public function scopePublished($query)
    public function scopeDraft($query)
    public function scopeArchived($query)

    // Status helpers
    public function isPublished(): bool
    public function isDraft(): bool
    public function publish(): bool
    public function unpublish(): bool
    public function archive(): bool
}
```

#### 2. HasTranslations.php
**Path:** `app/CMS/Traits/HasTranslations.php`

**Purpose:** Add translation support to content models

**Methods to Implement:**
- `translate(string $field, string $locale = null): mixed` - Get translated value
- `setTranslation(string $field, string $locale, mixed $value): self` - Set translation
- `hasTranslation(string $field, string $locale): bool` - Check if translation exists
- `getTranslations(string $field): array` - Get all translations for field
- `getAllTranslations(): array` - Get all translations for all fields
- `deleteTranslations(string $field = null): self` - Delete translations
- `isTranslatable(string $field): bool` - Check if field is translatable
- `getTranslatableFields(): array` - Get all translatable field names

**Dependencies:**
- Polymorphic relationship to `translations` table
- Uses ModelScanner to detect translatable fields from Field attributes

**Example Usage:**
```php
$post->setTranslation('title', 'it', 'Titolo in Italiano');
$post->translate('title', 'it'); // Returns: 'Titolo in Italiano'
```

#### 3. HasSlug.php
**Path:** `app/CMS/Traits/HasSlug.php`

**Purpose:** Auto-generate and manage URL slugs

**Methods to Implement:**
- `generateSlug(string $source = null): string` - Generate slug from title/source
- `ensureUniqueSlug(string $slug): string` - Ensure slug is unique (append -1, -2, etc.)
- `getSlugSource(): string` - Get field to generate slug from (default: title)
- `slugShouldBeUnique(): bool` - Whether slugs must be unique (default: true)

**Behavior:**
- Auto-generate slug on model creation if not provided
- Use `Illuminate\Support\Str::slug()` for generation
- Check uniqueness against database
- Support custom slug source field
- Observe model events (creating, updating)

**Example:**
```php
$post->title = 'My Amazing Post';
$post->save(); // Slug auto-generated: 'my-amazing-post'
```

#### 4. HasSEO.php
**Path:** `app/CMS/Traits/HasSEO.php`

**Purpose:** SEO metadata management

**Methods to Implement:**
- `getSEOTitle(): string` - Get SEO title (fallback to title)
- `getSEODescription(): string` - Get SEO description (fallback to excerpt)
- `getSEOKeywords(): array` - Get SEO keywords
- `getSEOImage(): string|null` - Get SEO image URL
- `getCanonicalUrl(): string` - Get canonical URL
- `getSchemaMarkup(): array` - Generate Schema.org JSON-LD
- `getSitemapPriority(): float` - Get sitemap priority
- `getSitemapChangeFreq(): string` - Get sitemap change frequency
- `shouldIncludeInSitemap(): bool` - Check if should be in sitemap

**Properties:**
- Virtual attribute accessors for SEO fields
- Uses SEO attribute from ContentModel

**Example:**
```php
$post->getSEOTitle(); // Returns meta title or falls back to title
$post->getSchemaMarkup(); // Returns Schema.org JSON-LD array
```

#### 5. OptimizedQueries.php
**Path:** `app/CMS/Traits/OptimizedQueries.php`

**Purpose:** Query optimization helpers

**Methods to Implement:**
- `scopeWithCommonRelations($query)` - Eager load common relationships
- `scopeWithTranslations($query, string $locale = null)` - Eager load translations
- `scopeWithSEO($query)` - Eager load SEO data
- `scopeOptimized($query)` - Load all common optimizations
- `getCacheKey(): string` - Get unique cache key for model
- `getCacheTTL(): int` - Get cache TTL in seconds
- `flushCache(): void` - Clear model cache

**Features:**
- Automatic eager loading based on ContentModel supports
- Cache key generation using model class + ID
- Cache TTL from config (`config('cms.cache.ttl')`)

**Example:**
```php
Post::optimized()->get(); // Eager loads all common relations
$post->getCacheKey(); // Returns: 'cms_post_123'
```

**Testing Requirements:**
- Create unit tests for each trait
- Create integration tests for BaseContent
- Test slug uniqueness with 100+ records
- Test translation fallback behavior
- Test SEO metadata generation
- Test query optimization reduces query count
- Run full test suite after implementation

---

### ✅ Phase 1.5: Configuration Files (COMPLETED - 2026-01-02)

#### 1. config/cms.php
**Path:** `config/cms.php`

**Purpose:** Core CMS configuration

**Configuration Sections:**

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'driver' => env('CMS_CACHE_DRIVER', 'file'), // file, database, redis
        'ttl' => env('CMS_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => env('CMS_CACHE_PREFIX', 'cms_'),

        // Model scan cache
        'model_scan_ttl' => 3600, // 1 hour

        // Translation cache
        'translation_ttl' => 7200, // 2 hours

        // Content cache
        'content_ttl' => 1800, // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Models Registration
    |--------------------------------------------------------------------------
    */
    'models' => [
        // Auto-discover models in this namespace
        'namespace' => 'App\\CMS\\ContentModels',

        // Manually register models
        'register' => [
            // App\CMS\ContentModels\Page::class,
            // App\CMS\ContentModels\Post::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'status' => 'draft',
        'locale' => 'en',
        'per_page' => 15,
        'excerpt_length' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Configuration
    |--------------------------------------------------------------------------
    */
    'slug' => [
        'separator' => '-',
        'unique' => true,
        'source_field' => 'title',
        'max_length' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Library
    |--------------------------------------------------------------------------
    */
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'path' => 'media',
        'max_file_size' => 10240, // 10MB in KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],

        // Image processing
        'image' => [
            'driver' => 'gd', // gd or imagick
            'quality' => 85,
            'webp_quality' => 80,
            'thumbnails' => [
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [800, 800],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'default_schema_type' => 'Thing',
        'sitemap_enabled' => true,
        'robots_enabled' => true,
        'canonical_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'prefix' => 'admin',
        'middleware' => ['web', 'auth'],
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('CMS_API_ENABLED', true),
        'prefix' => 'api/cms',
        'middleware' => ['api'],
        'rate_limit' => '60,1', // 60 requests per minute
    ],
];
```

**Usage:**
```php
config('cms.cache.ttl'); // 3600
config('cms.defaults.status'); // 'draft'
```

#### 2. config/languages.php
**Path:** `config/languages.php`

**Purpose:** Multilanguage configuration

**Configuration Structure:**

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    */
    'default' => env('CMS_DEFAULT_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    */
    'fallback' => env('CMS_FALLBACK_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    */
    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇬🇧',
            'locale' => 'en_US',
            'enabled' => true,
        ],
        'it' => [
            'name' => 'Italian',
            'native' => 'Italiano',
            'flag' => '🇮🇹',
            'locale' => 'it_IT',
            'enabled' => true,
        ],
        'de' => [
            'name' => 'German',
            'native' => 'Deutsch',
            'flag' => '🇩🇪',
            'locale' => 'de_DE',
            'enabled' => false,
        ],
        'fr' => [
            'name' => 'French',
            'native' => 'Français',
            'flag' => '🇫🇷',
            'locale' => 'fr_FR',
            'enabled' => false,
        ],
        'es' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => '🇪🇸',
            'locale' => 'es_ES',
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Strategy
    |--------------------------------------------------------------------------
    |
    | Options: 'prefix' - /en/page, /it/page
    |          'domain' - en.example.com, it.example.com
    |          'parameter' - /page?lang=en
    */
    'url_strategy' => env('CMS_LANGUAGE_URL_STRATEGY', 'prefix'),

    /*
    |--------------------------------------------------------------------------
    | Hide Default Language in URL
    |--------------------------------------------------------------------------
    */
    'hide_default_in_url' => env('CMS_HIDE_DEFAULT_LANGUAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Translation Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => 'database', // database or file
        'table' => 'cms_translations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Switcher
    |--------------------------------------------------------------------------
    */
    'switcher' => [
        'show_flags' => true,
        'show_native_name' => true,
        'show_english_name' => false,
    ],
];
```

**Helper Methods to Create:**
- `languages_enabled()`: Get array of enabled languages
- `language_config(string $code)`: Get config for specific language
- `is_language_enabled(string $code)`: Check if language is enabled

**Testing Requirements:**
- Test config values load correctly
- Test default values work when env not set
- Test language helper functions
- Validate all config arrays have required keys

---

### 📋 Phase 1.6: Artisan Commands (PENDING)

#### 1. cms:make-model
**Path:** `app/Console/Commands/MakeContentModel.php`
**Signature:** `cms:make-model {name} {--force}`

**Purpose:** Generate a new content model with attributes

**Implementation:**
- Ask for ContentModel label and icon
- Ask which features to support (translations, seo, media, blocks)
- Ask for field definitions interactively
- Generate model class file with attributes
- Generate migration using MigrationGenerator
- Register model in config if not auto-discovered

**Interactive Prompts:**
```
What is the label for this model? (e.g., "Blog Posts")
What icon should be used? [file]
Which features should be supported? (comma-separated: translations,seo,media,blocks)
Add a field? (yes/no)
  Field name:
  Field type: (string/text/integer/boolean/date/datetime/image/file/json/select)
  Field label:
  Is it required? (yes/no)
  Is it translatable? (yes/no)
  Max length (for string fields):
```

**Generated Output:**
```php
#[ContentModel(
    label: 'Blog Posts',
    icon: 'edit',
    supports: ['translations', 'seo', 'media']
)]
#[SEO(
    schemaType: 'Article',
    sitemapPriority: '0.8',
    sitemapChangeFreq: 'weekly'
)]
class Post extends BaseContent
{
    #[Field(type: 'string', label: 'Title', required: true, translatable: true, maxLength: 200)]
    public string $title;

    // ... other fields
}
```

**Tests:**
- Test model file generation
- Test migration generation
- Test validation of inputs
- Test --force flag overwrites existing

#### 2. cms:generate-migrations
**Path:** `app/Console/Commands/GenerateCmsMigrations.php`
**Signature:** `cms:generate-migrations {model?} {--fresh} {--run}`

**Purpose:** Generate migrations for all or specific content models

**Options:**
- `model`: Specific model to generate for (optional)
- `--fresh`: Delete existing migrations first
- `--run`: Run migrations after generation

**Implementation:**
- Auto-discover all content models in namespace
- Use MigrationGenerator for each model
- Show progress bar for multiple models
- Output file paths for generated migrations
- Optionally run `php artisan migrate`

**Output:**
```
Discovering content models...
Found 3 models: Page, Post, Category

Generating migrations:
✓ Page     → database/migrations/cms/2026_01_02_120000_create_pages_table.php
✓ Post     → database/migrations/cms/2026_01_02_120001_create_posts_table.php
✓ Category → database/migrations/cms/2026_01_02_120002_create_categories_table.php

3 migrations generated successfully!

Run migrations now? (yes/no)
```

**Tests:**
- Test discovery of all models
- Test single model migration
- Test fresh flag deletes old migrations
- Test run flag executes migrations

#### 3. cms:cache-clear
**Path:** `app/Console/Commands/ClearCmsCache.php`
**Signature:** `cms:cache-clear {--type=} {--all}`

**Purpose:** Clear CMS-specific caches

**Types:**
- `models`: Clear model scan cache
- `translations`: Clear translation cache
- `content`: Clear content cache
- `all`: Clear all CMS caches

**Implementation:**
- Use Cache facade with CMS prefix
- Clear specific cache type or all
- Show count of cleared entries
- Flush cache for each model individually

**Output:**
```
Clearing CMS caches...

✓ Model scan cache     (cleared 15 entries)
✓ Translation cache    (cleared 342 entries)
✓ Content cache        (cleared 128 entries)

Total: 485 cache entries cleared
```

**Tests:**
- Test clearing each cache type
- Test --all flag clears everything
- Test cache is actually cleared

#### 4. cms:cache-warm
**Path:** `app/Console/Commands/WarmCache.php`
**Signature:** `cms:cache-warm {--models} {--translations} {--content}`

**Purpose:** Pre-cache content for better performance

**Implementation:**
- Discover all content models
- Scan each model and cache results
- Load all published content and cache
- Load all translations and cache
- Show progress with progress bar

**Output:**
```
Warming CMS caches...

Models:
✓ Scanning Page model
✓ Scanning Post model
✓ Scanning Category model

Content:
✓ Caching 45 published pages
✓ Caching 128 published posts
✓ Caching 12 categories

Translations:
✓ Caching 342 translations (en, it)

Cache warming completed in 3.2 seconds!
```

**Tests:**
- Test all models are scanned and cached
- Test content is cached
- Test translations are cached
- Test cache contains expected data

---

## Detailed Sprint Plans

### Sprint 1: Foundation (Phases 1.1-2.2) - DETAILS

**Phase 2.1: Page Content Model**
**File:** `app/CMS/ContentModels/Page.php`

**Requirements:**
- Extend BaseContent
- Use ContentModel attribute
- Use SEO attribute (schemaType: 'WebPage', priority: 0.9, changeFreq: 'weekly')
- Fields:
  - `title`: string, required, translatable, maxLength: 200
  - `content`: text, translatable
  - `featured_image`: image, optional
  - `template`: select (options: 'default', 'landing', 'contact'), default: 'default'
  - `blocks`: json (for GrapesJS page builder)
  - `parent_id`: belongsTo relationship to self (for page hierarchy)

**Example:**
```php
#[ContentModel(label: 'Pages', icon: 'file-text', supports: ['translations', 'seo', 'blocks'])]
#[SEO(schemaType: 'WebPage', sitemapPriority: '0.9', sitemapChangeFreq: 'weekly')]
class Page extends BaseContent
{
    #[Field(type: 'string', label: 'Page Title', required: true, translatable: true, maxLength: 200)]
    public string $title;

    #[Field(type: 'text', label: 'Content', translatable: true)]
    public string $content;

    #[Field(type: 'image', label: 'Featured Image')]
    public ?string $featured_image;

    #[Field(type: 'select', label: 'Template', options: ['default' => 'Default', 'landing' => 'Landing Page', 'contact' => 'Contact'], default: 'default')]
    public string $template;

    #[Field(type: 'json', label: 'Page Blocks')]
    public ?string $blocks;

    #[Relationship(type: 'belongsTo', model: Page::class, foreignKey: 'parent_id', label: 'Parent Page')]
    public ?Page $parent;

    #[Relationship(type: 'hasMany', model: Page::class, foreignKey: 'parent_id', label: 'Child Pages')]
    public Collection $children;
}
```

**Testing:**
- Generate migration and verify schema
- Create page with all fields
- Test parent-child relationships
- Test translation support
- Test SEO metadata generation

**Phase 2.2: Post Content Model**
**File:** `app/CMS/ContentModels/Post.php`

**Requirements:**
- Extend BaseContent
- Use ContentModel attribute
- Use SEO attribute (schemaType: 'Article', priority: 0.8, changeFreq: 'monthly')
- Fields:
  - `title`: string, required, translatable, maxLength: 200
  - `excerpt`: text, translatable, maxLength: 500
  - `content`: text, translatable
  - `featured_image`: image, optional
  - `published_at`: datetime, indexed
  - `author_id`: belongsTo User
  - `categories`: belongsToMany relationship

**Example:**
```php
#[ContentModel(label: 'Blog Posts', icon: 'edit', supports: ['translations', 'seo', 'media'])]
#[SEO(schemaType: 'Article', schemaProperties: ['author', 'datePublished', 'image'], sitemapPriority: '0.8', sitemapChangeFreq: 'monthly')]
class Post extends BaseContent
{
    #[Field(type: 'string', label: 'Post Title', required: true, translatable: true, maxLength: 200)]
    public string $title;

    #[Field(type: 'text', label: 'Excerpt', translatable: true, maxLength: 500)]
    public ?string $excerpt;

    #[Field(type: 'text', label: 'Post Content', translatable: true)]
    public string $content;

    #[Field(type: 'image', label: 'Featured Image')]
    public ?string $featured_image;

    #[Field(type: 'datetime', label: 'Published Date', indexed: true)]
    public ?\DateTime $published_at;

    #[Relationship(type: 'belongsTo', model: User::class, foreignKey: 'author_id', label: 'Author', eager: true)]
    public User $author;

    #[Relationship(type: 'belongsToMany', model: Category::class, pivotTable: 'post_category', label: 'Categories')]
    public Collection $categories;
}
```

**Testing:**
- Generate migration and verify schema
- Create post with all fields
- Test author relationship
- Test category relationships
- Test published_at indexing
- Test SEO Article schema generation

---

### Sprint 2: Core Services - DETAILS

**Phase 3.1: Translation Database**

**Migration:** `create_cms_translations_table`
**File:** `database/migrations/create_cms_translations_table.php`

**Schema:**
```php
Schema::create('cms_translations', function (Blueprint $table) {
    $table->id();
    $table->morphs('translatable'); // translatable_type, translatable_id
    $table->string('locale', 10)->index();
    $table->string('field', 100);
    $table->text('value')->nullable();
    $table->timestamps();

    // Composite index for faster lookups
    $table->index(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_lookup');

    // Unique constraint
    $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_unique');
});
```

**Model:** `app/Models/Translation.php`
```php
class Translation extends Model
{
    protected $table = 'cms_translations';
    protected $fillable = ['translatable_type', 'translatable_id', 'locale', 'field', 'value'];

    public function translatable()
    {
        return $this->morphTo();
    }
}
```

**Phase 3.2: Translation Service**

**File:** `app/CMS/Services/TranslationService.php`

**Methods:**
- `get(Model $model, string $field, string $locale): mixed`
- `set(Model $model, string $field, string $locale, mixed $value): void`
- `has(Model $model, string $field, string $locale): bool`
- `delete(Model $model, string $field = null, string $locale = null): void`
- `getAll(Model $model, string $locale = null): array`
- `import(string $modelClass, array $translations): int`
- `export(string $modelClass, string $locale = null): array`
- `copyTranslations(Model $from, Model $to, string $locale = null): void`
- `getProgress(string $modelClass, string $locale): array`

**Caching Strategy:**
- Cache key: `translations_{model_type}_{model_id}_{locale}`
- TTL: From `config('cms.cache.translation_ttl')`
- Invalidate on set/delete

**Phase 3.3: Language Middleware**

**File:** `app/Http/Middleware/LocaleMiddleware.php`

**Responsibilities:**
- Detect language from URL prefix, domain, or parameter
- Validate language is supported and enabled
- Set Laravel app locale
- Store locale in session
- Redirect to default language if needed
- Add locale to route parameters

**Phase 4.1: Repository Pattern**

**Files:**
- `app/CMS/Repositories/ContentRepository.php`
- `app/CMS/Repositories/TranslationRepository.php`
- `app/CMS/Repositories/MediaRepository.php`

**ContentRepository Methods:**
- `find(int $id, array $with = []): ?Model`
- `findBySlug(string $slug, array $with = []): ?Model`
- `findWhere(array $criteria, array $with = []): Collection`
- `paginate(int $perPage = 15, array $with = []): LengthAwarePaginator`
- `create(array $data): Model`
- `update(Model $model, array $data): Model`
- `delete(Model $model): bool`
- `restore(int $id): Model`

**Phase 5.1: Content Service**

**File:** `app/CMS/Services/ContentService.php`

**Methods:**
- `create(string $modelClass, array $data, array $translations = []): Model`
- `update(Model $model, array $data, array $translations = []): Model`
- `delete(Model $model): bool`
- `duplicate(Model $model, array $overrides = []): Model`
- `publish(Model $model): bool`
- `unpublish(Model $model): bool`
- `validateData(string $modelClass, array $data): array`

**Phase 5.2: Media Service**

**File:** `app/CMS/Services/MediaService.php`

**Methods:**
- `upload(UploadedFile $file, string $folder = null): Media`
- `delete(Media $media): bool`
- `resize(Media $media, int $width, int $height): string`
- `crop(Media $media, int $x, int $y, int $width, int $height): string`
- `rotate(Media $media, int $angle): string`
- `flip(Media $media, string $direction): string`
- `convertToWebP(Media $media): string`
- `generateThumbnails(Media $media): array`
- `extractExif(Media $media): array`
- `getUnusedMedia(): Collection`

**Phase 5.5: Cache Service**

**File:** `app/CMS/Services/CacheService.php`

**Methods:**
- `remember(string $key, int $ttl, Closure $callback): mixed`
- `forget(string $key): bool`
- `flush(string $prefix = null): bool`
- `warmContent(string $modelClass): int`
- `warmTranslations(string $locale = null): int`
- `warmAll(): array`

---

## Testing Strategy - EXPANDED

### Unit Tests (Required for Each Phase)

**Test Organization:**
```
tests/
├── Unit/
│   ├── CMS/
│   │   ├── Attributes/         # ✅ Phase 1.1 (39 tests)
│   │   ├── Reflection/         # ✅ Phase 1.2-1.3 (37 tests)
│   │   ├── Traits/             # Phase 1.4
│   │   ├── Services/           # Phases 3-5
│   │   └── Repositories/       # Phase 4
```

**Coverage Requirements:**
- Minimum 80% code coverage
- 100% coverage for Attributes and Reflection
- All public methods must have tests
- All edge cases must be tested

**Test Naming Convention:**
```php
public function test_{method_name}_{scenario}_{expected_result}(): void
```

**Examples:**
- `test_generate_slug_from_title_returns_lowercase_slug()`
- `test_translate_missing_field_returns_fallback_value()`
- `test_upload_invalid_file_type_throws_exception()`

### Feature Tests (Required for Each Sprint)

**Test Organization:**
```
tests/
├── Feature/
│   ├── CMS/
│   │   ├── ContentManagement/    # Create, update, delete content
│   │   ├── Translation/          # Translation workflows
│   │   ├── Media/                # Upload, process, delete media
│   │   ├── Admin/                # Admin panel functionality
│   │   └── API/                  # API endpoints
```

**Requirements:**
- Test complete user workflows
- Test with real database transactions
- Test file uploads with temporary files
- Test authentication and authorization

### Integration Tests (Required for Sprint Completion)

**Workflows to Test:**
1. **Content Workflow:**
   - Create content in default language
   - Add translations
   - Publish content
   - Display on frontend
   - Verify SEO metadata
   - Check sitemap inclusion

2. **Media Workflow:**
   - Upload image
   - Generate thumbnails
   - Convert to WebP
   - Attach to content
   - Delete image (cascade delete thumbnails)

3. **Cache Workflow:**
   - Warm cache
   - Retrieve from cache
   - Update content (invalidate cache)
   - Verify fresh data loaded

---

## Git Commit History - EXPANDED

| Commit | Phase | Description | Tests | Files Changed |
|--------|-------|-------------|-------|---------------|
| `a22200b` | Initial | Initial commit: ELKCMS foundation | 0 | 56 |
| `3b15cbb` | 1.1 | feat: Implement PHP 8 Attributes System | 39 | 5 |
| `28300cd` | 1.2 | feat: Implement Model Scanner & Reflection System | 27 | 3 |
| `c76db62` | Docs | docs: Add detailed development plan | 0 | 3 |
| `685673a` | 1.3 | feat: Implement Migration Generator | 10 | 2 |
| `33a817a` | Tests | test: Add comprehensive test suite | 73 | 13 |
| (next) | 1.4 | feat: Implement Base Content Model & Traits | ~30 | ~8 |

---

## Quick Reference - EXPANDED

### Project Structure (Complete)
```
app/
├── CMS/
│   ├── Attributes/              # ✅ Phase 1.1
│   │   ├── ContentModel.php
│   │   ├── Field.php
│   │   ├── Relationship.php
│   │   └── SEO.php
│   ├── ContentModels/           # ✅ Test, Phase 2
│   │   ├── BaseContent.php      # 📋 Phase 1.4
│   │   ├── TestPost.php         # ✅ Complete
│   │   ├── Page.php             # 📋 Phase 2.1
│   │   └── Post.php             # 📋 Phase 2.2
│   ├── Traits/                  # 📋 Phase 1.4
│   │   ├── HasTranslations.php
│   │   ├── HasSlug.php
│   │   ├── HasSEO.php
│   │   └── OptimizedQueries.php
│   ├── Reflection/              # ✅ Phase 1.2-1.3
│   │   ├── AttributeReader.php
│   │   ├── FieldAnalyzer.php
│   │   ├── MigrationGenerator.php
│   │   └── ModelScanner.php
│   ├── Services/                # 📋 Phases 3-5
│   │   ├── TranslationService.php
│   │   ├── ContentService.php
│   │   ├── MediaService.php
│   │   ├── SEOAnalyzer.php
│   │   ├── SchemaGenerator.php
│   │   └── CacheService.php
│   ├── Repositories/            # 📋 Phase 4
│   │   ├── ContentRepository.php
│   │   ├── TranslationRepository.php
│   │   └── MediaRepository.php
│   └── Builders/                # 📋 Phase 6
│       └── FormBuilder.php
├── Console/
│   └── Commands/                # 📋 Phase 1.6
│       ├── MakeContentModel.php
│       ├── GenerateCmsMigrations.php
│       ├── ClearCmsCache.php
│       └── WarmCache.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/               # 📋 Phase 7
│   │   └── Frontend/            # 📋 Phase 8
│   └── Middleware/              # 📋 Phase 3.3, 7.1
│       ├── LocaleMiddleware.php
│       └── AdminMiddleware.php
└── Models/
    └── Translation.php          # 📋 Phase 3.1

config/
├── cms.php                      # 📋 Phase 1.5
└── languages.php                # 📋 Phase 1.5

database/
└── migrations/
    └── cms/                     # ✅ Phase 1.3
        └── 2026_01_02_044324_create_test_posts_table.php

tests/
├── TestCase.php                 # ✅ Complete
├── CreatesApplication.php       # ✅ Complete
├── Unit/
│   └── CMS/
│       ├── Attributes/          # ✅ 39 tests
│       ├── Reflection/          # ✅ 37 tests
│       ├── Traits/              # 📋 Phase 1.4
│       └── Services/            # 📋 Phases 3-5
└── Feature/
    └── CMS/                     # 📋 Future sprints
```

### Command Reference

**Testing:**
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/CMS/Attributes/FieldAttributeTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter=test_can_create_field_attribute
```

**Code Quality:**
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run static analysis with Larastan
./vendor/bin/phpstan analyse

# Run ESLint on JavaScript
npm run lint
```

**CMS Commands (Phase 1.6):**
```bash
# Generate new content model
php artisan cms:make-model Post

# Generate migrations
php artisan cms:generate-migrations --run

# Clear caches
php artisan cms:cache-clear --all

# Warm caches
php artisan cms:cache-warm
```

### Development Workflow

**For Each New Phase:**
1. Review detailed requirements in this plan
2. Create todo list with specific tasks
3. Implement features following examples
4. Write comprehensive unit tests (aim for 100% coverage)
5. Run full test suite (must pass)
6. Update documentation (CHANGELOG, DEVELOPMENT_PLAN, README)
7. Commit with detailed message

**Commit Message Format:**
```
{type}: {Short description} (Phase X.Y)

{Detailed description of what was implemented}

## {Section 1}
- ✅ Feature 1
- ✅ Feature 2

## {Section 2}
- Details...

## Testing
- ✅ X tests passing

🦌 Generated with Claude Code

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

---

**Last Updated:** 2026-01-02
**Current Phase:** 1.4 - Base Content Model & Traits
**Next Milestone:** Complete Sprint 1 (Foundation)
**Total Tests:** 73 passing (209 assertions)
