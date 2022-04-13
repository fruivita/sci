<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar localidades em duplicidade, isto é, com nomes iguais', function () {
    expect(
        fn () => Site::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar localidade com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Site::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['name', null,             'cannot be null'],           // obrigatório
]);

// Happy path
test('cadastra múltiplas localidades', function () {
    Site::factory(30)->create();

    expect(Site::count())->toBe(30);
});

test('nome do localidade em seu tamanho máximo é aceito', function () {
    Site::factory()->create(['name' => Str::random(255)]);

    expect(Site::count())->toBe(1);
});

test('uma localidade é controlada por vários servidores', function () {
    Site::factory()
        ->has(Server::factory(3), 'servers')
        ->create();

    $sites = Site::with('servers')->first();

    expect($sites->servers)->toHaveCount(3);
});
