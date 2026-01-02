# Caching Strategy - ELKCMS

**Last Updated:** 2026-01-02
**Applies To:** Phase 1-3
**Status:** Comprehensive

---

## Overview

This document defines all caching strategies, cache key formats, TTL values, and invalidation patterns used throughout ELKCMS.

---

## Cache Configuration

### Primary Settings

**File:** `config/cms.php`

```php
return [
    'cache' => [
        // Global cache enable/disable
        'enabled' => env('CMS_CACHE_ENABLED', true),

        // Cache driver (file, redis, memcached, database)
        'driver' => env('CACHE_DRIVER', 'file'),

        // Default TTL (in seconds)
        'ttl' => [
            'default' => env('CMS_CACHE_TTL', 3600),       // 1 hour
            'models' => env('CMS_CACHE_TTL_MODELS', 3600), // 1 hour
            'translations' => env('CMS_CACHE_TTL_TRANSLATIONS', 7200), // 2 hours
            'content' => env('CMS_CACHE_TTL_CONTENT', 1800), // 30 minutes
            'seo' => env('CMS_CACHE_TTL_SEO', 3600),       // 1 hour
        ],
    ],
];
```

---

## Cache Key Formats

### Standard Format

All cache keys follow this pattern:
```
cms_{component}_{identifier}_{locale?}
```

Where:
- `cms` = namespace prefix (prevents collisions)
- `{component}` = what is being cached (translations, content, models, etc.)
- `{identifier}` = unique identifier (model ID, slug, class name, etc.)
- `{locale}` = optional locale code for multilingual content

---

### 1. Translation Caches

#### Individual Model Translations
**Key Format:** `cms_translations_{model_basename}_{model_id}_{locale}`
**TTL:** 7200 seconds (2 hours)
**Example:** `cms_translations_TestPost_42_it`

```php
// app/CMS/Services/TranslationService.php
protected function getTranslationCacheKey(Model $model, string $locale): string
{
    return sprintf(
        'cms_translations_%s_%d_%s',
        class_basename($model),
        $model->id,
        $locale
    );
}

// Usage
$cacheKey = 'cms_translations_TestPost_42_it';
$ttl = config('cms.cache.ttl.translations', 7200);
Cache::remember($cacheKey, $ttl, fn() => $model->translations);
```

#### All Model Translations (All Locales)
**Key Format:** `cms_translations_{model_basename}_{model_id}_all`
**TTL:** 7200 seconds (2 hours)
**Example:** `cms_translations_TestPost_42_all`

```php
$cacheKey = sprintf('cms_translations_%s_%d_all', class_basename($model), $model->id);
Cache::remember($cacheKey, 7200, fn() => $model->getAllTranslations());
```

#### Translation Statistics
**Key Format:** `cms_translation_stats`
**TTL:** 3600 seconds (1 hour)

```php
$cacheKey = 'cms_translation_stats';
Cache::remember($cacheKey, 3600, fn() => $this->getTranslationStats());
```

---

### 2. Model Metadata Caches

#### Scanned Model Definition
**Key Format:** `cms_model_scan_{full_class_name}`
**TTL:** 3600 seconds (1 hour)
**Example:** `cms_model_scan_App\CMS\ContentModels\TestPost`

```php
// app/CMS/Reflection/ModelScanner.php
protected function getCacheKey(string $modelClass): string
{
    return 'cms_model_scan_' . str_replace('\\', '_', $modelClass);
}

// Usage
$cacheKey = 'cms_model_scan_App_CMS_ContentModels_TestPost';
Cache::remember($cacheKey, 3600, fn() => $this->scanModel($modelClass));
```

#### All Scanned Models
**Key Format:** `cms_models_all`
**TTL:** 3600 seconds (1 hour)

```php
$cacheKey = 'cms_models_all';
Cache::remember($cacheKey, 3600, fn() => $this->scanAllModels());
```

---

### 3. Content Caches

#### Individual Content by ID
**Key Format:** `cms_content_{model_basename}_{id}`
**TTL:** 1800 seconds (30 minutes)
**Example:** `cms_content_Page_15`

