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
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite does not have bit');
        }

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

            protected $casts = [
                'boolean_column' => 'boolean',
            ];
        });

        $this->assertStringContainsString('@property bool $boolean_column', $generatedText);
        $this->assertStringContainsString('@property bool|null $hello_bool', $generatedText);
    }

    public function testNumericPostgresRenderFloat()
    {
        if (env('DB_CONNECTION') !== 'pgsql') {
            $this->markTestSkipped('only available for pgsql');
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

    public function testDatesCastColumns()
    {
        Schema::create('test_date_columns_table', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('activated_at');
            $table->timestamp('enabled_at');
            $table->timestamp('no_cast_at')->nullable();
            $table->date('incurred_date')->nullable();
            $table->date('invoiced_date');
        });

        $columnGenerator = app(ColumnsGenerator::class);
        $generatedText = $columnGenerator->generate(new class extends Model {
            protected $table = 'test_date_columns_table';

            protected $casts = [
                'incurred_date' => 'date',
                'activated_at' => 'datetime',
                'invoiced_date' => 'immutable_date',
                'enabled_at' => 'immutable_datetime',
            ];
        });

        $this->assertStringContainsString('@property Carbon\Carbon|null $created_at', $generatedText);
        $this->assertStringContainsString('@property Carbon\Carbon|null $updated_at', $generatedText);
        $this->assertStringContainsString('@property Carbon\Carbon|null $deleted_at', $generatedText);
        $this->assertStringContainsString('@property Carbon\Carbon $activated_at', $generatedText);
        $this->assertStringContainsString('@property Carbon\CarbonImmutable $enabled_at', $generatedText);
        $this->assertStringContainsString('@property string|null $no_cast_at', $generatedText);
        $this->assertStringContainsString('@property Carbon\Carbon|null $incurred_date', $generatedText);
        $this->assertStringContainsString('@property Carbon\CarbonImmutable $invoiced_date', $generatedText);
    }
}