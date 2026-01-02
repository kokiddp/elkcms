# Error Handling Strategy - ELKCMS

**Last Updated:** 2026-01-02
**Applies To:** Phase 1-3
**Status:** Comprehensive

---

## Overview

This document defines the comprehensive error handling strategy for ELKCMS. It covers exception handling, error messages, logging, validation, and recovery patterns across all components.

---

## Core Principles

1. **Fail Explicitly** - Never silently fail; always return meaningful errors
2. **Fail Safely** - Errors should not expose sensitive data or create security vulnerabilities
3. **Fail Gracefully** - Provide helpful error messages and recovery paths
4. **Fail Consistently** - Use consistent patterns across the application

---

## Error Handling Patterns by Layer

### 1. Services Layer

**Pattern:** Try-catch with structured error responses

```php
// app/CMS/Services/TranslationService.php
public function importTranslations(string $format, mixed $data): array
{
    $result = [
        'imported' => 0,
        'errors' => [],
    ];

    try {
        // Business logic here
        // Validation
        if (! $modelClass || ! $modelId || ! $locale) {
            $result['errors'][] = 'Missing required fields';
            return $result;
        }

        // Security checks
        $allowedModels = $this->getAllowedModelClasses();
        if (! in_array($modelClass, $allowedModels)) {
            $result['errors'][] = 'Invalid or unauthorized model class';
            return $result;
        }

        // Successful operations
        $result['imported']++;
    } catch (\Exception $e) {
        $result['errors'][] = $e->getMessage();
    }

    return $result;
}
```

**Key Points:**
- Return structured arrays with `errors` key
- Use early returns for validation failures
- Catch generic exceptions at the top level
- Never expose stack traces to users
- Log exceptions for debugging

---

### 2. Repositories Layer

**Pattern:** Return null/false for not found, throw for errors

```php
// app/CMS/Repositories/ContentRepository.php
public function find(int $id): ?Model
{
    return $this->executeQuery(function () use ($id) {
        return $this->query->find($id); // Returns null if not found
    });
}

public function update(Model $model, array $data): bool
{
    if (! $model->exists) {
        return false; // Model doesn't exist, return false
    }

    return $model->update($data); // Laravel handles exceptions
}

public function delete(Model $model): bool
{
    if (! $model->exists) {
        return false;
    }

    return $model->delete();
}
```

**Key Points:**
- Return `null` for "not found" scenarios (expected)
- Return `false` for "cannot perform" scenarios (validation failure)
- Let Eloquent exceptions bubble up for database errors (unexpected)
- Don't catch exceptions unless you can handle them

---

### 3. Traits (HasTranslations, HasSlug, HasSEO)

**Pattern:** Throw exceptions for programmer errors, return safely for user errors

```php
// app/CMS/Traits/HasTranslations.php
public function setTranslation(string $field, string $locale, $value): self
{
    // Programmer error: trying to translate non-translatable field
    if (! $this->isTranslatable($field)) {
        throw new \InvalidArgumentException(
            "Field '{$field}' is not translatable on " . static::class
        );
    }

    // User error: unsupported locale (validation error)
    if (! in_array($locale, config('languages.supported', []))) {
        throw new \InvalidArgumentException("Unsupported locale: {$locale}");
    }

    // Programmer error: model must be saved first
    if (! $this->exists) {
        throw new \RuntimeException(
            'Model must be saved before adding translations'
        );
    }

    // Safe operation
    Translation::updateOrCreate(/* ... */);

    return $this;
}
```

**Key Points:**
- Throw `InvalidArgumentException` for bad arguments
- Throw `RuntimeException` for state errors
- Use descriptive error messages
- Reference the problematic class/field in messages

---

### 4. Middleware

**Pattern:** Abort with HTTP errors, log suspicious activity

```php
// app/Http/Middleware/LocaleMiddleware.php
public function handle(Request $request, Closure $next): Response
{
    $locale = $this->detectLocale($request);

    // Validate and sanitize
    if ($this->isValidLocale($locale)) {
        App::setLocale($locale);
    } else {
        // Don't fail, just use default
        $locale = config('languages.default', 'en');
        App::setLocale($locale);
    }

    // Continue processing
    return $next($request);
}
```

**Future AdminMiddleware Pattern:**
```php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (! auth()->user()->hasRole('admin')) {
        // Log unauthorized access attempt
        Log::warning('Unauthorized admin access attempt', [
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'path' => $request->path(),
        ]);

        abort(403, 'Unauthorized access to admin panel');
    }

    return $next($request);
}
```

---

### 5. Artisan Commands

**Pattern:** Use command return codes and output

