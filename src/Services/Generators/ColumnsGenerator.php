<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

class ColumnsGenerator implements PhpDocGeneratorContract
{
    protected AbstractSchemaManager $schema;
    protected Model $model;

    public function __construct(
        Connection $databaseConnection
    ) {
        $this->schema = $databaseConnection->getDoctrineSchemaManager();
    }

    public function generate(Model $model, array $options = []): string
    {
        $this->model = $model;
        $columns = $this->schema->listTableColumns($model->getTable());
        $phpDocStr = "\n*\n* === Columns ===";
        if (empty($columns)) {
            return '';
        }

        // columns
        foreach ($columns as $column) {
            $phpDocStr .= sprintf(
                '%s * @property %s %s',
                "\n",
                $this->resolveColumnType($column),
                '$' . $column->getName()
            );
        }

        return $phpDocStr;
    }

    /**
     * Resolve from DB type to PHP type
     *
     * @param Column $column
     * @return string
     */
    protected function resolveColumnType(Column $column): string
    {
        $columnType = strtolower($column->getType()->getName());

        $type = match ($columnType) {
            'int', 'smallint', 'tinyint',
            'mediumint', 'bigint', 'integer' => 'int',
            'float', 'double', 'decimal', 'dec', 'numeric' => 'float',

            'bool', 'boolean' => 'bool',

            // would be string if you don't add 'casts'
            'date', 'datetime', 'timestamp', 'time', 'year' => $this->hasDateCasting($column->getName())
                ? '\Carbon\Carbon'
                : 'string',

            'char', 'string', 'varchar',
            'text', 'tinytext', 'mediumtext',
            'longtext', 'enum', 'binary',
            'varbinary', 'set', 'json', 'jsonb' => $this->getJsonCastType($column->getName()),
            // ^ because sqlite doesn't have json, they will use `text` but Eloquent can parse text to particular cast

            // would be string if you don't add 'casts', default to array
            default => 'mixed',
        };

        if (!$column->getNotnull()) {
            $type .= '|null';
        }

        return $type;
    }

    protected function hasDateCasting(string $column): bool
    {
        if (in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
            return true;
        }

        return $this->model->hasCast($column, ['date', 'datetime', 'immutable_date', 'immutable_datetime']);
    }

    protected function getJsonCastType(string $column): string
    {
        if ($this->model->hasCast($column, ['array'])) {
            return 'array';
        }

        if ($this->model->hasCast($column, ['collection'])) {
            return '\Illuminate\Support\Collection';
        }

        if ($this->model->hasCast($column, ['object'])) {
            return 'object|\stdClass';
        }

        return 'string';
    }
}