<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar documentação em duplicidade, isto é, com rotas iguais', function () {
    expect(
        fn () => Documentation::factory(2)->create(['app_route_name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar documentação com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Documentation::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['app_route_name', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['app_route_name', null,             'cannot be null'],           // obrigatório
    ['doc_link',       Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Happy path
test('cadastra múltiplas documentações', function () {
    Documentation::factory(30)->create();

    expect(Documentation::count())->toBe(30);
});

test('campos da documentação em seu tamanho máximo são aceitos', function () {
    Documentation::factory()->create([
        'app_route_name' => Str::random(255),
        'doc_link' => Str::random(255),
    ]);

    expect(Documentation::count())->toBe(1);
});

test('campos opcionais da documentação são aceitos', function () {
    Documentation::factory()->create(['doc_link' => null]);

    expect(Documentation::count())->toBe(1);
});

test('retorna as localidades usando o escopo de ordenação default definido', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Documentation::factory()->create(['app_route_name' => $third]);
    Documentation::factory()->create(['app_route_name' => $first]);
    Documentation::factory()->create(['app_route_name' => $second]);

    $docs = Documentation::defaultOrder()->get();

    expect($docs->get(0)->app_route_name)->toBe($first)
    ->and($docs->get(1)->app_route_name)->toBe($second)
    ->and($docs->get(2)->app_route_name)->toBe($third);
});
