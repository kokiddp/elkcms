# ELKCMS Development Guide

This guide provides detailed instructions for developers working on ELKCMS.

## Table of Contents

1. [Development Environment Setup](#development-environment-setup)
2. [Project Structure](#project-structure)
3. [Core Concepts](#core-concepts)
4. [Development Workflow](#development-workflow)
5. [Creating Content Models](#creating-content-models)
6. [Database Migrations](#database-migrations)
7. [Frontend Development](#frontend-development)
8. [Testing](#testing)
9. [Code Style](#code-style)
10. [Debugging](#debugging)

## Development Environment Setup

### Prerequisites

#### For Docker Development (Recommended)

- Docker 20.10+
- Docker Compose 1.29+
- Git

#### For Local Development (Without Docker)

- PHP 8.2, 8.3, or 8.4 with extensions: mbstring, xml, curl, zip, gd, pdo_mysql
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Node.js 20+ & NPM
- Git

### Docker Setup (Recommended)

1. **Clone the repository**

```bash
git clone https://github.com/kokiddp/elkcms.git
cd elkcms
```

2. **Start Docker containers**

```bash
docker-compose up -d
```

This starts:
- **app**: PHP 8.3-FPM application container
- **nginx**: Web server (port 8000)
- **db**: MySQL 8.0 database (port 3306)
- **node**: Node.js 20 for asset building (port 5173 for Vite HMR)

3. **Install dependencies**

```bash
docker-compose exec app composer install
docker-compose exec node npm install
```

4. **Configure environment**

```bash
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
```

The Docker setup uses these default database credentials (already configured in docker-compose.yml):
```ini
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=elkcms
DB_USERNAME=elkcms
DB_PASSWORD=secret
```

5. **Setup database**

```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cms:make-admin
```

6. **Link storage**

```bash
docker-compose exec app php artisan storage:link
```

7. **Start Vite dev server**

Vite is already running via the node container. Access:
- **Frontend**: http://localhost:8000
- **Admin**: http://localhost:8000/admin
- **Vite HMR**: http://localhost:5173

### Docker Commands

```bash
# View logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db

# Access containers
docker-compose exec app bash
docker-compose exec db mysql -u elkcms -pelkcms

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cms:cache-clear

# Run composer commands
docker-compose exec app composer require package/name

# Run npm commands
docker-compose exec node npm install package-name
docker-compose exec node npm run build

# Stop containers
docker-compose down

# Stop and remove volumes (deletes database!)
docker-compose down -v

# Rebuild containers
docker-compose up -d --build
```

### Local Setup (Without Docker)

1. **Clone the repository**

```bash
git clone https://github.com/kokiddp/elkcms.git
cd elkcms
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```ini
APP_NAME=ELKCMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elkcms
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file

CMS_CACHE_ENABLED=true
CMS_CACHE_DRIVER=file
```

4. **Setup database**

```bash
php artisan migrate
php artisan db:seed
php artisan cms:make-admin
```

5. **Link storage**

```bash
php artisan storage:link
```

6. **Start development servers**

Terminal 1 - Laravel dev server:
```bash
php artisan serve
```

Terminal 2 - Vite dev server with HMR:
```bash
npm run dev
```

Access:
- Frontend: http://localhost:8000
- Admin: http://localhost:8000/admin

## Project Structure

```
elkcms/
├── app/
│   ├── CMS/                        # Core CMS functionality
│   │   ├── Attributes/             # PHP 8 attributes for models
│   │   │   ├── ContentModel.php    # Content model metadata
│   │   │   ├── Field.php           # Field definitions
│   │   │   ├── Relationship.php    # Relationship definitions
│   │   │   └── SEO.php             # SEO metadata
│   │   ├── ContentModels/          # User-defined content types
│   │   │   ├── BaseContent.php     # Base class for all models
│   │   │   ├── Page.php            # Page content type
│   │   │   └── Post.php            # Blog post content type
│   │   ├── Reflection/             # Model scanning & generation
│   │   │   ├── ModelScanner.php    # Scans model attributes
│   │   │   ├── FieldAnalyzer.php   # Analyzes field definitions
│   │   │   └── MigrationGenerator.php # Auto-generates migrations
│   │   ├── Services/               # Business logic services
│   │   │   ├── ContentService.php
│   │   │   ├── TranslationService.php
│   │   │   ├── MediaService.php
│   │   │   ├── SEOAnalyzer.php
│   │   │   ├── SchemaGenerator.php
│   │   │   └── CacheService.php
│   │   ├── Repositories/           # Data access layer
│   │   │   ├── ContentRepository.php
│   │   │   └── TranslationRepository.php
│   │   ├── Builders/               # Dynamic builders
│   │   │   ├── FormBuilder.php     # Auto-generates forms
│   │   │   ├── RouteBuilder.php    # Auto-registers routes
│   │   │   └── QueryBuilder.php    # Query optimization
│   │   └── Traits/                 # Reusable behaviors
│   │       ├── HasTranslations.php
│   │       ├── HasSlug.php
│   │       ├── HasSEO.php
│   │       └── OptimizedQueries.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin panel controllers
│   │   │   │   ├── ContentController.php
│   │   │   │   ├── MediaController.php
│   │   │   │   ├── TranslationController.php
│   │   │   │   └── SEOController.php
│   │   │   └── Frontend/           # Public site controllers
│   │   │       └── ContentController.php
│   │   └── Middleware/
│   │       ├── LocaleMiddleware.php
│   │       ├── AdminMiddleware.php
│   │       └── RedirectMiddleware.php
│   ├── Models/
│   │   ├── Translation.php
│   │   ├── Media.php
│   │   ├── Redirect.php
│   │   └── User.php
│   └── Console/Commands/           # Artisan commands
│       ├── MakeContentModel.php
│       ├── GenerateCmsMigrations.php
│       ├── ClearCmsCache.php
│       └── WarmCache.php
├── config/
│   ├── cms.php                     # Core CMS configuration
│   ├── languages.php               # Supported languages
│   └── content-models.php          # Registered models
├── database/
│   ├── migrations/
│   │   └── cms/                    # Auto-generated migrations
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── admin/                  # Admin panel Blade templates
│   │   │   ├── layouts/
│   │   │   ├── content/
│   │   │   ├── media/
│   │   │   ├── translations/
│   │   │   └── components/
│   │   └── frontend/               # Public site templates
│   │       ├── layouts/
│   │       ├── content/
│   │       ├── blocks/
│   │       ├── components/
│   │       └── partials/
│   ├── js/
│   │   ├── admin/
│   │   │   ├── app.js
│   │   │   ├── grapes-config.js
│   │   │   └── modules/
│   │   │       ├── media-editor.js
│   │   │       ├── media-library.js
│   │   │       └── seo-analyzer.js
│   │   └── frontend/
│   │       └── app.js
│   └── scss/
│       ├── admin/
│       │   └── admin.scss
│       └── frontend/
│           └── app.scss
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── admin.php
│   └── cms.php                     # Dynamic content routes
├── tests/
│   ├── Feature/
│   └── Unit/
└── vite.config.js
```

## Core Concepts

### 1. Attribute-Driven Content Models

ELKCMS's core innovation is using PHP 8 attributes to define content models. The system reads these attributes via reflection and automatically generates everything else.

**Example:**

```php
#[ContentModel(
    label: 'Blog Posts',
    icon: 'edit',
    supports: ['translations', 'seo', 'media', 'blocks']
)]
#[SEO(
    schemaType: 'Article',
    sitemapPriority: '0.8'
)]
class Post extends BaseContent
{
    #[Field(type: 'string', translatable: true, maxLength: 200)]
    public string $title;

    #[Field(type: 'text', translatable: true)]
    public string $content;

    #[Field(type: 'image', required: false)]
    public ?string $featured_image;

    #[Relationship(type: 'belongsToMany', model: Category::class)]
    public Collection $categories;
}
```

### 2. Model Scanner

The `ModelScanner` class uses PHP's Reflection API to scan content model classes and extract attribute metadata:

```php
$scanner = new ModelScanner();
$models = $scanner->scanModels(); // Returns array of model definitions
```

### 3. Migration Generator

The `MigrationGenerator` takes scanned model definitions and creates Laravel migration files:

```php
$generator = new MigrationGenerator($scanner);
$migrations = $generator->generate(); // Creates migration files
```

### 4. Form Builder

The `FormBuilder` dynamically generates admin forms based on field attributes:

```php
$formBuilder = new FormBuilder($scanner);
$form = $formBuilder->buildForm(Post::class, $post);
$rules = $formBuilder->buildValidationRules(Post::class);
```

### 5. Translation System

Translations are stored in a polymorphic `cms_translations` table. The `HasTranslations` trait provides methods:

```php
$post->translate('title', 'it'); // Get Italian translation
$post->setTranslation('title', 'it', 'Titolo Italiano'); // Set translation
```

### 6. SEO System

The SEO system analyzes content and provides real-time feedback:

```php
$analyzer = new SEOAnalyzer();
$analysis = $analyzer->analyzeContent($post, 'en');
// Returns score, status, checks, improvements
```

## Development Workflow

### Adding a New Content Type

1. **Generate the model**

```bash
php artisan cms:make-model Event
```

2. **Edit the model** (`app/CMS/ContentModels/Event.php`)

```php
<?php

namespace App\CMS\ContentModels;

use App\CMS\Attributes\{ContentModel, Field, SEO};
use Carbon\Carbon;

#[ContentModel(
    label: 'Events',
    icon: 'calendar',
    supports: ['translations', 'seo', 'media']
)]
#[SEO(
    schemaType: 'Event',
    schemaProperties: ['startDate', 'endDate', 'location'],
    sitemapPriority: '0.7'
)]
class Event extends BaseContent
{
    protected $table = 'cms_events';

    #[Field(type: 'string', translatable: true, maxLength: 200)]
    public string $title;

    #[Field(type: 'text', translatable: true)]
    public string $description;

    #[Field(type: 'datetime', label: 'Start Date')]
    public Carbon $start_date;

    #[Field(type: 'datetime', label: 'End Date')]
    public Carbon $end_date;

    #[Field(type: 'string', translatable: true, required: false)]
    public ?string $location;

    #[Field(type: 'string', required: false)]
    public ?string $registration_url;
}
```

3. **Generate migration**

```bash
php artisan cms:generate-migrations
php artisan migrate
```

4. **Done!** The Event content type now has:
   - Database table (`cms_events`)
   - Admin CRUD interface at `/admin/content/event`
   - Public routes at `/{locale}/event/{slug}`
   - SEO meta fields
   - Translation support
   - Schema.org Event markup

### Adding a Custom Field Type

1. **Update Field attribute** to support new type in `app/CMS/Attributes/Field.php`

2. **Update MigrationGenerator** column mapping in `generateColumnDefinition()`

3. **Create admin component** `resources/views/admin/content/fields/{type}.blade.php`

4. **Update FormBuilder** type mapping in `mapFieldType()`

Example for a `wysiwyg` field:

```php
// In Field.php - already supports any string type

// In MigrationGenerator.php - map to text column
'wysiwyg' => "\$table->text('{$name}')",

// In FormBuilder.php
'wysiwyg' => 'wysiwyg-editor',

// Create resources/views/admin/content/fields/wysiwyg-editor.blade.php
<div class="mb-3">
    <label>{{ $field['label'] }}</label>
    <textarea
        name="{{ $field['name'] }}"
        class="form-control wysiwyg-editor"
        rows="10"
    >{{ old($field['name'], $field['value']) }}</textarea>
</div>

@push('scripts')
<script>
    // Initialize your WYSIWYG editor (TinyMCE, CKEditor, etc.)
</script>
@endpush
```

### Adding Custom GrapesJS Blocks

Edit `resources/js/admin/grapes-config.js`:

```javascript
export default {
    // ... existing config

    blockManager: {
        blocks: [
            // ... existing blocks
            {
                id: 'testimonial',
                label: 'Testimonial',
                category: 'Components',
                content: `
                    <div class="testimonial py-4">
                        <blockquote class="blockquote">
                            <p>Customer quote goes here...</p>
                            <footer class="blockquote-footer">
                                Customer Name
                            </footer>
                        </blockquote>
                    </div>
                `,
            },
        ],
    },
};
```

## Database Migrations

### Auto-Generated Migrations

ELKCMS automatically generates migrations from content models:

```bash
php artisan cms:generate-migrations
```

This creates files in `database/migrations/cms/`.

### Custom Migrations

For manual schema changes, create standard Laravel migrations:

```bash
php artisan make:migration add_custom_field_to_pages
```

### Migration Best Practices

1. **Never edit auto-generated migrations** - they'll be overwritten
2. **Use custom migrations** for one-off schema changes
3. **Test migrations** with `php artisan migrate:fresh` in development
4. **Version control** all migration files

## Frontend Development

### Vite Configuration

ELKCMS uses Vite for asset bundling with hot module replacement (HMR).

**Development mode:**
```bash
npm run dev
```

**Production build:**
```bash
npm run build
```

### Adding Custom JavaScript

Admin JavaScript:
```javascript
// resources/js/admin/modules/custom-module.js
export function initCustomFeature() {
    // Your code here
}

// resources/js/admin/app.js
import { initCustomFeature } from './modules/custom-module';

document.addEventListener('DOMContentLoaded', () => {
    initCustomFeature();
});
```

Frontend JavaScript:
```javascript
// resources/js/frontend/modules/custom.js
// Your code here

// resources/js/frontend/app.js
import './modules/custom';
```

### Adding Custom Styles

Admin styles:
```scss
// resources/scss/admin/components/_custom.scss
.my-custom-component {
    // Styles
}

// resources/scss/admin/admin.scss
@import 'components/custom';
```

Frontend styles:
```scss
// resources/scss/frontend/components/_custom.scss
.my-component {
    // Styles
}

// resources/scss/frontend/app.scss
@import 'components/custom';
```

### Creating Blade Components

```bash
php artisan make:component LanguageSwitcher
```

```php
<?php
// app/View/Components/LanguageSwitcher.php
namespace App\View\Components;

use Illuminate\View\Component;

class LanguageSwitcher extends Component
{
    public function render()
    {
        $languages = config('languages.supported');
        $current = app()->getLocale();

        return view('components.language-switcher', [
            'languages' => $languages,
            'current' => $current,
        ]);
    }
}
```

```blade
{{-- resources/views/components/language-switcher.blade.php --}}
<div class="language-switcher">
    @foreach($languages as $code => $language)
        <a href="{{ route(Route::currentRouteName(), ['locale' => $code]) }}"
           class="{{ $current === $code ? 'active' : '' }}">
            {{ $language['flag'] }} {{ $language['name'] }}
        </a>
    @endforeach
</div>
```

Usage in templates:
```blade
<x-language-switcher />
```

## Testing

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/ContentModelTest.php

# With coverage
php artisan test --coverage
```

### Writing Feature Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\CMS\ContentModels\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_page()
    {
        $page = Page::create([
            'title' => 'Test Page',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('cms_pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    public function test_can_translate_page()
    {
        $page = Page::factory()->create();

        $page->setTranslation('title', 'it', 'Pagina di Test');

        $this->assertEquals('Pagina di Test', $page->translate('title', 'it'));
    }
}
```

### Writing Unit Tests

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\CMS\Reflection\ModelScanner;

class ModelScannerTest extends TestCase
{
    public function test_scans_content_models()
    {
        $scanner = new ModelScanner();
        $models = $scanner->scanModels();

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
    }
}
```

## Code Style

ELKCMS follows PSR-12 coding standards.

### PHP Code Style

Run PHP CS Fixer:
```bash
./vendor/bin/php-cs-fixer fix
```

### JavaScript/TypeScript Code Style

Run ESLint:
```bash
npm run lint
npm run lint:fix
```

### Naming Conventions

- **Classes**: PascalCase (`ContentModel`, `ModelScanner`)
- **Methods**: camelCase (`scanModels()`, `generateMigration()`)
- **Variables**: camelCase (`$modelInfo`, `$translatableFields`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_FILE_SIZE`)
- **Database tables**: snake_case with `cms_` prefix (`cms_pages`, `cms_translations`)
- **Routes**: kebab-case (`/admin/content-models`)

## Debugging

### Enable Query Logging

Add to your controller method:
```php
\DB::enableQueryLog();
// ... your code
dd(\DB::getQueryLog());
```

### Debug Bar

Install Laravel Debugbar:
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Logging

```php
use Illuminate\Support\Facades\Log;

Log::info('Model scanned', ['model' => $modelClass]);
Log::error('Migration failed', ['error' => $e->getMessage()]);
```

View logs:
```bash
tail -f storage/logs/laravel.log
```

### Cache Issues

If you're experiencing cache issues:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Translation Issues

Check translation cache:
```bash
php artisan cms:cache-clear --type=translations
```

### Asset Issues

Clear compiled assets:
```bash
npm run build
rm -rf public/build
npm run build
```

## Performance Optimization

### Database Optimization

1. **Add indexes** for frequently queried columns
2. **Use eager loading** to avoid N+1 queries:
   ```php
   $posts = Post::with(['translations', 'categories'])->get();
   ```
3. **Cache query results**:
   ```php
   $posts = cache()->remember('posts.published', 3600, function () {
       return Post::published()->get();
   });
   ```

### Cache Strategy

1. **Content cache**: Full rendered pages (1 hour TTL)
2. **Translation cache**: Individual translations (1 hour TTL)
3. **Model metadata cache**: Scanned model definitions (indefinite, cleared on deployment)
4. **Query cache**: Common queries (15 minutes TTL)

Warm caches after deployment:
```bash
php artisan cms:cache-warm
```

### Asset Optimization

1. **Code splitting**: Automatically done by Vite
2. **Lazy loading images**: Use `loading="lazy"` attribute
3. **WebP images**: Auto-generated by media library
4. **Minification**: `npm run build` minifies assets

## Deployment

ELKCMS uses [Deployer](https://deployer.org/) for automated, zero-downtime deployments.

### Setup Deployment

1. **Install Deployer**

Deployer is already included as a dev dependency. The configuration is in `deploy.php`.

2. **Configure hosts**

Edit `deploy.php` to add your servers:

```php
host('production')
    ->set('hostname', 'your-production-server.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/elkcms')
    ->set('branch', 'main');

host('staging')
    ->set('hostname', 'your-staging-server.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/elkcms-staging')
    ->set('branch', 'develop');
```

3. **Setup SSH keys**

Ensure your SSH key is added to the remote server:

```bash
ssh-copy-id deploy@your-production-server.com
```

4. **Configure environment files**

Copy `.env.production.example` to your server:

```bash
scp .env.production.example deploy@your-server.com:/var/www/elkcms/shared/.env
```

Edit the `.env` file on the server with production credentials.

### Deploy Commands

```bash
# Deploy to production
dep deploy production

# Deploy to staging
dep deploy staging

# Rollback to previous release
dep rollback production

# SSH into server
dep ssh production

# Run artisan commands on server
dep artisan:migrate production
dep cms:cache-warm production
dep artisan:cache:clear production
```

### Deployment Flow

When you run `dep deploy production`, Deployer:

1. **Prepares release** - Creates new release directory
2. **Clones repository** - Pulls latest code from git
3. **Installs dependencies** - Runs `composer install --no-dev`
4. **Builds assets** - Runs `npm ci && npm run build`
5. **Links storage** - Creates storage symlink
6. **Caches config** - Runs `artisan:config:cache`, `artisan:route:cache`, `artisan:view:cache`
7. **Runs migrations** - Executes database migrations
8. **Generates CMS migrations** - Auto-generates from models
9. **Switches symlink** - Points `current` to new release (zero downtime!)
10. **Warms cache** - Runs `cms:cache-warm`
11. **Cleans up** - Removes old releases (keeps last 3)

### Server Requirements

Your production server needs:

- PHP 8.2, 8.3, or 8.4 with extensions: mbstring, xml, curl, zip, gd, pdo_mysql
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Node.js 20+ & NPM
- Web server (Nginx or Apache)

### Nginx Configuration

Example Nginx config for production:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/elkcms/current/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### CI/CD with GitHub Actions

ELKCMS includes a GitHub Actions workflow (`.github/workflows/deploy.yml`) that:

1. Runs tests on every push
2. Deploys to staging on push to `develop` branch
3. Deploys to production on push to `main` branch

To enable:

1. Add `SSH_PRIVATE_KEY` to GitHub repository secrets
2. Push to `develop` or `main` branch

### Post-Deployment Checklist

After deploying to production:

- [ ] Verify `.env` file has correct credentials
- [ ] Run database migrations
- [ ] Warm CMS cache
- [ ] Test critical user flows
- [ ] Check error logs
- [ ] Verify SSL certificate
- [ ] Test multilanguage pages
- [ ] Verify media uploads work
- [ ] Check SEO meta tags and sitemaps

---

Happy coding! 🚀
