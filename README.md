# ELKCMS

**A high-performance, attribute-driven PHP CMS built on Laravel**

ELKCMS is a modern content management system that combines the power of Laravel with an innovative "define once, reflect everywhere" architecture. By leveraging PHP 8 attributes, ELKCMS allows you to define content models once and automatically generates database schemas, admin interfaces, forms, routes, and APIs.

## 🚀 Key Features

### Core Innovation
- **Attribute-Driven Content Models**: Define content types as PHP classes with attributes
- **Auto-Generated Everything**: Migrations, admin forms, routes, and APIs generated automatically
- **Zero Boilerplate**: Focus on business logic, not repetitive CRUD code

### Multilanguage Support
- **WPML-Inspired, Better Performance**: Enterprise-grade translation system
- **Frontend & Backend**: Full translation support everywhere
- **Translation Dashboard**: Visual progress tracking, bulk operations, import/export
- **Cache-Optimized**: Aggressive caching strategy for lightning-fast multilingual sites
- **Language Switcher**: Seamless language switching with proper URL handling

### Advanced SEO (Yoast-Inspired)
- **Real-Time SEO Analysis**: Live content scoring and suggestions as you type
- **Schema.org Integration**: Automatic structured data generation from model attributes
- **Traffic Light Indicators**: Visual feedback on SEO quality (red/orange/green)
- **Sitemap Generation**: Automatic XML sitemaps with multilingual support
- **Breadcrumbs**: Auto-generated with Schema.org markup
- **Redirect Manager**: 301/302 redirects with analytics
- **Meta Preview**: See how your content appears on Google, Facebook, Twitter

### Professional Media Library
- **Built-In Image Editor**: Crop, resize, rotate, flip, filter
- **Drag & Drop Upload**: Modern interface with progress indicators
- **Multiple Sizes**: Auto-generate thumbnails and WebP versions
- **Folder Organization**: Organize media in folders
- **Bulk Operations**: Select and manage multiple files
- **EXIF Data**: Extract and display camera information
- **Unused Media Detection**: Find and clean up orphaned files

### Visual Page Builder
- **Gutenberg Integration (Isolated Block Editor)**: Block editor for pagebuilder fields
- **Bootstrap Blocks**: Pre-built components (hero, cards, grids, etc.)
- **Responsive Design**: Mobile-first editing experience
- **Custom Blocks**: Easy to add your own components
- **No Lock-In**: Content stored as clean HTML, easily portable

### Performance Oriented
- **File/Database Caching**: Aggressive multi-layer caching strategy (no Redis required)
- **Query Optimization**: Eager loading, indexed queries, batch operations
- **Asset Optimization**: Code splitting, minification, WebP images
- **Cache Warming**: Pre-cache content after deployment
- **Database Indexing**: Strategic indexes for common query patterns
- **Works Anywhere**: No Redis dependency - compatible with any hosting platform

### Developer Experience
- **Artisan Commands**: Generate models, migrations, and more
- **Clean Architecture**: Service layer, repository pattern, trait composition
- **Type Safety**: PHP 8+ with strict types
- **Vite Build System**: Hot reload, fast builds, modern tooling
- **API-First**: Optional headless CMS mode with REST API

### Security & Compliance
- **Role-Based Access**: Multiple user roles with granular permissions
- **Activity Logging**: Audit trail for all content changes
- **Automated Backups**: Scheduled backups to S3, Google Drive, etc.
- **CSRF Protection**: Built-in security best practices

## 📊 Development Progress

> **Current Status:** Phase 4 Partial Complete - 60% Production-Ready

ELKCMS is under active development with a **solid foundation** and **functional admin interface**. See [SPRINT_PLAN.md](SPRINT_PLAN.md) for the production roadmap.

### Completed ✅
- ✅ **Phase 1:** Foundation (PHP 8 Attributes, Migration Generator, Base Models, Artisan Commands)
- ✅ **Phase 2:** Translation System (Database-backed multilingual support, N+1 optimization)
- ✅ **Phase 3:** Services & Repositories (TranslationService, ContentRepository, LocaleMiddleware)
- 🟡 **Phase 4:** Admin Interface (Auth, Dashboard, Content CRUD) - **Partial**
- **Testing:** 313 tests passing (777 assertions, 100% pass rate)

### What Works Today ✅
- **Backend:** Attribute-driven content models, translation engine, performance optimization
- **Admin:** WordPress-inspired dashboard, content CRUD, role-based access (5 roles, 18 permissions)
- **Editor:** Gutenberg (Isolated Block Editor) pagebuilder fields with on-demand assets
- **Quality:** SCSS architecture, reusable Blade components, comprehensive testing

