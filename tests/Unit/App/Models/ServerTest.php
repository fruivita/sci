<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Printing;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function() {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('lança exceção ao tentar cadastrar servidores em duplicidade, isto é, com nomes iguais', function () {
    expect(
        fn () => Server::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar servidor com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Server::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['name', null,             'cannot be null'],           // obrigatório
]);

// Happy path
test('cadastra múltiplos servidores', function () {
    Server::factory(30)->create();

    expect(Server::count())->toBe(30);
});

test('nome do servidor em seu tamanho máximo é aceito', function () {
    Server::factory()->create(['name' => Str::random(255)]);

    expect(Server::count())->toBe(1);
});

test('um servidor possui várias impressões', function () {
    Server::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $server = Server::with('prints')->first();

    expect($server->prints)->toHaveCount(3);
});

test('um servidor controla várias localidades', function () {
    Server::factory()
        ->has(Site::factory(3), 'sites')
        ->create();

    $server = Server::with('sites')->first();

    expect($server->sites)->toHaveCount(3);
});

test('previous retorna o registro anterior correto, mesmo sendo o primeiro', function () {
    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'foo']);

    expect($server_2->previous()->first()->id)->toBe($server_1->id)
    ->and($server_1->previous()->first())->toBeNull();
});

test('next retorna o registro posterior correto, mesmo sendo o último', function () {
    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'foo']);

    expect($server_1->next()->first()->id)->toBe($server_2->id)
    ->and($server_2->next()->first())->toBeNull();
});

test('retorna os servidores usando o escopo de ordenação default definido', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Server::factory()->create(['name' => $third]);
    Server::factory()->create(['name' => $first]);
    Server::factory()->create(['name' => $second]);

    $servers = Server::defaultOrder()->get();

    expect($servers->get(0)->name)->toBe($first)
    ->and($servers->get(1)->name)->toBe($second)
    ->and($servers->get(2)->name)->toBe($third);
});
