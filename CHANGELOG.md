# Changelog

All notable changes to ELKCMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

> **Development Plan:** See [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md) for detailed implementation progress and roadmap.

### Phase 1.4 & 1.5: Base Content Model, Traits & Configuration (2026-01-02) ✅
- BaseContent abstract class extending Eloquent Model
- HasTranslations trait with 8 translation methods
- HasSlug trait with automatic slug generation and uniqueness
- HasSEO trait with Schema.org JSON-LD and sitemap support
- OptimizedQueries trait for eager loading and caching
- config/cms.php with cache, media, SEO, admin, API configuration
- config/languages.php with multilanguage support (5 languages)
- TestPost model now extends BaseContent
- ModelScanner respects CMS_CACHE_ENABLED setting
- Testing: ✅ 147 tests passing (318 assertions)
  - 13 BaseContent tests
  - 23 HasSEO tests
  - 13 HasSlug tests
  - 13 HasTranslations tests
  - 12 OptimizedQueries tests

### Phase 1.3: Migration Generator (2026-01-02) ✅
- MigrationGenerator class for auto-generating Laravel migrations
- Support all field types with proper database column types
- Handle relationships (foreign keys, pivot tables)
- Auto-add slug and status columns based on model features
- Generated migration tested successfully with TestPost model
- Database schema verified (all columns, indexes, constraints)
- Updated .gitignore to commit CMS migrations per architecture decision

### Phase 1.2: Model Scanner & Reflection System (2026-01-02)
- ModelScanner class for extracting attribute metadata
- FieldAnalyzer for form types and migration method generation
- AttributeReader helper for common reflection operations
- Caching system (1 hour TTL) for scanned models
- Successfully tested with TestPost model

### Phase 1.1: PHP 8 Attributes System (2026-01-02)
- ContentModel attribute for defining model metadata
- Field attribute with 15+ types and validation
- Relationship attribute for Eloquent relationships
- SEO attribute with Schema.org and sitemap configuration
- TestPost model demonstrating all attribute types

### Initial Setup (2026-01-02)
- Project foundation with Laravel 11
- PHP 8.3 support with Xdebug 3.3.2
- Docker development environment (PHP, MySQL 8.0, Nginx, Node 20)
- Deployer deployment configuration
- Complete documentation (README, DEVELOPMENT, AGENTS, CONTRIBUTING)
- CI/CD with GitHub Actions
- Code quality tools (Pint, Larastan, ESLint)
- All dependencies installed (Spatie, Intervention Image, GrapesJS, etc.)

### Planned Features
- Base content model and traits
- Configuration files (cms.php, languages.php)
- Artisan commands for model generation
- Translation system (WPML-inspired)
- SEO analyzer (Yoast-like)
- Professional media library with image editing
- GrapesJS visual page builder
- Performance optimization (file/database caching)
- User roles and permissions
- Activity logging
- Automated backups

[Unreleased]: https://github.com/kokiddp/elkcms/commits/main