```php
// app/CMS/Repositories/ContentRepository.php
$cacheKey = sprintf('cms_content_%s_%d', class_basename($this->modelClass), $id);
Cache::remember($cacheKey, 1800, fn() => $this->query->find($id));
```

#### Content by Slug
**Key Format:** `cms_content_{model_basename}_slug_{slug}`
**TTL:** 1800 seconds (30 minutes)
**Example:** `cms_content_Page_slug_about-us`

```php
$cacheKey = sprintf('cms_content_%s_slug_%s', class_basename($this->modelClass), $slug);
Cache::remember($cacheKey, 1800, fn() => $this->query->where('slug', $slug)->first());
```

#### Paginated Content Lists
**Key Format:** `cms_content_{model_basename}_page_{page}_per_{perPage}`
**TTL:** 1800 seconds (30 minutes)
**Example:** `cms_content_Post_page_1_per_15`

```php
$cacheKey = sprintf('cms_content_%s_page_%d_per_%d',
    class_basename($this->modelClass),
    $page,
    $perPage
);
Cache::remember($cacheKey, 1800, fn() => $this->query->paginate($perPage));
```

#### Custom Query Results
**Key Format:** Custom, provided by caller
**TTL:** Custom, provided by caller
**Example:** `published_posts_homepage`

```php
// Fluent interface allows custom cache keys
$repository->cache('published_posts_homepage', 3600)->where('status', 'published')->get();
```

---

### 4. SEO Caches

#### Schema.org JSON-LD
**Key Format:** `cms_schema_{model_basename}_{id}_{locale}`
**TTL:** 3600 seconds (1 hour)
**Example:** `cms_schema_Page_10_en`

```php
$cacheKey = sprintf('cms_schema_%s_%d_%s',
    class_basename($model),
    $model->id,
    app()->getLocale()
);
Cache::remember($cacheKey, 3600, fn() => $this->generateSchema($model));
```

#### Sitemap XML
**Key Format:** `cms_sitemap_{locale}`
**TTL:** 86400 seconds (24 hours)
**Example:** `cms_sitemap_en`

```php
$cacheKey = sprintf('cms_sitemap_%s', $locale);
Cache::remember($cacheKey, 86400, fn() => $this->generateSitemap($locale));
```

---

## Cache Invalidation Patterns

### 1. Automatic Invalidation (Model Events)

```php
// app/CMS/Traits/OptimizedQueries.php
protected static function bootOptimizedQueries(): void
{
    static::saved(function ($model) {
        $model->flushCache();
    });

    static::deleted(function ($model) {
        $model->flushCache();
    });
}

public function flushCache(): void
{
    if (! config('cms.cache.enabled', true)) {
        return;
    }

    // Clear individual model cache
    $cacheKey = $this->getCacheKey();
    Cache::forget($cacheKey);

    // Clear related caches
    // - Translation caches
    // - Content list caches
    // - SEO caches
}
```

---

### 2. Manual Invalidation (Translation Changes)

```php
// app/CMS/Services/TranslationService.php
public function clearTranslationCache(Model $model, ?string $locale = null): void
{
    if (! config('cms.cache.enabled', true)) {
        return;
    }

    if ($locale) {
        // Clear specific locale
        $key = $this->getTranslationCacheKey($model, $locale);
        Cache::forget($key);
    } else {
        // Clear all locales
        $supportedLocales = config('languages.supported', ['en']);
        foreach ($supportedLocales as $loc) {
            $key = $this->getTranslationCacheKey($model, $loc);
            Cache::forget($key);
        }
    }

    // Clear "all translations" cache
    $allKey = sprintf('cms_translations_%s_%d_all',
        class_basename($model),
        $model->id
    );
    Cache::forget($allKey);
}
```

---

### 3. Bulk Invalidation (Admin Actions)

