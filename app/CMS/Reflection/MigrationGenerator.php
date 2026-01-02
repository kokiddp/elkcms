<?php

namespace App\CMS\Reflection;

use Illuminate\Support\Str;

class MigrationGenerator
{
    protected ModelScanner $scanner;

    protected FieldAnalyzer $analyzer;

    public function __construct()
    {
        $this->scanner = new ModelScanner();
        $this->analyzer = new FieldAnalyzer();
    }

    /**
     * Generate a Laravel migration file for a content model.
     *
     * @param  string  $modelClass  Fully qualified class name
     * @param  string|null  $outputPath  Migration file output path (default: database/migrations/cms)
     * @return string Path to generated migration file
     *
     * @throws \ReflectionException
     */
    public function generate(string $modelClass, ?string $outputPath = null): string
    {
        $definition = $this->scanner->scan($modelClass, useCache: false);

        $tableName = $this->getTableName($definition);
        $migrationName = 'create_'.$tableName.'_table';
        $className = Str::studly($migrationName);

        $outputPath = $outputPath ?? database_path('migrations/cms');

        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";
        $filepath = "{$outputPath}/{$filename}";

        $content = $this->generateMigrationContent($className, $tableName, $definition);

        file_put_contents($filepath, $content);

        return $filepath;
    }

    /**
     * Generate the migration file content.
     *
     * @param  string  $className
     * @param  string  $tableName
     * @param  array  $definition
     * @return string
     */
    protected function generateMigrationContent(string $className, string $tableName, array $definition): string
    {
        $upMethods = $this->generateUpMethods($definition);
        $indexes = $this->generateIndexes($definition);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$upMethods}
{$indexes}
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};

PHP;
    }

    /**
     * Generate field definition methods for the up() migration.
     *
     * @param  array  $definition
     * @return string
     */
    protected function generateUpMethods(array $definition): string
    {
        $lines = [];

        foreach ($definition['fields'] as $field) {
            $analyzed = $this->analyzer->analyze($field);
            $lines[] = '            '.$analyzed['migrationMethod'];
        }

        // Add relationships foreign keys
        foreach ($definition['relationships'] ?? [] as $relationship) {
            if ($relationship['type'] === 'belongsTo') {
                $foreignKey = $relationship['foreignKey'] ?? Str::snake($relationship['name']).'_id';
                $lines[] = "            \$table->foreignId('{$foreignKey}')->nullable()->constrained();";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Generate index definitions.
     *
     * @param  array  $definition
     * @return string
     */
    protected function generateIndexes(array $definition): string
    {
        $lines = [];

        // Add slug index if model supports SEO
        $contentModel = $definition['contentModel'] ?? null;
        if ($contentModel && in_array('seo', $contentModel['supports'] ?? [], true)) {
            $lines[] = "            \$table->string('slug')->unique();";
        }

        // Add status index for published content
        if ($contentModel && $contentModel['public']) {
            $lines[] = "            \$table->string('status')->default('draft')->index();";
        }

        return empty($lines) ? '' : "\n".implode("\n", $lines);
    }

    /**
     * Generate a pivot table migration for belongsToMany relationships.
     *
     * @param  string  $modelClass
     * @param  string  $relationshipName
     * @param  string|null  $outputPath
     * @return string|null
     *
     * @throws \ReflectionException
     */
    public function generatePivotMigration(string $modelClass, string $relationshipName, ?string $outputPath = null): ?string
    {
        $definition = $this->scanner->scan($modelClass, useCache: false);

        $relationship = $definition['relationships'][$relationshipName] ?? null;

        if (! $relationship || ! $relationship['requiresPivot']) {
            return null;
        }

        $pivotTable = $relationship['pivotTable'] ?? $this->derivePivotTableName($definition, $relationship);

        $outputPath = $outputPath ?? database_path('migrations/cms');

        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $migrationName = 'create_'.$pivotTable.'_table';
        $filename = "{$timestamp}_{$migrationName}.php";
        $filepath = "{$outputPath}/{$filename}";

        $content = $this->generatePivotMigrationContent($pivotTable, $definition, $relationship);

        file_put_contents($filepath, $content);

        return $filepath;
    }

    /**
     * Generate pivot table migration content.
     *
     * @param  string  $pivotTable
     * @param  array  $modelDefinition
     * @param  array  $relationship
     * @return string
     */
    protected function generatePivotMigrationContent(string $pivotTable, array $modelDefinition, array $relationship): string
    {
        $modelTable = $this->getTableName($modelDefinition);
        $relatedTable = Str::snake(Str::pluralStudly(class_basename($relationship['model'])));

        $modelForeignKey = Str::singular($modelTable).'_id';
        $relatedForeignKey = Str::singular($relatedTable).'_id';

        $pivotFields = '';
        foreach ($relationship['pivotFields'] as $field) {
            $pivotFields .= "            \$table->{$field['type']}('{$field['name']}')";
            if ($field['nullable'] ?? false) {
                $pivotFields .= '->nullable()';
            }
            $pivotFields .= ";\n";
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$pivotTable}', function (Blueprint \$table) {
            \$table->foreignId('{$modelForeignKey}')->constrained('{$modelTable}')->onDelete('cascade');
            \$table->foreignId('{$relatedForeignKey}')->constrained('{$relatedTable}')->onDelete('cascade');
{$pivotFields}
            \$table->primary(['{$modelForeignKey}', '{$relatedForeignKey}']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$pivotTable}');
    }
};

PHP;
    }

    /**
     * Get the database table name for a content model.
     *
     * @param  array  $definition
     * @return string
     */
    protected function getTableName(array $definition): string
    {
        // Use route prefix if specified, otherwise pluralize short name
        $contentModel = $definition['contentModel'] ?? null;

        if ($contentModel && $contentModel['routePrefix']) {
            return Str::snake($contentModel['routePrefix']);
        }

        return Str::snake(Str::pluralStudly($definition['shortName']));
    }

    /**
     * Derive pivot table name from two models.
     *
     * @param  array  $modelDefinition
     * @param  array  $relationship
     * @return string
     */
    protected function derivePivotTableName(array $modelDefinition, array $relationship): string
    {
        $tables = [
            Str::snake($modelDefinition['shortName']),
            Str::snake(class_basename($relationship['model'])),
        ];

        sort($tables);

        return implode('_', $tables);
    }
}
