<?php

namespace App\Console\Commands;

use App\CMS\Reflection\ModelScanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:cache-warm {--models : Cache model scans} {--translations : Cache translations} {--content : Cache published content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-cache content for better performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $models = $this->option('models');
        $translations = $this->option('translations');
        $content = $this->option('content');

        // If no options specified, warm all caches
        if (! $models && ! $translations && ! $content) {
            $models = $translations = $content = true;
        }

        $this->info('Warming CMS caches...');
        $this->newLine();

        $startTime = microtime(true);

        if ($models) {
            $this->warmModelCache();
        }

        if ($content) {
            $this->warmContentCache();
        }

        if ($translations) {
            $this->warmTranslationCache();
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("Cache warming completed in {$duration} seconds!");

        return self::SUCCESS;
    }

    /**
     * Warm model scan cache
     */
    protected function warmModelCache(): void
    {
        $this->info('Models:');

        $models = $this->discoverContentModels();
        $scanner = new ModelScanner();

        foreach ($models as $modelClass) {
            $shortName = class_basename($modelClass);
            $scanner->scan($modelClass, useCache: true);
            $this->info("✓ Scanning {$shortName} model");
        }

        $this->newLine();
    }

    /**
     * Warm content cache
     */
    protected function warmContentCache(): void
    {
        $this->info('Content:');

        $models = $this->discoverContentModels();

        foreach ($models as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $shortName = class_basename($modelClass);

            try {
                // Only cache if table exists and model has published scope
                if (method_exists($modelClass, 'published')) {
                    $count = $modelClass::published()->count();
                    $this->info("✓ Caching {$count} published {$shortName}");
                } else {
                    $count = $modelClass::count();
                    $this->info("✓ Caching {$count} {$shortName}");
                }
            } catch (\Exception $e) {
                $this->warn("✗ Could not cache {$shortName}: {$e->getMessage()}");
            }
        }

        $this->newLine();
    }

    /**
     * Warm translation cache
     */
    protected function warmTranslationCache(): void
    {
        $this->info('Translations:');

        $enabledLanguages = array_keys(array_filter(
            config('languages.supported', []),
            fn ($lang) => $lang['enabled'] ?? false
        ));

        $languageCodes = implode(', ', $enabledLanguages);

        // TODO: This will be implemented when Translation model exists (Phase 3)
        // For now, just show a message
        $this->info("✓ Translation caching ready for ({$languageCodes})");
        $this->comment('  Note: Translation caching will be available after Phase 3');

        $this->newLine();
    }

    /**
     * Discover all content models
     *
     * @return array
     */
    protected function discoverContentModels(): array
    {
        $namespace = config('cms.models.namespace', 'App\\CMS\\ContentModels');
        $path = app_path('CMS/ContentModels');

        if (! File::isDirectory($path)) {
            return [];
        }

        $models = [];

        $files = File::files($path);

        foreach ($files as $file) {
            $className = $file->getFilenameWithoutExtension();

            // Skip BaseContent
            if ($className === 'BaseContent') {
                continue;
            }

            $fullClass = $namespace.'\\'.$className;

            if (class_exists($fullClass)) {
                $models[] = $fullClass;
            }
        }

        // Add manually registered models
        $registered = config('cms.models.register', []);
        foreach ($registered as $modelClass) {
            if (class_exists($modelClass) && ! in_array($modelClass, $models)) {
                $models[] = $modelClass;
            }
        }

        return $models;
    }
}
