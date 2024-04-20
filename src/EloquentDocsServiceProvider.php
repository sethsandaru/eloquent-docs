<?php

namespace SethPhat\EloquentDocs;

use Illuminate\Support\ServiceProvider;
use SethPhat\EloquentDocs\Commands\BulkEloquentDocsGeneratorCommand;
use SethPhat\EloquentDocs\Commands\EloquentDocsGeneratorCommand;

class EloquentDocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EloquentDocsGeneratorCommand::class,
                BulkEloquentDocsGeneratorCommand::class,
            ]);
        }
    }
}