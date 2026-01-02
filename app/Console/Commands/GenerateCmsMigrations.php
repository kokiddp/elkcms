<?php

namespace App\Console\Commands;

use App\CMS\Reflection\MigrationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateCmsMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:generate-migrations {model? : Specific model to generate for} {--fresh : Delete existing migrations first} {--run : Run migrations after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate migrations for all or specific content models';

    /**
     * Migration output path
     */
    protected string $migrationPath;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->migrationPath = database_path('migrations/cms');

        $modelArg = $this->argument('model');
        $fresh = $this->option('fresh');
        $run = $this->option('run');

        // Handle fresh flag
        if ($fresh) {
            $this->handleFreshFlag();
        }

        $this->info('Discovering content models...');

        $models = $modelArg
            ? $this->getSingleModel($modelArg)
            : $this->discoverContentModels();

        if (empty($models)) {
            $this->error('No content models found.');

            return self::FAILURE;
        }

        $this->info('Found '.count($models).' model'.(count($models) > 1 ? 's' : '').': '.implode(', ', array_map('class_basename', $models)));
        $this->newLine();

        $this->info('Generating migrations:');

        $generator = new MigrationGenerator();
        $generated = [];

        foreach ($models as $modelClass) {
            $shortName = class_basename($modelClass);

            try {
                $filepath = $generator->generate($modelClass, $this->migrationPath);
                $relativePath = str_replace(base_path().'/', '', $filepath);
                $this->info("✓ {$shortName} → {$relativePath}");
                $generated[] = $filepath;
            } catch (\Exception $e) {
                $this->error("✗ {$shortName} → Error: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info(count($generated).' migration'.(count($generated) > 1 ? 's' : '').' generated successfully!');

        // Handle run flag
        if ($run || $this->confirm('Run migrations now?', false)) {
            $this->newLine();
            $this->call('migrate');
        }

        return self::SUCCESS;
    }

    /**
     * Handle fresh flag - delete existing migrations
     */
    protected function handleFreshFlag(): void
    {
        if (File::isDirectory($this->migrationPath)) {
            $files = File::files($this->migrationPath);

            if (! empty($files)) {
                $this->warn('Deleting '.count($files).' existing migration'.(count($files) > 1 ? 's' : '').'...');

                foreach ($files as $file) {
                    File::delete($file->getPathname());
                }

                $this->newLine();
            }
        }
    }

    /**
     * Get single model by name
     *
     * @param  string  $name
     * @return array
     */
    protected function getSingleModel(string $name): array
    {
        $namespace = config('cms.models.namespace', 'App\\CMS\\ContentModels');

        // Try with and without namespace
        $attempts = [
            $name,
            $namespace.'\\'.$name,
            $namespace.'\\'.ucfirst($name),
        ];

        foreach ($attempts as $attempt) {
            if (class_exists($attempt)) {
                return [$attempt];
            }
        }

        $this->error("Model '{$name}' not found.");

        return [];
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
