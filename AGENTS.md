# ELKCMS - Agent Implementation Guide

This document provides detailed instructions for AI agents implementing ELKCMS. It breaks down the implementation into sequential, well-defined tasks that can be executed step-by-step.

## Implementation Philosophy

1. **Sequential Development**: Build foundation first, then add features
2. **Test as You Go**: Verify each component works before moving on
3. **Follow the Plan**: Refer to the main implementation plan for context
4. **Incremental Complexity**: Start simple, add complexity gradually

## Phase 1: Project Foundation

### Task 1.1: Initialize Laravel Project

**Objective**: Set up a fresh Laravel installation

**Steps**:
1. Run: `composer create-project laravel/laravel . --prefer-dist`
2. Verify Laravel is installed: `php artisan --version`
3. Generate app key: `php artisan key:generate`
4. Configure `.env` file with database credentials
5. Test database connection: `php artisan migrate`

**Verification**:
- Laravel welcome page loads at `http://localhost:8000`
- No errors in `storage/logs/laravel.log`

### Task 1.2: Install PHP Dependencies

**Objective**: Install required Composer packages

**Steps**:
```bash
composer require spatie/laravel-permission
composer require doctrine/dbal
composer require intervention/image
composer require spatie/laravel-backup
composer require spatie/laravel-activitylog
```

**Verification**:
- All packages installed without conflicts
- `composer.json` contains all dependencies

### Task 1.3: Install Node Dependencies

**Objective**: Install required NPM packages

**Steps**:
```bash
npm install
npm install bootstrap@5.3 @popperjs/core
npm install grapesjs grapesjs-preset-webpage
npm install sass
npm install dropzone
npm install chart.js
npm install sortablejs
```

**Verification**:
- `package.json` contains all dependencies
- `node_modules` folder created
- No npm errors

### Task 1.4: Create Directory Structure

**Objective**: Set up the CMS directory structure

**Steps**:
Create these directories:
```
app/CMS/
app/CMS/Attributes/
app/CMS/ContentModels/
app/CMS/Reflection/
app/CMS/Services/
app/CMS/Repositories/
app/CMS/Builders/
app/CMS/Traits/
app/Http/Controllers/Admin/
app/Http/Controllers/Frontend/
app/View/Components/
database/migrations/cms/
resources/views/admin/
resources/views/admin/layouts/
resources/views/admin/content/
resources/views/admin/content/fields/
resources/views/admin/content/components/
resources/views/admin/media/
resources/views/admin/translations/
resources/views/frontend/
resources/views/frontend/layouts/
resources/views/frontend/content/
resources/views/frontend/blocks/
resources/views/frontend/components/
resources/views/frontend/partials/
resources/js/admin/
resources/js/admin/modules/
resources/js/frontend/
resources/scss/admin/
resources/scss/admin/components/
resources/scss/frontend/
resources/scss/frontend/components/
resources/scss/frontend/utilities/
routes/
```

**Verification**:
- All directories exist
- Proper permissions (writable by web server)

### Task 1.5: Create Configuration Files

**Objective**: Set up CMS configuration files

**File**: `config/cms.php`
```php
<?php

return [
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'ttl' => env('CMS_CACHE_TTL', 3600),
        'driver' => env('CMS_CACHE_DRIVER', 'file'), // file or database
        'tags' => false, // file driver doesn't support tags
    ],

    'models' => [
        'namespace' => 'App\\CMS\\ContentModels',
        'scan_path' => app_path('CMS/ContentModels'),
        'auto_migrate' => env('CMS_AUTO_MIGRATE', false),
    ],

    'media' => [
        'disk' => 'public',
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
            'large' => [1024, 1024],
            'xlarge' => [1920, 1920],
        ],
    ],

    'admin' => [
        'prefix' => 'admin',
        'middleware' => ['web', 'auth', 'admin'],
        'per_page' => 20,
    ],
];
```

**File**: `config/languages.php`
```php
<?php

return [
    'default' => env('LANGUAGE_DEFAULT', 'en'),
    'fallback' => env('LANGUAGE_FALLBACK', 'en'),

    'supported' => [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
    ],

    'show_in_url' => true,
    'hide_default' => false,
];
```

**Verification**:
- Files exist in `config/` directory
- No syntax errors: `php artisan config:clear`

## Phase 2: Core Content Model System

### Task 2.1: Create PHP Attributes

**Objective**: Build the attribute system for content models

**File**: `app/CMS/Attributes/ContentModel.php`
```php
<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ContentModel
{
    public function __construct(
        public string $label,
        public string $icon = 'file',
        public array $supports = ['translations', 'seo', 'media'],
        public bool $hasSlug = true,
        public bool $hierarchical = false,
        public string $adminRoute = '',
    ) {}
}
```

**File**: `app/CMS/Attributes/Field.php`
```php
<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public string $type,
        public ?string $label = null,
        public bool $translatable = false,
        public bool $required = true,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public ?array $options = null,
        public ?string $default = null,
        public bool $searchable = true,
        public bool $sortable = true,
        public bool $showInList = true,
        public string $adminComponent = 'auto',
    ) {}
}
```

