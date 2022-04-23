<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Client;
use App\Models\Printing;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function() {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('lança exceção ao tentar cadastrar clientes em duplicidade, isto é, com nomes iguais', function () {
    expect(
        fn () => Client::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar cliente com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Client::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['name', null,             'cannot be null'],           // obrigatório
]);

// Happy path
test('cadastra múltiplos clientes', function () {
    Client::factory(30)->create();

    expect(Client::count())->toBe(30);
});

test('nome do cliente em seu tamanho máximo é aceito', function () {
    Client::factory()->create(['name' => Str::random(255)]);

    expect(Client::count())->toBe(1);
});

test('um cliente possui várias impressões', function () {
    Client::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $client = Client::with('prints')->first();

    expect($client->prints)->toHaveCount(3);
});
