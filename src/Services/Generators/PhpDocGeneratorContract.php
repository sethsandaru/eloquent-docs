<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;

interface PhpDocGeneratorContract
{
    /**
     * Generate the needful phpDoc
     *
     * @param Model $model
     * @param array $options
     *
     * @return string
     */
    public function generate(Model $model, array $options = []): string;
}