```php
// app/Console/Commands/ClearCmsCache.php
public function handle(): int
{
    $type = $this->option('type');
    $all = $this->option('all');

    if (! $type && ! $all) {
        $this->error('Please specify --type or --all');
        $this->info('Usage: cms:cache-clear {--type=models|translations|content} {--all}');
        return self::FAILURE; // Exit code 1
    }

    try {
        $cleared = $this->clearCache($type);
        $this->info("Cache cleared: {$cleared} items");
        return self::SUCCESS; // Exit code 0
    } catch (\Exception $e) {
        $this->error('Failed to clear cache: ' . $e->getMessage());
        Log::error('Cache clear failed', [
            'exception' => $e,
            'type' => $type,
        ]);
        return self::FAILURE;
    }
}
```

**Key Points:**
- Return `self::SUCCESS` (0) on success
- Return `self::FAILURE` (1) on error
- Use `$this->error()` for error messages
- Use `$this->info()` for success messages
- Log exceptions for debugging

---

## Validation Strategies

### 1. Input Validation (Services)

```php
public function validateTranslations(array $translations, string $locale): array
{
    $errors = [];

    // Validate locale
    if (! in_array($locale, config('languages.supported', []))) {
        $errors[] = "Unsupported locale: {$locale}";
    }

    // Validate each field
    foreach ($translations as $field => $value) {
        // Empty check
        if (empty($value)) {
            $errors[] = "Translation for '{$field}' cannot be empty";
        }

        // Type check
        if (! is_string($value)) {
            $errors[] = "Translation for '{$field}' must be a string";
        }

        // Length check
        if (strlen($value) > 65535) { // TEXT column limit
            $errors[] = "Translation for '{$field}' exceeds maximum length";
        }
    }

    return $errors;
}
```

---

### 2. Security Validation

```php
protected function getAllowedModelClasses(): array
{
    // Whitelist pattern: only allow known content models
    $namespace = config('cms.models.namespace', 'App\\CMS\\ContentModels');
    $registered = config('cms.models.registered', []);

    if (! empty($registered)) {
        return $registered; // Explicit whitelist
    }

    // Dynamic scanning with security checks
    $modelsPath = app_path('CMS/ContentModels');
    $allowedModels = [];

    if (is_dir($modelsPath)) {
        $files = glob($modelsPath.'/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClass = $namespace.'\\'.$className;

            if (class_exists($fullClass)) {
                $reflection = new \ReflectionClass($fullClass);
                if (! $reflection->isAbstract()) {
                    $allowedModels[] = $fullClass;
                }
            }
        }
    }

    return $allowedModels;
}
```

**Security Principles:**
- Always use whitelists, never blacklists
- Validate against known-good values
- Reject unknown/suspicious input
- Log security violations

---

### 3. Database Validation (Eloquent)

```php
// Use Laravel's validation in FormRequests (Phase 4)
class StoreContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'slug' => 'nullable|string|unique:test_posts,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'status.in' => 'The status must be draft, published, or archived.',
        ];
    }
}
```

---

## Logging Strategy

### Log Levels and Usage

| Level | When to Use | Example |
|-------|-------------|---------|
| **emergency** | System unusable | Database connection lost permanently |
| **alert** | Immediate action required | Disk space critical, backups failing |
| **critical** | Critical conditions | Application component unavailable |
| **error** | Runtime errors | Exception during import, file not found |
| **warning** | Warning messages | Deprecated API usage, suspicious activity |
| **notice** | Normal but significant | User role changed, cache cleared |
| **info** | Informational | User logged in, content published |
| **debug** | Debug-level messages | Variable dump, SQL query log |

---

### Logging Examples

```php
use Illuminate\Support\Facades\Log;

// Error: Import failed
Log::error('Translation import failed', [
    'format' => 'json',
    'data_length' => strlen($data),
    'exception' => $e->getMessage(),
    'user_id' => auth()->id(),
]);

// Warning: Security check failed
Log::warning('Unauthorized model class in import', [
    'attempted_class' => $modelClass,
    'allowed_classes' => $allowedModels,
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
]);

// Info: Cache operation
Log::info('Cache cleared', [
    'type' => 'translations',
    'count' => $cleared,
    'user_id' => auth()->id(),
]);

// Debug: Query optimization
if (config('app.debug')) {
    Log::debug('Translation progress query', [
        'model' => get_class($model),
        'queries_count' => DB::getQueryLog(),
    ]);
}
```

---

## Exception Hierarchy

### Custom Exceptions (Future)

```php
namespace App\CMS\Exceptions;

// Base CMS exception
class CMSException extends \Exception {}

// Translation exceptions
class TranslationException extends CMSException {}
class UnsupportedLocaleException extends TranslationException {}
class NonTranslatableFieldException extends TranslationException {}

// Model exceptions
class ModelNotFoundException extends CMSException {}
class InvalidModelClassException extends CMSException {}

// Cache exceptions
class CacheException extends CMSException {}

// Usage:
public function setTranslation(string $field, string $locale, $value): self
{
    if (! $this->isTranslatable($field)) {
        throw new NonTranslatableFieldException(
            "Field '{$field}' is not translatable on " . static::class
        );
    }

    if (! in_array($locale, config('languages.supported', []))) {
        throw new UnsupportedLocaleException($locale);
    }

    // ...
}
```

