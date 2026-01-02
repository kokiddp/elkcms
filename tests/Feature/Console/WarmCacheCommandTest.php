<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class WarmCacheCommandTest extends TestCase
{
    public function test_can_warm_all_caches(): void
    {
        $this->artisan('cms:cache-warm')
            ->expectsOutput('Warming CMS caches...')
            ->expectsOutput('Models:')
            ->assertExitCode(0);
    }

    public function test_can_warm_models_cache_only(): void
    {
        $this->artisan('cms:cache-warm', ['--models' => true])
            ->expectsOutput('Warming CMS caches...')
            ->expectsOutput('Models:')
            ->assertExitCode(0);
    }

    public function test_can_warm_content_cache_only(): void
    {
        $this->artisan('cms:cache-warm', ['--content' => true])
            ->expectsOutput('Warming CMS caches...')
            ->expectsOutput('Content:')
            ->assertExitCode(0);
    }

    public function test_can_warm_translations_cache_only(): void
    {
        $this->artisan('cms:cache-warm', ['--translations' => true])
            ->expectsOutput('Warming CMS caches...')
            ->expectsOutput('Translations:')
            ->assertExitCode(0);
    }

    public function test_scans_test_post_model(): void
    {
        $this->artisan('cms:cache-warm', ['--models' => true])
            ->expectsOutputToContain('TestPost')
            ->assertExitCode(0);
    }

}
