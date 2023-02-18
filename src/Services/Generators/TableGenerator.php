<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;

class TableGenerator implements PhpDocGeneratorContract
{
    public function generate(Model $model, array $options = []): string
    {
        return sprintf('%s * Table: %s', "\n", $model->getTable());
    }
}