### What's Missing (60% → 100%) 🔴
- **FormBuilder Service** - Dynamic form generation from attributes
- **Frontend Routes & Views** - Public website (0% complete)
- **Translation Management UI** - Currently requires code/Tinker
- **Media Upload Processing** - Image upload handling
- **User Management UI** - Admin panel for users/roles

### Production Roadmap 📋
See [SPRINT_PLAN.md](SPRINT_PLAN.md) for the complete 3-sprint roadmap:
- **Sprint 1:** FormBuilder + Frontend + Visual Builder + Media (5 days) → 75% ready
- **Sprint 2:** Translation UI + Users + Settings + Taxonomy (6 days) → 90% ready
- **Sprint 3:** Media Library + Bulk Actions + Revisions + Menus (5 days) → 100% ready

**Total:** 16 days to production-ready | 96 hours of development | 412+ tests

For detailed status and gap analysis, see [CURRENT_STATUS.md](CURRENT_STATUS.md).

## 📋 Requirements

- PHP 8.2, 8.3, or 8.4
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Node.js 20+ & NPM
- **Or use Docker** (recommended for development)

## 🔧 Installation

### Option 1: Docker (Recommended for Development)

```bash
# Clone the repository
git clone https://github.com/kokiddp/elkcms.git
cd elkcms

# Start Docker containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec node npm install

# Set up environment
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# Run migrations and create admin
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cms:make-admin

# Build assets (Vite will auto-reload on changes)
docker-compose exec node npm run dev
```

Access the application at `http://localhost:8000` and admin at `http://localhost:8000/admin`

### Option 2: Local Installation

#### 1. Clone the Repository

```bash
git clone https://github.com/kokiddp/elkcms.git
cd elkcms
```

#### 2. Install PHP Dependencies

```bash
composer install
```

#### 3. Install Node Dependencies

```bash
npm install
```

#### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:
- Database credentials
- App URL
- Mail settings
- Cache driver (file or database)

#### 5. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

#### 6. Create Admin User

```bash
php artisan cms:make-admin
```

Follow the prompts to create your admin account.

#### 7. Link Storage

```bash
php artisan storage:link
```

#### 8. Build Assets

For development with hot reload:
```bash
npm run dev
```

For production:
```bash
npm run build
```

#### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000/admin` and log in with your admin credentials.

## 📚 Creating Content Models

ELKCMS's most powerful feature is its attribute-driven content model system. Here's how to create a custom content type:

### Generate a New Model

```bash
php artisan cms:make-model Portfolio
```

This creates `app/CMS/ContentModels/Portfolio.php`.

### Define Your Model

```php
<?php

namespace App\CMS\ContentModels;

use App\CMS\Attributes\ContentModel;
use App\CMS\Attributes\Field;
use App\CMS\Attributes\Relationship;
use App\CMS\Attributes\SEO;

#[ContentModel(
    label: 'Portfolio Items',
    icon: 'briefcase',
    supports: ['translations', 'seo', 'media', 'blocks']
)]
#[SEO(
    schemaType: 'CreativeWork',
    schemaProperties: ['author', 'dateCreated', 'image'],
    sitemapPriority: '0.8',
    sitemapChangeFreq: 'monthly'
)]
class Portfolio extends BaseContent
{
    protected $table = 'cms_portfolios';

    #[Field(
        type: 'string',
        maxLength: 200,
        translatable: true,
        label: 'Project Title'
    )]
    public string $title;

    #[Field(
        type: 'text',
        translatable: true,
        label: 'Project Description'
    )]
    public string $description;

    #[Field(
        type: 'image',
        required: true,
        label: 'Featured Image'
    )]
    public string $featured_image;

    #[Field(
        type: 'date',
        label: 'Project Date'
    )]
    public Carbon $project_date;

    #[Field(
        type: 'string',
        required: false,
        label: 'Client Name'
    )]
    public ?string $client_name;

    #[Relationship(
        type: 'belongsToMany',
        model: Category::class
    )]
    public Collection $categories;
}
```

### Generate Database Migration

```bash
php artisan cms:generate-migrations
php artisan migrate
```

That's it! Your new content type now has:
- ✅ Database table
- ✅ Admin CRUD interface
- ✅ Multilingual support
- ✅ SEO meta fields
- ✅ Public routes
- ✅ Schema.org markup
- ✅ Automatic sitemap inclusion