**File**: `app/CMS/Attributes/Relationship.php`
```php
<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Relationship
{
    public function __construct(
        public string $type,
        public string $model,
        public ?string $pivot = null,
        public ?array $fields = [],
    ) {}
}
```

**File**: `app/CMS/Attributes/SEO.php`
```php
<?php

namespace App\CMS\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SEO
{
    public function __construct(
        public string $schemaType = 'WebPage',
        public array $schemaProperties = [],
        public bool $enableBreadcrumbs = true,
        public bool $enableSitemap = true,
        public ?string $sitemapPriority = '0.5',
        public ?string $sitemapChangeFreq = 'weekly',
    ) {}
}
```

**Verification**:
- All attribute classes exist
- No syntax errors
- Can be imported without errors

### Task 2.2: Create ModelScanner

**Objective**: Build the reflection engine that scans content models

**File**: `app/CMS/Reflection/ModelScanner.php`

This is a CRITICAL file. Refer to the main implementation plan for the complete code.

**Key Methods**:
- `scanModels(): array` - Scans all models in the ContentModels directory
- `analyzeModel(string $className): array` - Extracts attributes from a single model
- `getTableName(string $className): string` - Generates table name

**Verification**:
- Create a test model
- Run scanner: `$scanner = new ModelScanner(); $models = $scanner->scanModels();`
- Verify it returns model metadata array

### Task 2.3: Create MigrationGenerator

**Objective**: Build the system that auto-generates migrations

**File**: `app/CMS/Reflection/MigrationGenerator.php`

Refer to main plan for complete implementation.

**Key Methods**:
- `generate(): array` - Generates migrations for all scanned models
- `generateMigration(array $model): string` - Creates a single migration file
- `getStub(array $model): string` - Generates migration code
- `generateColumns(array $model): string` - Generates column definitions

**Verification**:
- Run: `$generator = new MigrationGenerator($scanner); $generator->generate();`
- Check `database/migrations/cms/` for generated files
- Verify migration syntax is valid

### Task 2.4: Create Translation System

**File**: `database/migrations/2024_01_01_000001_create_cms_translations_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('field');
            $table->string('locale', 5);
            $table->text('value');
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id', 'locale']);
            $table->unique(['translatable_type', 'translatable_id', 'field', 'locale'], 'translation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_translations');
    }
};
```

**File**: `app/Models/Translation.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'cms_translations';

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'field',
        'locale',
        'value',
    ];

    public function translatable()
    {
        return $this->morphTo();
    }
}
```

**File**: `app/CMS/Traits/HasTranslations.php`

Refer to main plan for complete implementation.

**Key Methods**:
- `translations()` - MorphMany relationship
- `translate(string $field, string $locale)` - Get translation with fallback
- `setTranslation(string $field, string $locale, mixed $value)` - Store translation

**Verification**:
- Run migration: `php artisan migrate`
- Test translation storage and retrieval

### Task 2.5: Create Base Traits

**File**: `app/CMS/Traits/HasSlug.php`
**File**: `app/CMS/Traits/HasSEO.php`
**File**: `app/CMS/Traits/OptimizedQueries.php`

Refer to main plan for implementations.

**Verification**:
- Each trait can be imported
- Methods are callable

### Task 2.6: Create BaseContent Model

**File**: `app/CMS/ContentModels/BaseContent.php`

```php
<?php

namespace App\CMS\ContentModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CMS\Traits\{HasTranslations, HasSlug, HasSEO, OptimizedQueries};

abstract class BaseContent extends Model
{
    use SoftDeletes, HasTranslations, HasSlug, HasSEO, OptimizedQueries;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'blocks_data' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeInLocale($query, string $locale)
    {
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }
}
```

**Verification**:
- Class can be extended
- Traits are properly loaded

### Task 2.7: Create Example Content Models

**File**: `app/CMS/ContentModels/Page.php`
**File**: `app/CMS/ContentModels/Post.php`

Refer to main plan for complete implementations.

**Verification**:
- Both models can be scanned by ModelScanner
- Migrations can be generated for both
- Migrations run successfully

## Phase 3: Admin Panel Foundation

### Task 3.1: Create Admin Middleware

**File**: `app/Http/Middleware/AdminMiddleware.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
}
```

**Update**: `app/Http/Kernel.php`
Add to `$middlewareAliases`:
```php
'admin' => \App\Http\Middleware\AdminMiddleware::class,
```

**Migration**: `database/migrations/XXXX_add_admin_to_users.php`
```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false);
        $table->string('locale', 5)->default('en');
        $table->json('preferences')->nullable();
    });
}
```

**Verification**:
- Run migration
- Middleware can be used in routes

### Task 3.2: Create FormBuilder

**File**: `app/CMS/Builders/FormBuilder.php`

Refer to main plan for complete implementation. This is a CRITICAL component.

**Key Methods**:
- `buildForm(string $modelClass, $instance = null): array`
- `buildValidationRules(string $modelClass): array`
- `mapFieldType(array $field): string`

**Verification**:
- Can generate form array from a content model
- Can generate Laravel validation rules

