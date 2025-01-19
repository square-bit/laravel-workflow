<?php

namespace Squarebit\Workflows\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Factories\UserFactory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;
use Squarebit\Workflows\Tests\Support\User;
use Squarebit\Workflows\WorkflowsServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Squarebit\\Workflows\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $migration = include __DIR__.'/../database/migrations/create_workflow_tables.php.stub';
        $migration->up();

        $this->actingAs(UserFactory::new()->create(['email' => 'test@user.com']));
    }

    protected function getPackageProviders($app): array
    {
        return [
            WorkflowsServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('workflow.user_model', \Squarebit\Workflows\Tests\Support\User::class);
        config()->set('workflow.allow_guests_to_transition', false);
        config()->set('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);
        config()->set('permission.column_names', [
            'role_pivot_key' => null, // default 'role_id',
            'permission_pivot_key' => null, // default 'permission_id',
            'model_morph_key' => 'model_id',
            'team_foreign_key' => 'team_id',
        ]);
        config()->set('permission.teams', false);
        config()->set('auth.providers.users.model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $from = __DIR__.'/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        $to = __DIR__.'/Support/2000_01_01_000000_create_permission_tables.php';
        File::copy($from, $to);

        $this->loadMigrationsFrom(__DIR__.'/Support');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadLaravelMigrations();
    }
}
