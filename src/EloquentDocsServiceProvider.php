<?php

namespace SethPhat\EloquentDocs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use SethPhat\EloquentDocs\Commands\EloquentDocsGeneratorCommand;

class EloquentDocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EloquentDocsGeneratorCommand::class,
            ]);

            DB::connection()
                ->getDoctrineConnection()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}