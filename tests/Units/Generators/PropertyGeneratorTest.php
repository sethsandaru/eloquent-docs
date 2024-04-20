<?php

namespace SethPhat\EloquentDocs\Tests\Units\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SethPhat\EloquentDocs\Services\Generators\ColumnsGenerator;
use SethPhat\EloquentDocs\Tests\TestCase;

class PropertyGeneratorTest extends TestCase
{
    public function testEnumColumnReturnsString()
    {
        Schema::create('test_enum_table', function (Blueprint $table) {
            $table->enum('hello', ['a', 'b', 'c']);
        });

        $columnGenerator = app(ColumnsGenerator::class);
        $generatedText = $columnGenerator->generate(new class extends Model {
            protected $table = 'test_enum_table';
        });

        $this->assertStringContainsString('@property string $hello', $generatedText);
    }

    public function testBitColumnReturnsString()
    {
        DB::unprepared("
            CREATE TABLE test_bit_table(
                this_is_bit BIT(1) NOT NULL
            )
        ");

        $columnGenerator = app(ColumnsGenerator::class);
        $generatedText = $columnGenerator->generate(new class extends Model {
            protected $table = 'test_bit_table';
        });

        $this->assertStringContainsString('@property string $this_is_bit', $generatedText);
    }

    public function testTinyIntUsesAsBoolean()
    {
        Schema::create('test_tiny_int', function (Blueprint $table) {
            $table->tinyInteger('boolean_column');
            $table->boolean('hello_bool')->nullable();
        });

        $columnGenerator = app(ColumnsGenerator::class);
        $generatedText = $columnGenerator->generate(new class extends Model {
            protected $table = 'test_tiny_int';
        });

        $this->assertStringContainsString('@property bool|null $hello_bool', $generatedText);
    }

    public function testNumericPostgresRenderFloat()
    {
        if (env('DB_CONNECTION') !== 'pgsql') {
            $this->markTestSkipped('only available for pgsql');
            return;
        }

        DB::unprepared("
            CREATE TABLE test_numeric(
                number numeric NOT NULL
            )
        ");

        $columnGenerator = app(ColumnsGenerator::class);
        $generatedText = $columnGenerator->generate(new class extends Model {
            protected $table = 'test_numeric';
        });

        $this->assertStringContainsString('@property float $number', $generatedText);
    }
}