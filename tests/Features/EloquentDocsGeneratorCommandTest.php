<?php

namespace SethPhat\EloquentDocs\Tests\Features;

use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class EloquentDocsGeneratorCommandTest extends TestCase
{
    public function testCommandWillRun()
    {
        Storage::fake('local');
    }
}