## 🌍 Multilanguage Configuration

Edit `config/languages.php`:

```php
return [
    'default' => 'en',
    'fallback' => 'en',
    'supported' => [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
    ],
    'show_in_url' => true,
    'hide_default' => false,
];
```

URLs will automatically include language prefixes:
- `/en/portfolio/my-project`
- `/it/portfolio/mio-progetto`
- `/es/portafolio/mi-proyecto`

## 🎨 Customizing the Admin Panel

### Add Custom Dashboard Widgets

Create a Blade component in `resources/views/admin/widgets/`:

```blade
<!-- resources/views/admin/widgets/stats.blade.php -->
<div class="card">
    <div class="card-body">
        <h5>Content Statistics</h5>
        <p>Total Pages: {{ $totalPages }}</p>
        <p>Total Posts: {{ $totalPosts }}</p>
    </div>
</div>
```

Register in `resources/views/admin/dashboard.blade.php`.

### Customize Form Fields

Override the default field template by creating:
`resources/views/admin/content/fields/{field-type}.blade.php`

## 🔌 API Usage (Headless Mode)

ELKCMS can be used as a headless CMS via its REST API:

### Get All Items

```http
GET /api/v1/portfolio?locale=en
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "My Project",
      "description": "Project description...",
      "featured_image": "https://...",
      "slug": "my-project",
      "published_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 10
  }
}
```

### Get Single Item

```http
GET /api/v1/portfolio/my-project?locale=en
```

## 🛠️ Useful Commands

```bash
# Create a new content model
php artisan cms:make-model ModelName

# Generate migrations from models
php artisan cms:generate-migrations

# Clear CMS caches
php artisan cms:cache-clear
php artisan cms:cache-clear --type=content

# Warm caches (after deployment)
php artisan cms:cache-warm

# Create admin user
php artisan cms:make-admin

# Run backups
php artisan backup:run
```

## 📊 Performance Tips

1. **Enable caching** in `.env`:
   ```
   CMS_CACHE_ENABLED=true
   CMS_CACHE_DRIVER=file  # or 'database'
   ```

2. **Warm caches after deployment**:
   ```bash
   php artisan cms:cache-warm
   ```

3. **Use WebP images**: The media library automatically generates WebP versions

4. **Enable OPcache** in production PHP configuration

5. **Use a CDN** for media files

## 🐳 Docker Commands

```bash
# Start development environment
docker-compose up -d

# Stop environment
docker-compose down

# View logs
docker-compose logs -f app

# Access application container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cms:cache-clear

# Access database
docker-compose exec db mysql -u elkcms -p

# Rebuild containers
docker-compose up -d --build

# Install/update dependencies
docker-compose exec app composer install
docker-compose exec node npm install
```

## 🚀 Deployment

ELKCMS uses [Deployer](https://deployer.org/) for zero-downtime deployments.

```bash
# Deploy to production
dep deploy production

# Deploy to staging
dep deploy staging

# Rollback if needed
dep rollback production

# SSH into server
dep ssh production

# Run remote commands
dep artisan:migrate production
dep cms:cache-warm production
```

See [deploy.php](deploy.php) for configuration.

## 🎯 Roadmap

- [ ] GraphQL API support
- [ ] ElasticSearch integration for advanced search
- [ ] Multi-site support
- [ ] Workflow & approval system
- [ ] A/B testing for content
- [ ] Advanced analytics integration
- [ ] Page builder: more pre-built blocks
- [ ] Mobile app for content management

## 🤝 Contributing

Contributions are welcome! Please read our [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

ELKCMS is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

Built with love using:
- [Laravel](https://laravel.com/)
- [Bootstrap](https://getbootstrap.com/)
- [Isolated Block Editor](https://github.com/Automattic/isolated-block-editor)
- [Intervention Image](http://image.intervention.io/)
- [Vite](https://vitejs.dev/)

Inspired by:
- WordPress (simplicity)
- WPML (multilanguage)
- Yoast SEO (SEO features)
- Strapi (headless CMS concepts)

## 📞 Support

- Documentation: [https://docs.elkcms.dev](https://docs.elkcms.dev)
- Issues: [GitHub Issues](https://github.com/kokiddp/elkcms/issues)
- Discussions: [GitHub Discussions](https://github.com/kokiddp/elkcms/discussions)

---

Made with ❤️ by Gabriele Coquillard (ELK-Lab)
