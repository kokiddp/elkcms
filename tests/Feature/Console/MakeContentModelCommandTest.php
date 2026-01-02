<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeContentModelCommandTest extends TestCase
{
    protected string $modelPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelPath = app_path('CMS/ContentModels');
    }

    protected function tearDown(): void
    {
        // Clean up test models
        $testModels = [
            'TestModel.php',
            'Portfolio.php',
            'Product.php',
        ];

        foreach ($testModels as $model) {
            $path = $this->modelPath.'/'.$model;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    public function test_creates_model_file(): void
    {
        $this->artisan('cms:make-model', ['name' => 'TestModel'])
            ->expectsQuestion('What is the label for this model?', 'Test Models')
            ->expectsQuestion('What icon should be used?', 'file')
            ->expectsQuestion('Features', 'seo')
            ->expectsQuestion('Schema.org type?', 'Thing')
            ->expectsQuestion('Sitemap priority (0.0-1.0)?', '0.5')
            ->expectsChoice('Sitemap change frequency?', 'monthly', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never',
            ])
            ->expectsConfirmation('Add a field?', 'no')
            ->expectsConfirmation('Generate migration for this model?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists($this->modelPath.'/TestModel.php');
    }

    public function test_fails_if_model_exists_without_force(): void
    {
        // Create a dummy model file
        File::ensureDirectoryExists($this->modelPath);
        File::put($this->modelPath.'/TestModel.php', '<?php // test');

        $this->artisan('cms:make-model', ['name' => 'TestModel'])
            ->expectsOutput('Model TestModel already exists. Use --force to overwrite.')
            ->assertExitCode(1);
    }

    public function test_force_flag_overwrites_existing(): void
    {
        // Create a dummy model file
        File::ensureDirectoryExists($this->modelPath);
        File::put($this->modelPath.'/TestModel.php', '<?php // old content');

        $this->artisan('cms:make-model', ['name' => 'TestModel', '--force' => true])
            ->expectsQuestion('What is the label for this model?', 'Test Models')
            ->expectsQuestion('What icon should be used?', 'file')
            ->expectsQuestion('Features', 'translations')
            ->expectsConfirmation('Add a field?', 'no')
            ->expectsConfirmation('Generate migration for this model?', 'no')
            ->assertExitCode(0);

        $content = File::get($this->modelPath.'/TestModel.php');
        $this->assertStringNotContainsString('old content', $content);
        $this->assertStringContainsString('class TestModel', $content);
    }

    public function test_generated_model_has_correct_structure(): void
    {
        $this->artisan('cms:make-model', ['name' => 'Portfolio'])
            ->expectsQuestion('What is the label for this model?', 'Portfolios')
            ->expectsQuestion('What icon should be used?', 'briefcase')
            ->expectsQuestion('Features', 'translations,seo,media')
            ->expectsQuestion('Schema.org type?', 'CreativeWork')
            ->expectsQuestion('Sitemap priority (0.0-1.0)?', '0.7')
            ->expectsChoice('Sitemap change frequency?', 'weekly', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never',
            ])
            ->expectsConfirmation('Add a field?', 'no')
            ->expectsConfirmation('Generate migration for this model?', 'no')
            ->assertExitCode(0);

        $content = File::get($this->modelPath.'/Portfolio.php');

        $this->assertStringContainsString('namespace App\CMS\ContentModels;', $content);
        $this->assertStringContainsString('use App\CMS\Attributes\ContentModel;', $content);
        $this->assertStringContainsString('use App\CMS\Attributes\SEO;', $content);
        $this->assertStringContainsString("label: 'Portfolios'", $content);
        $this->assertStringContainsString("icon: 'briefcase'", $content);
        $this->assertStringContainsString("supports: ['translations', 'seo', 'media']", $content);
        $this->assertStringContainsString("schemaType: 'CreativeWork'", $content);
        $this->assertStringContainsString('class Portfolio extends BaseContent', $content);
    }

    public function test_can_add_fields_to_model(): void
    {
        $this->artisan('cms:make-model', ['name' => 'Product'])
            ->expectsQuestion('What is the label for this model?', 'Products')
            ->expectsQuestion('What icon should be used?', 'shopping-cart')
            ->expectsQuestion('Features', 'translations,seo')
            ->expectsQuestion('Schema.org type?', 'Product')
            ->expectsQuestion('Sitemap priority (0.0-1.0)?', '0.8')
            ->expectsChoice('Sitemap change frequency?', 'daily', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never',
            ])
            ->expectsConfirmation('Add a field?', 'yes')
            ->expectsQuestion('Field name (e.g., title)', 'name')
            ->expectsChoice('Field type', 'string', [
                'string',
                'text',
                'integer',
                'boolean',
                'date',
                'datetime',
                'image',
                'file',
                'json',
                'select',
            ])
            ->expectsQuestion('Field label', 'Product Name')
            ->expectsConfirmation('Is it required?', 'yes')
            ->expectsConfirmation('Is it translatable?', 'yes')
            ->expectsQuestion('Max length', '200')
            ->expectsConfirmation('Add a field?', 'no')
            ->expectsConfirmation('Generate migration for this model?', 'no')
            ->assertExitCode(0);

        $content = File::get($this->modelPath.'/Product.php');

        $this->assertStringContainsString("type: 'string'", $content);
        $this->assertStringContainsString("label: 'Product Name'", $content);
        $this->assertStringContainsString('required: true', $content);
        $this->assertStringContainsString('translatable: true', $content);
        $this->assertStringContainsString('maxLength: 200', $content);
        $this->assertStringContainsString('public string $name;', $content);
    }

    public function test_shows_success_message(): void
    {
        $this->artisan('cms:make-model', ['name' => 'TestModel'])
            ->expectsQuestion('What is the label for this model?', 'Test Models')
            ->expectsQuestion('What icon should be used?', 'file')
            ->expectsQuestion('Features', 'seo')
            ->expectsQuestion('Schema.org type?', 'Thing')
            ->expectsQuestion('Sitemap priority (0.0-1.0)?', '0.5')
            ->expectsChoice('Sitemap change frequency?', 'monthly', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never',
            ])
            ->expectsConfirmation('Add a field?', 'no')
            ->expectsConfirmation('Generate migration for this model?', 'no')
            ->expectsOutputToContain('✓ Model created:')
            ->assertExitCode(0);
    }
}
