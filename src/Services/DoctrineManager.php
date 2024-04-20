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
        /** @var string $driverName */
        $driverName = config("database.connections.{$defaultDb}.driver");

        $configs = match ($driverName) {
            'mysql', 'pgsql' => [
                'dbname' => config("database.connections.{$defaultDb}.database"),
                'user' => config("database.connections.{$defaultDb}.username"),
                'password' => config("database.connections.{$defaultDb}.password"),
                'host' => config("database.connections.{$defaultDb}.host"),
                'port' => config("database.connections.{$defaultDb}.port"),
                'driver' => 'pdo_' . $driverName,
            ],
            'sqlite' => [
                'driver' => 'pdo_' . $driverName,
                'path' => config("database.connections.{$defaultDb}.database"),
            ],
            default => throw new LogicException("EloquentDocs is not supporting '$driverName' database driver"),
        };

        $connection = DriverManager::getConnection($configs);

        $dbPlatform = $connection->getDatabasePlatform();
        foreach (static::DB_DOCTRINE_TYPES_MAP as $dbType => $doctrineType) {
            $dbPlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
        }

        return static::$connection = $connection;
    }
}