### Task 3.3: Create Admin ContentController

**File**: `app/Http/Controllers/Admin/ContentController.php`

Refer to main plan for complete implementation.

**Key Methods**:
- `index(string $modelType)` - List all items
- `create(string $modelType)` - Show create form
- `store(Request $request, string $modelType)` - Save new item
- `edit(string $modelType, int $id)` - Show edit form
- `update(Request $request, string $modelType, int $id)` - Update item
- `destroy(string $modelType, int $id)` - Delete item

**Verification**:
- Routes work
- Can create/read/update/delete content

### Task 3.4: Create Admin Routes

**File**: `routes/admin.php`
```php
<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Content management
Route::prefix('content')->name('content.')->group(function () {
    Route::get('/{modelType}', [ContentController::class, 'index'])->name('index');
    Route::get('/{modelType}/create', [ContentController::class, 'create'])->name('create');
    Route::post('/{modelType}', [ContentController::class, 'store'])->name('store');
    Route::get('/{modelType}/{id}/edit', [ContentController::class, 'edit'])->name('edit');
    Route::put('/{modelType}/{id}', [ContentController::class, 'update'])->name('update');
    Route::delete('/{modelType}/{id}', [ContentController::class, 'destroy'])->name('destroy');
});

// Media library
Route::prefix('media')->name('media.')->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
    Route::get('/{id}/edit', [MediaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [MediaController::class, 'update'])->name('update');
    Route::delete('/{id}', [MediaController::class, 'delete'])->name('delete');
});

// Translations
Route::prefix('translations')->name('translations.')->group(function () {
    Route::get('/', [TranslationController::class, 'dashboard'])->name('dashboard');
    Route::get('/missing', [TranslationController::class, 'missing'])->name('missing');
});
```

Update `app/Providers/RouteServiceProvider.php` to load admin routes.

**Verification**:
- Admin routes are registered
- Middleware is applied
- Can access admin panel

(Continuing in next section due to length...)

## Incremental Implementation Strategy

For each subsequent phase:

1. **Read the main plan** for that phase
2. **Implement files sequentially** in the order specified
3. **Test each component** before moving to the next
4. **Verify dependencies** are met before starting a phase
5. **Create simple tests** to ensure functionality
6. **Document any deviations** from the plan

## Critical Implementation Order

These components MUST be implemented in this order:

1. ✅ Attributes (ContentModel, Field, Relationship, SEO)
2. ✅ ModelScanner (reads attributes)
3. ✅ MigrationGenerator (uses ModelScanner output)
4. ✅ Base traits (HasTranslations, HasSlug, HasSEO)
5. ✅ BaseContent model (uses traits)
6. ✅ Example models (Page, Post)
7. ✅ FormBuilder (uses ModelScanner)
8. ✅ Admin ContentController (uses FormBuilder)
9. ✅ Admin views (uses FormBuilder output)
10. ✅ Frontend system (displays content)

## Testing Checkpoints

After each phase, verify:

- **Phase 1**: Laravel is running, dependencies installed
- **Phase 2**: Can create model, generate migration, run migration
- **Phase 3**: Admin panel accessible, can CRUD content
- **Phase 4**: Translations work, language switching works
- **Phase 5**: Assets compile, Vite HMR works
- **Phase 6**: Routes work, SEO data renders
- **Phase 7**: GrapesJS editor loads, blocks save
- **Phase 8**: Caching works, performance optimized
- **Phase 9**: Media library works, image editing works
- **Phase 10**: SEO analyzer works, sitemaps generate

## Common Pitfalls to Avoid

1. **Don't skip the foundation** - Attributes and ModelScanner are critical
2. **Test migrations** before creating views
3. **Handle cache properly** - Clear cache during development
4. **Check permissions** on storage directories
5. **Verify Vite builds** before testing frontend
6. **Test translations** with multiple locales
7. **Test on fresh database** regularly

## When Things Go Wrong

### Migration errors:
```bash
php artisan migrate:rollback
php artisan cms:generate-migrations --fresh
php artisan migrate
```

### Cache issues:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Asset issues:
```bash
rm -rf public/build
npm run build
```

### Database issues:
```bash
php artisan migrate:fresh --seed
```

## Performance Monitoring

During development, monitor:
- Database query count (should use eager loading)
- Cache hit rates
- Asset bundle sizes
- Page load times

Use Laravel Debugbar for monitoring:
```bash
composer require barryvdh/laravel-debugbar --dev
```

## Completion Checklist

Before considering a phase complete:

- [ ] All files created
- [ ] No syntax errors
- [ ] All imports resolved
- [ ] Migrations run successfully
- [ ] Manual testing passes
- [ ] Cache cleared
- [ ] Git committed

---

## Support for AI Agents

This document should be used alongside:
- Main implementation plan (in .claude/plans/)
- README.md (for feature overview)
- DEVELOPMENT.md (for development guidelines)

When implementing, always:
1. Read the context from all documents
2. Follow the sequential order
3. Test incrementally
4. Ask for clarification if the plan is ambiguous
5. Document any changes or improvements

Good luck! 🚀
