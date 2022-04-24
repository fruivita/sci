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
test('lança exceção ao tentar cadastrar impressoras em duplicidade, isto é, com nomes iguais', function () {
    expect(
        fn () => Printer::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar impressora com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Printer::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['name', null,             'cannot be null'],           // obrigatório
]);

// Happy path
test('cadastra múltiplas impressoras', function () {
    Printer::factory(30)->create();

    expect(Printer::count())->toBe(30);
});

test('nome da impressora em seu tamanho máximo é aceito', function () {
    Printer::factory()->create(['name' => Str::random(255)]);

    expect(Printer::count())->toBe(1);
});

test('uma impressora possui várias impressões', function () {
    Printer::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $printer = Printer::with('prints')->first();

    expect($printer->prints)->toHaveCount(3);
});
