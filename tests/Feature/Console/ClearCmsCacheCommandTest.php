<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ClearCmsCacheCommandTest extends TestCase
{
    public function test_requires_type_or_all_flag(): void
    {
        $this->artisan('cms:cache-clear')
            ->expectsOutput('Please specify --type or --all')
            ->assertExitCode(1);
    }

    public function test_can_clear_with_all_flag(): void
    {
        $this->artisan('cms:cache-clear', ['--all' => true])
            ->expectsOutput('Clearing CMS caches...')
            ->assertExitCode(0);
    }

    public function test_can_clear_models_cache(): void
    {
        $this->artisan('cms:cache-clear', ['--type' => 'models'])
            ->expectsOutput('Clearing CMS caches...')
            ->assertExitCode(0);
    }

    public function test_can_clear_translations_cache(): void
    {
        $this->artisan('cms:cache-clear', ['--type' => 'translations'])
            ->expectsOutput('Clearing CMS caches...')
            ->assertExitCode(0);
    }

    public function test_can_clear_content_cache(): void
    {
        $this->artisan('cms:cache-clear', ['--type' => 'content'])
            ->expectsOutput('Clearing CMS caches...')
            ->assertExitCode(0);
    }

    public function test_shows_total_cleared(): void
    {
        $this->artisan('cms:cache-clear', ['--all' => true])
            ->expectsOutputToContain('Total:')
            ->expectsOutputToContain('cleared')
            ->assertExitCode(0);
    }
}
