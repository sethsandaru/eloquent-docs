<?php

namespace SethPhat\EloquentDocs\Commands;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use SethPhat\EloquentDocs\Services\GeneratePhpDocService;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'eloquent:bulk-phpdoc')]
class BulkEloquentDocsGeneratorCommand extends EloquentDocsGeneratorCommand
{
    protected $signature = 'eloquent:bulk-phpdoc
                            {location : The glob pattern to get the Model files}
                            {--short-class : Use the short classname (without full path) in phpDoc block}';
    protected $description = '[EloquentDocs] Bulk Generate PHPDoc scope for your Eloquent Model';

    public function handle(
        Composer $composer,
        GeneratePhpDocService $generatePhpDocService,
        Filesystem $filesystem
    ): int {
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            if (!$this->components->confirm('Create model with phpDoc properties requires requires the Doctrine DBAL (doctrine/dbal) package. Would you like to install it?')) {
                return 1;
            }

            $this->composer = $composer;
            $this->installDependencies();

            return 0;
        }

        $models = $this->getModels();
        if ($models->isEmpty()) {
            return $this->error('No Eloquent Model found from the glob pattern') || 1;
        }

        // generate docs
        $generatedDocs = $models->map(fn ($model) => [
            ...$model,
            'modelGeneratedDocs' => $generatePhpDocService->setModel($model['modelInstance'])
                ->setOptions([
                    'useShortClass' => (bool) $this->option('short-class'),
                ])
                ->generate(),
        ]);

        // save to files
        $generatedDocs->each(fn ($generatedModel) =>
            $this->writePhpDoc(
                $filesystem,
                $generatedModel['modelClass'],
                $generatedModel['modelGeneratedDocs']
            )
            || $this->info("Generated & Saved for: {$generatedModel['modelFilePath']}")
            || true
        );

        $this->info("Generated EloquentDocs for {$generatedDocs->count()} model file(s)");
        $this->info('Thank you for using EloquentDocs!');

        return 0;
    }

    /**
     * @return Collection<Model>
     */
    private function getModels(): Collection
    {
        return collect(glob($this->argument('location')))
            ->filter(fn (string $file) => Str::contains($file, '.php'))
            ->map(function (string $file) {
                $className = $this->getFullyQualifiedClassName($file);

                return [
                    'modelClass' => $className,
                    'modelFilePath' => $file,
                    'modelInstance' => app($className),
                ];
            })
            ->filter(fn ($model) => $model['modelInstance'] instanceof Model)
            ->values();
    }

    private function getFullyQualifiedClassName(string $filePath): string
    {
        $fileContents = file_get_contents($filePath);
        $tokens = token_get_all($fileContents);
        $namespace = '';
        $className = '';

        foreach ($tokens as $index => $token) {
            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) {
                    $namespace = '\\' . $tokens[$index + 2][1];
                } else if ($token[0] === T_CLASS) {
                    $className = $tokens[$index + 2][1];
                    break;
                }
            }
        }

        return $namespace . '\\' . $className;
    }
}