<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class AccessorsGenerator implements PhpDocGeneratorContract
{
    protected const TYPE_ACCESSOR = 'accessor';
    protected const TYPE_ATTRIBUTE = 'attribute';

    protected const TEMPLATES = [
        self::TYPE_ACCESSOR => '%s * @property-read %s %s',
        self::TYPE_ATTRIBUTE => '%s * @property %s %s',
    ];

    public function generate(Model $model, array $options = []): string
    {
        $phpDocStr = "\n*\n* === Accessors/Attributes ===";

        $virtualAttributes = $this->getVirtualAttributes($model);
        if ($virtualAttributes->isEmpty()) {
            return '';
        }

        $isUseShortClass = $options['useShortClass'] ?? false;

        foreach ($virtualAttributes as $virtualAttribute) {
            $template = static::TEMPLATES[$virtualAttribute['cast']];

            // if attribute is immutable => @property-read
            if ($virtualAttribute['cast'] === static::TYPE_ATTRIBUTE && !$virtualAttribute['hasSetter']) {
                $template = static::TEMPLATES[static::TYPE_ACCESSOR];
            }

            $returnType = $virtualAttribute['returnType'];
            if (class_exists($returnType)) {
                if ($isUseShortClass) {
                    $returnType = class_basename($returnType) . '|null';
                } else{
                    $returnType = '\\' . $returnType . '|null';
                }
            }

            $phpDocStr .= sprintf(
                $template,
                "\n",
                $returnType,
                '$' . Str::camel($virtualAttribute['name'])
            );
        }

        return $phpDocStr;
    }

    /**
     * Get the virtual (non-column) attributes for the given model.
     *
     * @param Model $model
     * @return Collection
     */
    protected function getVirtualAttributes(Model $model): Collection
    {
        $class = new ReflectionClass($model);

        return collect($class->getMethods())
            ->reject(
                fn (ReflectionMethod $method) => $method->isStatic()
                    || $method->isAbstract()
                    || $method->getDeclaringClass()->getName() !== get_class($model)
            )
            ->mapWithKeys(function (ReflectionMethod $method) use ($model) {
                $returnType = $method->hasReturnType()
                    ? $method->getReturnType()->getName()
                    : 'mixed';

                if (preg_match('/^get(.*)Attribute$/', $method->getName(), $matches) === 1) {
                    return [Str::snake($matches[1]) => [static::TYPE_ACCESSOR, $returnType]];
                } elseif ($model->hasAttributeMutator($method->getName())) {
                    $invokeResult = $method->invoke($model);
                    $hasSetter = (bool) $invokeResult->set;

                    return [Str::snake($method->getName()) => [static::TYPE_ATTRIBUTE, 'mixed', $hasSetter]];
                } else {
                    return [];
                }
            })
            ->filter()
            ->map(fn ($cast, $name) => [
                'name' => $name,
                'cast' => $cast[0],
                'returnType' => $cast[1],
                'hasSetter' => $cast[2] ?? false,
            ])
            ->values();
    }
}