```php
// app/Console/Commands/ClearCmsCache.php
public function handle(): int
{
    $type = $this->option('type');

    $cleared = match($type) {
        'models' => $this->clearModelsCache(),
        'translations' => $this->clearTranslationsCache(),
        'content' => $this->clearContentCache(),
        'all' => $this->clearAllCache(),
        default => 0,
    };

    $this->info("Cleared {$cleared} cache entries");
    return self::SUCCESS;
}

protected function clearTranslationsCache(): int
{
    $count = 0;

    // Get all cache keys matching pattern
    $pattern = 'cms_translations_*';

    // For file/database cache, need to iterate
    // For Redis, can use KEYS command (performance concern in production)

    if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
        $redis = Cache::getStore()->getRedis();
        $keys = $redis->keys($pattern);

        foreach ($keys as $key) {
            Cache::forget($key);
            $count++;
        }
    } else {
        // For file/database cache, clear all to be safe
        Cache::flush();
        $count = 1;
    }

    return $count;
}
```

---

### 4. Tag-Based Invalidation (Future - Phase 6)

```php
// app/CMS/Services/TranslationService.php
public function cacheTranslations(Model $model, string $locale): Collection
{
    $cacheKey = $this->getTranslationCacheKey($model, $locale);
    $tags = [
        'translations',
        'model_' . $model->id,
        'locale_' . $locale,
    ];

    return Cache::tags($tags)->remember($cacheKey, 7200, function () use ($model, $locale) {
        return $model->getTranslations($locale);
    });
}

// Clear all translations for a model
public function clearModelTranslations(Model $model): void
{
    Cache::tags('model_' . $model->id)->flush();
}

// Clear all translations for a locale
public function clearLocaleTranslations(string $locale): void
{
    Cache::tags('locale_' . $locale)->flush();
}

// Clear all translation caches
public function clearAllTranslations(): void
{
    Cache::tags('translations')->flush();
}
```

**Note:** Tag-based caching requires Redis or Memcached driver. File and database drivers do not support tags.

---

## Cache Warming

### Pre-populate Caches for Performance

```php
// app/Console/Commands/WarmCache.php
public function handle(): int
{
    $type = $this->option('type');

    match($type) {
        'models' => $this->warmModelsCache(),
        'content' => $this->warmContentCache(),
        'translations' => $this->warmTranslationsCache(),
        'all' => $this->warmAllCaches(),
    };

    return self::SUCCESS;
}

protected function warmTranslationsCache(): void
{
    $modelClasses = $this->getAllowedModelClasses();
    $locales = config('languages.supported', ['en']);

    foreach ($modelClasses as $class) {
        $models = $class::all();

        $this->withProgressBar($models, function ($model) use ($locales) {
            foreach ($locales as $locale) {
                // This will populate the cache
                $this->service->cacheTranslations($model, $locale);
            }
        });
    }
}
```

---

## Cache Bypassing

### Fresh Queries (Ignore Cache)

```php
// app/CMS/Repositories/ContentRepository.php
public function fresh(): self
{
    $this->bypassCache = true;
    return $this;
}

// Usage
$repository->fresh()->find($id); // Skips cache, always hits DB
```

---

## Performance Considerations

### TTL Selection Guidelines

| Data Type | TTL | Rationale |
|-----------|-----|-----------|
| **Static Content** (About Us page) | 24 hours | Rarely changes |
| **Dynamic Content** (Blog posts) | 30 minutes | Changes occasionally |
| **Translations** | 2 hours | Changes infrequently |
| **Model Metadata** | 1 hour | Changes only during deployments |
| **SEO Data** | 1 hour | Can tolerate slight staleness |
| **User Sessions** | 2 hours | Standard session length |
| **API Responses** | 5 minutes | Frequently updated |

---

### Cache Hit Rate Monitoring (Future)

```php
// Log cache performance
Log::info('Cache hit', [
    'key' => $cacheKey,
    'hit' => Cache::has($cacheKey),
    'ttl' => $ttl,
]);

// Track hit/miss ratio
Cache::increment('cache_hits');
Cache::increment('cache_attempts');

$hitRate = Cache::get('cache_hits') / Cache::get('cache_attempts');
```

---

## Testing with Caches

### Disable Caching in Tests

**File:** `phpunit.xml`

```xml
<env name="CMS_CACHE_ENABLED" value="false"/>
```

This ensures tests are deterministic and don't depend on cache state.

---

### Test Cache Behavior

