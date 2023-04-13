<?php

namespace SethPhat\EloquentDocs\Tests\Features;

use SethPhat\EloquentDocs\Tests\TestCase;

class BulkEloquentDocsGeneratorCommandTest extends TestCase
{
    public function testCommandReturnsErrorBecauseNoModelsFound()
    {
        $this->artisan('eloquent:bulk-phpdoc "tests/Units/*.php"')
            ->expectsOutputToContain('No Eloquent Model found from the glob pattern')
            ->assertFailed();
    }

    public function testCommandReturnsOkGeneratedDocsForAllModelFilesInPath()
    {
        $this->artisan('eloquent:bulk-phpdoc "tests/Fixtures/BulkFixtures/*.php"')
            ->expectsOutputToContain('Generated & Saved for:')
            ->expectsOutputToContain('Generated EloquentDocs for 3 model file(s)')
            ->assertSuccessful();

        // the generation & storing actually tested in EloquentDocsGeneratorCommandTest, no need to re-assert the same here.
    }
}