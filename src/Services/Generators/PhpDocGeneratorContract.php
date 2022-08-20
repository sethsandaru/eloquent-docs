<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;

interface PhpDocGeneratorContract
{
    /**
     * Generate the needful phpDoc
     *
     * @param Model $model
     *
     * @return string
     */
    public function generate(Model $model): string;
}