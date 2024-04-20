<?php

namespace SethPhat\EloquentDocs\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use ReflectionClass;
use SethPhat\EloquentDocs\Services\GeneratePhpDocService;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'eloquent:phpdoc')]
class EloquentDocsGeneratorCommand extends Command
{
    protected $signature = 'eloquent:phpdoc
                            {model : The model class}
                            {--write : Write the new phpDoc for the class (Force-write)} 
                            {--short-class : Use the short classname (without full path) in phpDoc block}';
    protected $description = '[EloquentDocs] Generate PHPDoc scope for your Eloquent Model';

    protected Composer $composer;

    public function handle(
        GeneratePhpDocService $generatePhpDocService,
        Filesystem $filesystem
    ): int {
        $modelClass = $this->argument('model');
        if (!class_exists($modelClass)) {
            return $this->error("Class $modelClass doesn't exists.") || 1;
        }

        $model = app($modelClass);
        if (!($model instanceof Model)) {
            return $this->error("Class $modelClass is not an Eloquent.") || 1;
        }

        $shouldWrite = (bool) $this->option('write');

        $generatedDocs = $generatePhpDocService->setModel($model)
            ->setOptions([
                'useShortClass' => (bool) $this->option('short-class'),
            ])
            ->generate();

        $this->info('====== Start PHPDOC scope of ' . $modelClass);
        $lines = explode("\n", $generatedDocs);

        foreach ($lines as $line) {
            $this->info($line);
        }
        $this->info('====== End PHPDOC scope of ' . $modelClass);

        if ($shouldWrite) {
            // replace to file
            $this->writePhpDoc(
                $filesystem,
                $modelClass,
                $generatedDocs
            );
        }

        $this->info('Thank you for using EloquentDocs!');

        return 0;
    }

    protected function getClassInfo(string $className): array
    {
        $reflectorClass = new ReflectionClass($className);

        return [$reflectorClass->getFileName(), $reflectorClass->getShortName()];
    }

    protected function writePhpDoc(Filesystem $filesystem, string $modelClass, string $phpDocContent): void
    {
        [$filePath, $shortClassName] = $this->getClassInfo($modelClass);
        $fileContent = $filesystem->get($filePath);

        if (!Str::contains($fileContent, "*/\nclass $shortClassName")) {
            // didn't have the phpDoc, write normally
            $middlePart = "\nclass $shortClassName";
            $fileContent = str_replace(
                $middlePart,
                "\n" . $phpDocContent . $middlePart,
                $fileContent
            );
        } else {
            // already has phpDoc
            $fileContent = preg_replace(
                '/\/\*\*(.*)\*\/\nclass/s',
                $phpDocContent . "\nclass",
                $fileContent
            );
        }

        $filesystem->put($filePath, $fileContent);
        $this->info('Wrote phpDoc scope to ' . $filePath);
     }
}