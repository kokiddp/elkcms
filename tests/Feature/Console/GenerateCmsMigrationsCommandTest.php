<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerateCmsMigrationsCommandTest extends TestCase
{
    protected string $migrationPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrationPath = database_path('migrations/cms');

        // Clean up migrations directory
        if (File::exists($this->migrationPath)) {
            File::cleanDirectory($this->migrationPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        if (File::exists($this->migrationPath)) {
            File::cleanDirectory($this->migrationPath);
        }

        parent::tearDown();
    }

    public function test_discovers_content_models(): void
    {
        $this->artisan('cms:generate-migrations')
            ->expectsOutput('Discovering content models...')
            ->expectsOutputToContain('TestPost')
            ->expectsQuestion('Run migrations now?', false)
            ->assertExitCode(0);
    }

    public function test_generates_migrations_for_all_models(): void
    {
        $this->artisan('cms:generate-migrations')
            ->expectsOutput('Generating migrations:')
            ->expectsQuestion('Run migrations now?', false)
            ->assertExitCode(0);

        // Check that migration file was created
        $files = File::files($this->migrationPath);
        $this->assertNotEmpty($files);
    }

    public function test_can_generate_for_specific_model(): void
    {
        $this->artisan('cms:generate-migrations', ['model' => 'TestPost'])
            ->expectsOutputToContain('Found 1 model: TestPost')
            ->expectsQuestion('Run migrations now?', false)
            ->assertExitCode(0);
    }

    public function test_fresh_flag_deletes_existing_migrations(): void
    {
        // Create a dummy migration file
        File::ensureDirectoryExists($this->migrationPath);
        File::put($this->migrationPath.'/test_migration.php', '<?php // test');

        $this->artisan('cms:generate-migrations', ['--fresh' => true])
            ->expectsOutputToContain('Deleting')
            ->expectsQuestion('Run migrations now?', false)
            ->assertExitCode(0);

        // Check that old migration was deleted
        $this->assertFileDoesNotExist($this->migrationPath.'/test_migration.php');
    }

    public function test_fails_for_non_existent_model(): void
    {
        $this->artisan('cms:generate-migrations', ['model' => 'NonExistentModel'])
            ->expectsOutput("Model 'NonExistentModel' not found.")
            ->assertExitCode(1);
    }

    public function test_shows_success_message(): void
    {
        $this->artisan('cms:generate-migrations')
            ->expectsOutputToContain('generated successfully!')
            ->expectsQuestion('Run migrations now?', false)
            ->assertExitCode(0);
    }
}
