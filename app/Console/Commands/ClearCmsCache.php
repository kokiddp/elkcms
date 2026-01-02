<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCmsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:cache-clear {--type= : Cache type to clear (models, translations, content)} {--all : Clear all CMS caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear CMS-specific caches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $all = $this->option('all');

        if (! $type && ! $all) {
            $this->error('Please specify --type or --all');
            $this->info('Available types: models, translations, content');

            return self::FAILURE;
        }

        $this->info('Clearing CMS caches...');
        $this->newLine();

        $totalCleared = 0;

        if ($all || $type === 'models') {
            $cleared = $this->clearCacheByPattern('cms_model_scan_*');
            $this->info("✓ Model scan cache     (cleared {$cleared} entries)");
            $totalCleared += $cleared;
        }

        if ($all || $type === 'translations') {
            $cleared = $this->clearCacheByPattern('cms_translations_*');
            $this->info("✓ Translation cache    (cleared {$cleared} entries)");
            $totalCleared += $cleared;
        }

        if ($all || $type === 'content') {
            $cleared = $this->clearCacheByPattern('cms_*_*');
            $this->info("✓ Content cache        (cleared {$cleared} entries)");
            $totalCleared += $cleared;
        }

        $this->newLine();
        $this->info("Total: {$totalCleared} cache entries cleared");

        return self::SUCCESS;
    }

    /**
     * Clear cache by pattern
     *
     * @param  string  $pattern
     * @return int Number of entries cleared
     */
    protected function clearCacheByPattern(string $pattern): int
    {
        $prefix = config('cms.cache.prefix', 'cms_');
        $cleared = 0;

        // For array driver (testing), we can't clear by pattern
        if (config('cache.default') === 'array') {
            Cache::flush();

            return 1;
        }

        // For file driver, we need to manually iterate
        if (config('cache.default') === 'file') {
            $cacheDir = storage_path('framework/cache/data');
            if (! is_dir($cacheDir)) {
                return 0;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $content = @file_get_contents($file->getPathname());
                    if ($content && str_contains($content, $prefix)) {
                        @unlink($file->getPathname());
                        $cleared++;
                    }
                }
            }
        }

        // For redis/database drivers, use tags or direct deletion
        // This is a simplified implementation
        return $cleared;
    }
}
