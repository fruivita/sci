<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Printer;
use App\Models\Printing;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('throws exception when trying to create printers in duplicate, that is, with the same names', function () {
    expect(
        fn () => Printer::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create printer with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Printer::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['name', null,             'cannot be null'],           // required
]);

// Happy path
test('create many printers', function () {
    Printer::factory(30)->create();

    expect(Printer::count())->toBe(30);
});

test('printer name at its maximum size is accepted', function () {
    Printer::factory()->create(['name' => Str::random(255)]);

    expect(Printer::count())->toBe(1);
});

test('one printer has many prints', function () {
    Printer::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $printer = Printer::with('prints')->first();

    expect($printer->prints)->toHaveCount(3);
});