```php
public function test_cache_translations_stores_in_cache(): void
{
    Config::set('cms.cache.enabled', true);

    $post = TestPost::create(['title' => 'Test', 'status' => 'published']);
    $post->setTranslation('title', 'it', 'Titolo');

    // First call should cache
    $this->service->cacheTranslations($post, 'it');

    // Check cache has the key
    $cacheKey = $this->service->getTranslationCacheKey($post, 'it');
    $this->assertTrue(Cache::has($cacheKey));

    // Cache value should match
    $cached = Cache::get($cacheKey);
    $this->assertCount(1, $cached);
}
```

---

## Cache Drivers

### Recommended Drivers by Environment

| Environment | Driver | Rationale |
|-------------|--------|-----------|
| **Development** | File | Simple, no dependencies |
| **Testing** | Array (disabled) | Fast, isolated |
| **Staging** | Redis | Production-like |
| **Production** | Redis | Fast, supports tags, distributed |
| **High Traffic** | Redis Cluster | Scalable, redundant |

---

### Driver Configuration

**File:** `.env`

```bash
# Development
CACHE_DRIVER=file
CMS_CACHE_ENABLED=true

# Testing
CACHE_DRIVER=array
CMS_CACHE_ENABLED=false

# Production
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=secret
REDIS_PORT=6379
REDIS_DB=0
CMS_CACHE_ENABLED=true
```

---

## Cache Key Index

Quick reference for all cache keys in ELKCMS:

| Component | Key Pattern | TTL | Invalidated By |
|-----------|-------------|-----|----------------|
| Translation (single locale) | `cms_translations_{model}_{id}_{locale}` | 2h | Model save, translation change |
| Translation (all locales) | `cms_translations_{model}_{id}_all` | 2h | Model save, translation change |
| Translation stats | `cms_translation_stats` | 1h | Any translation change |
| Model scan | `cms_model_scan_{class}` | 1h | Code deployment, cache clear |
| All models | `cms_models_all` | 1h | Code deployment, cache clear |
| Content by ID | `cms_content_{model}_{id}` | 30m | Model save/delete |
| Content by slug | `cms_content_{model}_slug_{slug}` | 30m | Model save/delete |
| Content list | `cms_content_{model}_page_{p}_per_{n}` | 30m | Any model save/delete |
| Schema.org | `cms_schema_{model}_{id}_{locale}` | 1h | Model save, SEO change |
| Sitemap | `cms_sitemap_{locale}` | 24h | Any published content change |

---

## Best Practices

### ✅ Do

- Always respect `CMS_CACHE_ENABLED` config
- Use descriptive cache keys with namespace prefix
- Set appropriate TTL based on data volatility
- Invalidate caches when underlying data changes
- Use cache warming for frequently accessed data
- Monitor cache hit rates in production
- Use tags for complex invalidation (when available)

### ❌ Don't

- Hard-code TTL values (use config)
- Cache user-specific data in shared cache
- Cache sensitive information (passwords, tokens)
- Assume cache will always be available (graceful fallback)
- Use very short TTLs (<60s) - defeats purpose
- Use very long TTLs (>24h) - risk stale data
- Forget to invalidate caches on updates

---

## Future Enhancements (Phase 6)

1. **Tag-Based Invalidation** - Redis/Memcached only
2. **Cache Monitoring Dashboard** - Hit rates, memory usage
3. **Intelligent Cache Warming** - Predict hot content
4. **CDN Integration** - Cache at edge for global performance
5. **Cache Compression** - Reduce memory footprint
6. **Automatic TTL Adjustment** - Based on access patterns

---

## Summary

**Current Status:**
- ✅ Consistent cache key format across all components
- ✅ Configurable TTL per component type
- ✅ Manual and automatic invalidation
- ✅ Cache warming support
- ✅ Bypass mechanism for fresh queries
- ✅ Disabled in testing for deterministic tests

**Cache Keys in Use:** 11 patterns
**Components Cached:** Translations, Models, Content, SEO
**Default TTL:** 1 hour (configurable per component)
**Invalidation:** Automatic on model save/delete

---

**Last Updated:** 2026-01-02
**Review Cycle:** After each phase completion
**Owner:** Development Team
