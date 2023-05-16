<?php

namespace SethPhat\EloquentDocs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use SethPhat\EloquentDocs\Commands\BulkEloquentDocsGeneratorCommand;
use SethPhat\EloquentDocs\Commands\EloquentDocsGeneratorCommand;

class EloquentDocsServiceProvider extends ServiceProvider
{
    /**
     * DB type => Doctrine Type manual mapping
     */
    protected const DB_DOCTRINE_TYPES_MAP = [
        'enum' => 'string',
        'bit' => 'string',
    ];

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EloquentDocsGeneratorCommand::class,
                BulkEloquentDocsGeneratorCommand::class,
            ]);

            if (interface_exists('Doctrine\DBAL\Driver')) {
                $dbPlatform = DB::connection()
                    ->getDoctrineConnection()
                    ->getDatabasePlatform();

                foreach (static::DB_DOCTRINE_TYPES_MAP as $dbType => $doctrineType) {
                    $dbPlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
                }
            }
        }
    }
}