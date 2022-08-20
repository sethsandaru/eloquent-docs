<?php

namespace SethPhat\EloquentDocs\Tests\Units\Generators;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use SethPhat\EloquentDocs\Services\Generators\TableGenerator;

class TableGeneratorTest extends TestCase
{
    public function testGeneratorWillReturnTableName()
    {
        $model = new class () extends Model {
            protected $table = 'seth_phat';
        };

        $generator = new TableGenerator();

        $this->assertStringContainsString('seth_phat', $generator->generate($model));
    }
}