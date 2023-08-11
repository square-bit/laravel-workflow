<?php

namespace Squarebit\Workflows;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WorkflowsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-workflow')
            ->hasConfigFile('workflow')
            ->hasMigration('create_laravel-workflow_table')
            ->runsMigrations();
    }
}
