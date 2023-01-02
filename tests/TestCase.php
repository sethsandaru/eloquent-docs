<?php

namespace SethPhat\EloquentDocs\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SethPhat\EloquentDocs\EloquentDocsServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EloquentDocsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        $dbConnection = env('DB_CONNECTION');
        $app['config']->set('database.default', $dbConnection);

        if ($dbConnection === 'mysql') {
            $app['config']->set('database.connections.mysql', [
                'driver' => $dbConnection,
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT', 3307),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'database' => env('DB_DATABASE'),
                'prefix' => '',
            ]);
        } elseif ($dbConnection === 'pgsql') {
            $app['config']->set('database.connections.pgsql', [
                'driver' => $dbConnection,
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT', 3307),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'database' => env('DB_DATABASE'),
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        } else {
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => env('DB_DATABASE', database_path('database.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]);
        }
    }

    protected function defineDatabaseMigrations()
    {
        $this->app['db']->connection(env('DB_CONNECTION'))
            ->getSchemaBuilder()
            ->dropAllTables();

        // create a fake table for assertions
        Schema::create('users', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('first_name');
            $blueprint->string('last_name');
            $blueprint->integer('age');
            $blueprint->decimal('profile_complete_percentage');
            $blueprint->json('payload')->nullable();
            $blueprint->json('additional_payload');
            $blueprint->json('external_data');
            $blueprint->enum('gender', ['F', 'M', 'O']);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        Schema::create('emails', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id');
            $blueprint->string('email');
        });

        Schema::create('user_details', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id');
            $blueprint->string('address');
            $blueprint->string('zone');
        });
    }
}