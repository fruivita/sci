<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
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

// Failure
test('método updateAndSync faz rollback em casa de falha na atualização da localidade', function () {
    $site = Site::factory()->create([
        'name' => 'foo',
    ]);

    $site->name = 'new foo';

    // relacionamento com servidores inexistentes
    $saved = $site->updateAndSync([1, 2]);

    $site->refresh()->load('servers');

    expect($saved)->toBeFalse()
    ->and($site->name)->toBe('foo')
    ->and($site->servers)->toBeEmpty();
});

test('método updateAndSync cria log em casa de falha na atualização da localidade', function () {
    Log::shouldReceive('error')->once();

    $site = Site::factory()->create();

    // relacionamento com permissões inexistentes
    $site->updateAndSync([1, 2]);
});

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

test('previous retorna o registro anterior correto, mesmo sendo o primeiro', function () {
    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'foo']);

    expect($site_2->previous()->first()->id)->toBe($site_1->id)
    ->and($site_1->previous()->first())->toBeNull();
});

test('next retorna o registro posterior correto, mesmo sendo o último', function () {
    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'foo']);

    expect($site_1->next()->first()->id)->toBe($site_2->id)
    ->and($site_2->next()->first())->toBeNull();
});

test('retorna as localidades usando o escopo de ordenação default definido', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Site::factory()->create(['name' => $third]);
    Site::factory()->create(['name' => $first]);
    Site::factory()->create(['name' => $second]);

    $sites = Site::defaultOrder()->get();

    expect($sites->get(0)->name)->toBe($first)
    ->and($sites->get(1)->name)->toBe($second)
    ->and($sites->get(2)->name)->toBe($third);
});

test('método updateAndSync salva os novos atributos e cria relacionamento com os servidores informados', function () {
    $site = Site::factory()->create([
        'name' => 'foo',
    ]);

    Server::factory()->create(['id' => 1]);
    Server::factory()->create(['id' => 2]);
    Server::factory()->create(['id' => 3]);

    $site->name = 'new foo';

    $saved = $site->updateAndSync([1, 3]);
    $site->refresh()->load('servers');

    expect($saved)->toBeTrue()
    ->and($site->name)->toBe('new foo')
    ->and($site->servers->modelKeys())->toBe([1, 3]);
});
