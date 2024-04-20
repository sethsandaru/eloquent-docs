<?php

namespace SethPhat\EloquentDocs\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use LogicException;

class DoctrineManager
{
    /**
     * DB type => Doctrine Type manual mapping
     */
    protected const DB_DOCTRINE_TYPES_MAP = [
        'enum' => 'string',
        'bit' => 'string',
    ];

    protected static ?Connection $connection = null;

    public static function get(): Connection
    {
        if (static::$connection) {
            return static::$connection;
        }

        /** @var string $defaultDb */
        $defaultDb = config('database.default');

        $configs = match ($defaultDb) {
            'mysql', 'pgsql' => [
                'dbname' => config("database.connections.{$defaultDb}.database"),
                'user' => config("database.connections.{$defaultDb}.username"),
                'password' => config("database.connections.{$defaultDb}.password"),
                'host' => config("database.connections.{$defaultDb}.host"),
                'port' => config("database.connections.{$defaultDb}.port"),
                'driver' => 'pdo_' . $defaultDb,
            ],
            'sqlite' => [
                'driver' => 'pdo_sqlite',
                'path' => config("database.connections.{$defaultDb}.database"),
            ],
            default => throw new LogicException("EloquentDocs is not supporting '$defaultDb' database driver"),
        };

        $connection = DriverManager::getConnection($configs);

        $dbPlatform = $connection->getDatabasePlatform();
        foreach (static::DB_DOCTRINE_TYPES_MAP as $dbType => $doctrineType) {
            $dbPlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
        }

        return static::$connection = $connection;
    }
}