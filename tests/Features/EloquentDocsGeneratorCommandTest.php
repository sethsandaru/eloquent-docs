<?php

namespace SethPhat\EloquentDocs\Tests\Features;

use SethPhat\EloquentDocs\Tests\Fixtures\EmailFixture;
use SethPhat\EloquentDocs\Tests\Fixtures\UserDetailFixture;
use SethPhat\EloquentDocs\Tests\Fixtures\UserFixture;
use SethPhat\EloquentDocs\Tests\TestCase;

class EloquentDocsGeneratorCommandTest extends TestCase
{
    public function testCommandWillRunAndShowUpThePhpDocs()
    {
        $this->artisan('eloquent:phpdoc', [
                'model' => UserFixture::class,
            ])
            ->expectsOutputToContain('Table: users')
            ->expectsOutputToContain('=== Columns ===')
            ->expectsOutputToContain('@property int $id')
            ->expectsOutputToContain('@property string $first_name')
            ->expectsOutputToContain('@property string $last_name')
            ->expectsOutputToContain('@property int $age')
            ->expectsOutputToContain('@property float $profile_complete_percentage')
            ->expectsOutputToContain('@property array|null $payload')
            ->expectsOutputToContain('@property object|\stdClass $additional_payload')
            ->expectsOutputToContain('@property \Illuminate\Support\Collection $external_data')
            ->expectsOutputToContain('=== Relationships ===')
            ->expectsOutputToContain('@property-read \SethPhat\EloquentDocs\Tests\Fixtures\EmailFixture[]|\Illuminate\Support\Collection $emails')
            ->expectsOutputToContain('@property-read \SethPhat\EloquentDocs\Tests\Fixtures\UserDetailFixture|null $userDetail')
            ->expectsOutputToContain('=== Accessors/Attributes ===')
            ->expectsOutputToContain('@property-read string $full_name')
            ->expectsOutputToContain('@property-read int $total_salary')
            ->expectsOutputToContain('@property mixed $first_name')
            ->expectsOutputToContain('@property-read mixed $last_name')
            ->assertSuccessful()
            ->execute();
    }

    public function testCommandWillRunAndWriteToFileNewPhpDoc()
    {
        $this->artisan('eloquent:phpdoc', [
            'model' => EmailFixture::class,
            '--write' => 1,
        ])
        ->assertSuccessful()
        ->execute();

        $fileContent = file_get_contents(__DIR__ . '/../Fixtures/EmailFixture.php');

        $this->assertStringContainsString('Table: emails', $fileContent);
        $this->assertStringContainsString('@property string $email', $fileContent);
    }

    public function testCommandWillRunAndWriteToFileReplaceOldPhpDoc()
    {
        $this->artisan('eloquent:phpdoc', [
            'model' => UserDetailFixture::class,
            '--write' => 1,
        ])
        ->assertSuccessful()
        ->execute();

        $fileContent = file_get_contents(__DIR__ . '/../Fixtures/UserDetailFixture.php');

        $this->assertStringContainsString('Table: user_details', $fileContent);
        $this->assertStringContainsString('@property string $zone', $fileContent);
        $this->assertStringContainsString('@property string $address', $fileContent);
        $this->assertStringNotContainsString('Here will be replaced', $fileContent);
    }
}