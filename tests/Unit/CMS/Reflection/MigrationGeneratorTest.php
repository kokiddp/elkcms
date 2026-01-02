<?php

namespace Tests\Unit\CMS\Reflection;

use App\CMS\ContentModels\TestPost;
use App\CMS\Reflection\MigrationGenerator;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MigrationGeneratorTest extends TestCase
{
    protected MigrationGenerator $generator;

    protected string $testOutputPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MigrationGenerator();
        $this->testOutputPath = storage_path('framework/testing/migrations');

        // Clean up test directory
        if (File::exists($this->testOutputPath)) {
            File::cleanDirectory($this->testOutputPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        if (File::exists($this->testOutputPath)) {
            File::deleteDirectory($this->testOutputPath);
        }

        parent::tearDown();
    }

    public function test_can_generate_migration_file(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);

        $this->assertFileExists($filepath);
        $this->assertStringContainsString('create_test_posts_table', $filepath);
    }

    public function test_migration_file_has_correct_structure(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString('use Illuminate\Database\Migrations\Migration;', $content);
        $this->assertStringContainsString('use Illuminate\Database\Schema\Blueprint;', $content);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Schema;', $content);
        $this->assertStringContainsString('public function up(): void', $content);
        $this->assertStringContainsString('public function down(): void', $content);
    }

    public function test_migration_creates_correct_table(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("Schema::create('test_posts'", $content);
        $this->assertStringContainsString("Schema::dropIfExists('test_posts'", $content);
    }

    public function test_migration_includes_all_fields(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("\$table->string('title', 200)", $content);
        $this->assertStringContainsString("\$table->text('content')", $content);
        $this->assertStringContainsString("\$table->string('featured_image')", $content);
        $this->assertStringContainsString("\$table->datetime('published_at')", $content);
    }

    public function test_migration_includes_slug_for_seo_support(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("\$table->string('slug')->unique()", $content);
    }

    public function test_migration_includes_status_for_public_models(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("\$table->string('status')->default('draft')->index()", $content);
    }

    public function test_migration_includes_timestamps(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("\$table->timestamps()", $content);
    }

    public function test_migration_includes_id_column(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $content = File::get($filepath);

        $this->assertStringContainsString("\$table->id()", $content);
    }

    public function test_migration_filename_has_timestamp(): void
    {
        $filepath = $this->generator->generate(TestPost::class, $this->testOutputPath);
        $filename = basename($filepath);

        $this->assertMatchesRegularExpression('/^\d{4}_\d{2}_\d{2}_\d{6}_create_test_posts_table\.php$/', $filename);
    }

    public function test_creates_output_directory_if_not_exists(): void
    {
        $customPath = storage_path('framework/testing/custom_migrations');

        $this->assertDirectoryDoesNotExist($customPath);

        $filepath = $this->generator->generate(TestPost::class, $customPath);

        $this->assertDirectoryExists($customPath);
        $this->assertFileExists($filepath);

        // Cleanup
        File::deleteDirectory($customPath);
    }
}
