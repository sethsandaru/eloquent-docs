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
            ->expectsOutputToContain('@property string $gender')
            ->expectsOutputToContain('=== Relationships ===')
            ->expectsOutputToContain('@property-read \SethPhat\EloquentDocs\Tests\Fixtures\EmailFixture[]|\Illuminate\Database\Eloquent\Collection $emails')
            ->expectsOutputToContain('@property-read \SethPhat\EloquentDocs\Tests\Fixtures\UserDetailFixture|null $userDetail')
            ->expectsOutputToContain('=== Accessors/Attributes ===')
            ->expectsOutputToContain('@property-read string $fullName')
            ->expectsOutputToContain('@property-read int $totalSalary')
            ->expectsOutputToContain('@property mixed $firstName')
            ->expectsOutputToContain('@property-read mixed $lastName')
            ->assertSuccessful()
            ->execute();
    }

    public function testCommandWillRunAndShowUpThePhpDocsUseShortHandClassName()
    {
        $this->artisan('eloquent:phpdoc', [
            'model' => UserFixture::class,
            '--short-class' => 1,
        ])
            ->expectsOutputToContain('@property-read EmailFixture[]|\Illuminate\Database\Eloquent\Collection $emails')
            ->expectsOutputToContain('@property-read UserDetailFixture|null $userDetail')
            ->expectsOutputToContain('@property-read EmailFixture|null $lastEmail')
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

        // should be able to create instance normally
        $this->assertNotNull((new EmailFixture()));
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

        // should be able to create instance normally
        $this->assertNotNull((new UserDetailFixture()));
    }

    public function testCommandReturnsErrorOnClassNotFound()
    {
        $this->artisan('eloquent:phpdoc', [
            'model' => 'FakeClass/From/SethPhat',
            '--write' => 1,
        ])
            ->expectsOutputToContain("doesn't exists.")
            ->assertFailed();
    }

    public function testCommandReturnsErrorOnClassNotEloquent()
    {
        $this->artisan('eloquent:phpdoc', [
            'model' => 'stdClass',
            '--write' => 1,
        ])
            ->expectsOutputToContain('is not an Eloquent')
            ->assertFailed();
    }
}