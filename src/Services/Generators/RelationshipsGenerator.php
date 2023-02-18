<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionMethod;
use SplFileObject;

class RelationshipsGenerator implements PhpDocGeneratorContract
{
    protected const RELATION_METHODS = [
        'hasMany',
        'hasManyThrough',
        'hasOneThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    public function generate(Model $model, array $options = []): string
    {
        $phpDocStr = "\n*\n* === Relationships ===";
        $relationships = $this->getRelations($model);
        $isUseShortClass = $options['useShortClass'] ?? false;

        if ($relationships->isEmpty()) {
            return '';
        }

        foreach ($relationships as $relationship) {
            $isManyRelation = Str::contains($relationship['returnType'], 'Many', true);

            $relatedClassName = $isUseShortClass
                ? class_basename($relationship['related'])
                : '\\' . $relationship['related'];

            $phpDocStr .= sprintf(
                '%s * @property-read %s%s %s',
                "\n",
                $relatedClassName,
                $isManyRelation ? '[]|\Illuminate\Database\Eloquent\Collection' : '|null',
                '$' . $relationship['name']
            );
        }

        return $phpDocStr;
    }

    /**
     * Get the relations from the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Support\Collection
     */
    protected function getRelations($model)
    {
        return collect(get_class_methods($model))
            ->map(fn ($method) => new ReflectionMethod($model, $method))
            ->reject(
                fn (ReflectionMethod $method) => $method->isStatic()
                    || $method->isAbstract()
                    || $method->getDeclaringClass()->getName() !== get_class($model)
            )
            ->filter(function (ReflectionMethod $method) {
                $file = new SplFileObject($method->getFileName());
                $file->seek($method->getStartLine() - 1);
                $code = '';
                while ($file->key() < $method->getEndLine()) {
                    $code .= $file->current();
                    $file->next();
                }

                return collect(static::RELATION_METHODS)
                    ->contains(fn ($relationMethod) => str_contains($code, '$this->'.$relationMethod.'('));
            })
            ->map(function (ReflectionMethod $method) use ($model) {
                $relation = $method->invoke($model);

                if (! $relation instanceof Relation) {
                    return null;
                }

                return [
                    'name' => $method->getName(),
                    'type' => Str::afterLast(get_class($relation), '\\'),
                    'related' => get_class($relation->getRelated()),
                    'returnType' => $method->hasReturnType()
                        ? $method->getReturnType()->getName()
                        : 'mixed',
                ];
            })
            ->filter()
            ->values();
    }
}