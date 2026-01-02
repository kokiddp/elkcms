<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('application', 'ELKCMS');
set('repository', 'git@github.com:kokiddp/elkcms.git');
set('keep_releases', 3);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', [
    'storage',
]);

// Writable dirs by web server
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// Hosts
host('production')
    ->set('hostname', 'your-production-server.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/elkcms')
    ->set('branch', 'main')
    ->set('php_version', '8.3');

host('staging')
    ->set('hostname', 'your-staging-server.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/elkcms-staging')
    ->set('branch', 'develop')
    ->set('php_version', '8.3');

// Tasks
desc('Build frontend assets');
task('npm:build', function () {
    cd('{{release_path}}');
    run('npm ci');
    run('npm run build');
});

desc('Warm CMS cache');
task('cms:cache-warm', function () {
    cd('{{release_path}}');
    run('php artisan cms:cache-warm');
});

desc('Generate CMS migrations');
task('cms:generate-migrations', function () {
    cd('{{release_path}}');
    run('php artisan cms:generate-migrations');
});

desc('Run database migrations');
task('artisan:migrate')->select('run');

// Hooks
after('deploy:vendors', 'npm:build');
after('artisan:migrate', 'cms:generate-migrations');
after('deploy:symlink', 'cms:cache-warm');

// Main task
desc('Deploy ELKCMS');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'npm:build',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:migrate',
    'cms:generate-migrations',
    'deploy:publish',
    'cms:cache-warm',
]);

// Zero-downtime deployment
after('deploy:failed', 'deploy:unlock');
