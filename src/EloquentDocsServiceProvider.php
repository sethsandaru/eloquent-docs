<?php

namespace SethPhat\EloquentDocs;

use Illuminate\Support\ServiceProvider;
use SethPhat\EloquentDocs\Commands\EloquentDocsGeneratorCommand;

class EloquentDocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            EloquentDocsGeneratorCommand::class,
        ]);
    }
}