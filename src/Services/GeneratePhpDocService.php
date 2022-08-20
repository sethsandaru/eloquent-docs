<?php

namespace SethPhat\EloquentDocs\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use SethPhat\EloquentDocs\Services\Generators\AccessorsGenerator;
use SethPhat\EloquentDocs\Services\Generators\ColumnsGenerator;
use SethPhat\EloquentDocs\Services\Generators\PhpDocGeneratorContract;
use SethPhat\EloquentDocs\Services\Generators\RelationshipsGenerator;
use SethPhat\EloquentDocs\Services\Generators\TableGenerator;

class GeneratePhpDocService
{
    protected const GENERATORS = [
        TableGenerator::class,
        ColumnsGenerator::class,
        RelationshipsGenerator::class,
        AccessorsGenerator::class,
    ];

    protected Model $model;

    public function __construct(protected Application $laravel)
    {
    }

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function generate(): string
    {
        $phpDocStr = '/**';

        foreach (static::GENERATORS as $generatorClass) {
            /**
             * @var PhpDocGeneratorContract $generator
             */
            $generator = $this->laravel->make($generatorClass);

            $phpDocStr .= $generator->generate($this->model);
        }

        $phpDocStr .= "\n*/";

        return $phpDocStr;
    }
}