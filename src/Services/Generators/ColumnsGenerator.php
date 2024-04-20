<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Eloquent\Model;
use SethPhat\EloquentDocs\Services\DoctrineManager;

class ColumnsGenerator implements PhpDocGeneratorContract
{
    protected AbstractSchemaManager $schema;
    protected Model $model;

    public function __construct() {
        $this->schema = DoctrineManager::get()->createSchemaManager();
    }

    public function generate(Model $model, array $options = []): string
    {
        $this->model = $model;
        $columns = $this->schema->listTableColumns($model->getTable());
        if (empty($columns)) {
            return '';
        }

        // columns
        $phpDocStr = "\n*\n* === Columns ===";
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
            'mediumint', 'bigint', 'integer' => $this->hasBoolCasting($column->getName())
                ? 'bool'
                : 'int',

            'float', 'double', 'decimal', 'dec', 'numeric' => 'float',

            'bool', 'boolean' => 'bool',

            // would be string if you don't add 'casts'
            'date', 'datetime', 'timestamp', 'time', 'year' => $this->getDateCasting($column->getName()),

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

    protected function getDateCasting(string $column): string
    {
        if (in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
            return Carbon::class;
        }

        if ($this->model->hasCast($column, ['immutable_date', 'immutable_datetime'])) {
            return CarbonImmutable::class;
        }

        return $this->model->hasCast($column, ['date', 'datetime'])
            ? Carbon::class
            : 'string';
    }

    protected function hasBoolCasting(string $column): bool
    {
        return $this->model->hasCast($column, ['bool', 'boolean']);
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