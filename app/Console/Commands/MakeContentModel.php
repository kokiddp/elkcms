<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeContentModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:make-model {name} {--force : Overwrite existing model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new content model with attributes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $force = $this->option('force');

        // Sanitize model name
        $modelName = Str::studly($name);
        $modelPath = app_path("CMS/ContentModels/{$modelName}.php");

        // Check if model already exists
        if (File::exists($modelPath) && ! $force) {
            $this->error("Model {$modelName} already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("Creating content model: {$modelName}");
        $this->newLine();

        // Gather model metadata
        $label = $this->ask('What is the label for this model?', Str::plural($modelName));
        $icon = $this->ask('What icon should be used?', 'file');

        // Ask for features
        $this->info('Which features should be supported? (comma-separated)');
        $this->comment('Available: translations, seo, media, blocks');
        $featuresInput = $this->ask('Features', 'translations,seo');
        $features = array_map('trim', explode(',', $featuresInput));

        // SEO configuration if SEO is enabled
        $seoConfig = null;
        if (in_array('seo', $features)) {
            $this->newLine();
            $this->info('SEO Configuration:');
            $schemaType = $this->ask('Schema.org type?', 'Thing');
            $sitemapPriority = $this->ask('Sitemap priority (0.0-1.0)?', '0.5');
            $sitemapChangeFreq = $this->choice('Sitemap change frequency?', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never',
            ], 'monthly');

            $seoConfig = [
                'schemaType' => $schemaType,
                'sitemapPriority' => $sitemapPriority,
                'sitemapChangeFreq' => $sitemapChangeFreq,
            ];
        }

        // Gather fields
        $fields = [];
        $this->newLine();
        $this->info('Now add fields to your model. Press Ctrl+C to finish.');
        $this->newLine();

        while ($this->confirm('Add a field?', true)) {
            $field = $this->gatherFieldDefinition();
            if ($field) {
                $fields[] = $field;
                $this->info("✓ Added field: {$field['name']}");
            }
        }

        // Generate model file
        $content = $this->generateModelContent($modelName, $label, $icon, $features, $seoConfig, $fields);

        // Ensure directory exists
        $directory = dirname($modelPath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Write file
        File::put($modelPath, $content);

        $this->newLine();
        $this->info("✓ Model created: {$modelPath}");

        // Ask to generate migration
        if ($this->confirm('Generate migration for this model?', true)) {
            $this->newLine();
            $this->call('cms:generate-migrations', ['model' => $modelName]);
        }

        return self::SUCCESS;
    }

    /**
     * Gather field definition from user input
     *
     * @return array|null
     */
    protected function gatherFieldDefinition(): ?array
    {
        $name = $this->ask('Field name (e.g., title)');

        if (! $name) {
            return null;
        }

        $type = $this->choice('Field type', [
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
        ], 'string');

        $label = $this->ask('Field label', Str::title(str_replace('_', ' ', $name)));
        $required = $this->confirm('Is it required?', true);
        $translatable = $this->confirm('Is it translatable?', in_array($type, ['string', 'text']));

        $field = [
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'required' => $required,
            'translatable' => $translatable,
        ];

        // Type-specific options
        if ($type === 'string') {
            $maxLength = $this->ask('Max length', '255');
            if ($maxLength) {
                $field['maxLength'] = (int) $maxLength;
            }
        }

        if ($type === 'select') {
            $options = $this->ask('Options (comma-separated)', 'option1,option2,option3');
            $field['options'] = array_map('trim', explode(',', $options));
        }

        return $field;
    }

    /**
     * Generate model file content
     *
     * @param  string  $modelName
     * @param  string  $label
     * @param  string  $icon
     * @param  array  $features
     * @param  array|null  $seoConfig
     * @param  array  $fields
     * @return string
     */
    protected function generateModelContent(
        string $modelName,
        string $label,
        string $icon,
        array $features,
        ?array $seoConfig,
        array $fields
    ): string {
        $content = "<?php\n\nnamespace App\\CMS\\ContentModels;\n\n";
        $content .= "use App\\CMS\\Attributes\\ContentModel;\n";
        $content .= "use App\\CMS\\Attributes\\Field;\n";

        if (in_array('seo', $features)) {
            $content .= "use App\\CMS\\Attributes\\SEO;\n";
        }

        $content .= "\n";

        // ContentModel attribute
        $featuresStr = "'".implode("', '", $features)."'";
        $content .= "#[ContentModel(\n";
        $content .= "    label: '{$label}',\n";
        $content .= "    icon: '{$icon}',\n";
        $content .= "    supports: [{$featuresStr}]\n";
        $content .= ")]\n";

        // SEO attribute
        if ($seoConfig) {
            $content .= "#[SEO(\n";
            $content .= "    schemaType: '{$seoConfig['schemaType']}',\n";
            $content .= "    sitemapPriority: '{$seoConfig['sitemapPriority']}',\n";
            $content .= "    sitemapChangeFreq: '{$seoConfig['sitemapChangeFreq']}'\n";
            $content .= ")]\n";
        }

        $content .= "class {$modelName} extends BaseContent\n{\n";

        // Add fields
        foreach ($fields as $field) {
            $content .= $this->generateFieldAttribute($field);
        }

        $content .= "}\n";

        return $content;
    }

    /**
     * Generate field attribute code
     *
     * @param  array  $field
     * @return string
     */
    protected function generateFieldAttribute(array $field): string
    {
        $code = "    #[Field(\n";
        $code .= "        type: '{$field['type']}',\n";
        $code .= "        label: '{$field['label']}',\n";
        $code .= "        required: ".($field['required'] ? 'true' : 'false');

        if ($field['translatable']) {
            $code .= ",\n        translatable: true";
        }

        if (isset($field['maxLength'])) {
            $code .= ",\n        maxLength: {$field['maxLength']}";
        }

        if (isset($field['options'])) {
            $optionsStr = "'".implode("', '", $field['options'])."'";
            $code .= ",\n        options: [{$optionsStr}]";
        }

        $code .= "\n    )]\n";

        // Determine PHP type
        $phpType = $this->getPhpType($field['type'], $field['required']);
        $code .= "    public {$phpType} \${$field['name']};\n\n";

        return $code;
    }

    /**
     * Get PHP type hint for field
     *
     * @param  string  $fieldType
     * @param  bool  $required
     * @return string
     */
    protected function getPhpType(string $fieldType, bool $required): string
    {
        $typeMap = [
            'string' => 'string',
            'text' => 'string',
            'integer' => 'int',
            'boolean' => 'bool',
            'date' => '\\DateTime',
            'datetime' => '\\DateTime',
            'image' => 'string',
            'file' => 'string',
            'json' => 'array',
            'select' => 'string',
        ];

        $phpType = $typeMap[$fieldType] ?? 'mixed';

        return $required ? $phpType : '?'.$phpType;
    }
}
