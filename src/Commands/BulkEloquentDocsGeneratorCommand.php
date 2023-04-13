<?php

namespace SethPhat\EloquentDocs\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'eloquent:phpdoc')]
class BulkEloquentDocsGeneratorCommand extends EloquentDocsGeneratorCommand
{
    protected $signature = 'eloquent:phpdoc
                            {model : The model class}
                            {--write : Write the new phpDoc for the class (Force-write)} 
                            {--short-class : Use the short classname (without full path) in phpDoc block}';
    protected $description = '[EloquentDocs] Generate PHPDoc scope for your Eloquent Model';
}