---

## Error Messages Best Practices

### ✅ Good Error Messages

```php
// Specific, actionable, includes context
"Field 'title' is not translatable on App\CMS\ContentModels\Page"
"Unsupported locale 'xx'. Supported locales: en, it, de"
"Model must be saved before adding translations"
"Translation for 'content' exceeds maximum length (65535 characters)"
"Invalid or unauthorized model class: App\Models\User"
```

### ❌ Bad Error Messages

```php
// Vague, not actionable
"Error"
"Invalid input"
"Something went wrong"
"Translation failed"
"Not found"
```

---

## Recovery Patterns

### 1. Graceful Degradation

```php
// If cache fails, continue without cache
try {
    $cached = Cache::get($key);
    if ($cached) {
        return $cached;
    }
} catch (\Exception $e) {
    Log::warning('Cache retrieval failed, continuing without cache', [
        'exception' => $e->getMessage(),
    ]);
}

// Fetch from database (fallback)
$data = $this->repository->find($id);
```

---

### 2. Transaction Rollback

```php
public function bulkUpdate(Model $model, array $updates): int
{
    $count = 0;

    DB::transaction(function () use ($model, $updates, &$count) {
        foreach ($updates as $update) {
            // If any operation fails, entire transaction rolls back
            $translation = Translation::create([/* ... */]);
            $count++;
        }
    });

    return $count;
}
```

---

### 3. Retry Logic (Future)

```php
use Illuminate\Support\Facades\Retry;

public function uploadToS3(UploadedFile $file): string
{
    return Retry::times(3)
        ->sleep(1000) // 1 second
        ->exponentialBackoff()
        ->when(fn ($e) => $e instanceof ConnectionException)
        ->attempt(function () use ($file) {
            return Storage::disk('s3')->put('media', $file);
        });
}
```

---

## Testing Error Scenarios

### Example: Security Test

```php
public function test_import_rejects_unauthorized_model_class(): void
{
    $json = json_encode([
        'model_type' => 'App\\Models\\User', // Not whitelisted
        'model_id' => 1,
        'locale' => 'it',
        'translations' => ['name' => 'Hacker'],
    ]);

    $result = $this->service->importTranslations('json', $json);

    // Assert error response
    $this->assertArrayHasKey('errors', $result);
    $this->assertCount(1, $result['errors']);
    $this->assertStringContainsString('Invalid or unauthorized model class', $result['errors'][0]);
    $this->assertEquals(0, $result['imported']);
}
```

---

## Configuration

### Error Handling Settings

**config/cms.php:**
```php
return [
    'error_handling' => [
        // Log all errors to dedicated channel
        'log_channel' => env('CMS_LOG_CHANNEL', 'stack'),

        // Include stack trace in logs (only in development)
        'include_trace' => env('APP_DEBUG', false),

        // Report errors to external service (Sentry, Bugsnag, etc.)
        'report_external' => env('CMS_ERROR_REPORTING', false),

        // Retry failed operations
        'retry_enabled' => env('CMS_RETRY_ENABLED', true),
        'retry_times' => env('CMS_RETRY_TIMES', 3),
        'retry_sleep' => env('CMS_RETRY_SLEEP', 1000), // milliseconds
    ],
];
```

---

## Future Enhancements

### Phase 4 and Beyond

1. **Admin Error Pages**
   - Custom 404, 403, 500 error pages
   - Friendly error messages for end users
   - Contact support links

2. **Validation Error Display**
   - Inline form validation
   - Flash messages for errors
   - JavaScript validation before submit

3. **Error Reporting Integration**
   - Sentry integration for production errors
   - Slack notifications for critical errors
   - Email alerts for security violations

4. **Activity Logging (Spatie)**
   - Log all admin actions
   - Log failed login attempts
   - Log permission denials
   - Audit trail for compliance

---

## Checklist for New Components

When creating new components, ensure:

- [ ] All user input is validated
- [ ] Security-sensitive operations use whitelists
- [ ] Exceptions are caught and logged appropriately
- [ ] Error messages are helpful and specific
- [ ] Database operations use transactions where needed
- [ ] Tests cover error scenarios (not just happy paths)
- [ ] Sensitive data is never exposed in error messages
- [ ] Stack traces are never shown to users
- [ ] Failed operations return consistent error structures
- [ ] Critical errors are logged with context

---

## Summary

This error handling strategy ensures:

✅ **Consistency** - All components handle errors the same way
✅ **Security** - Errors don't expose sensitive information
✅ **Debuggability** - Comprehensive logging with context
✅ **User Experience** - Helpful error messages
✅ **Reliability** - Graceful degradation and recovery
✅ **Testability** - Error scenarios are tested

---

**Last Updated:** 2026-01-02
**Review Cycle:** After each phase completion
**Owner:** Development Team
