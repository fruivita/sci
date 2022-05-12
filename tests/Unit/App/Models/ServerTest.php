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

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('throws exception when trying to create servers in duplicate, that is, with the same names', function () {
    expect(
        fn () => Server::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create server with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Server::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['name', null,             'cannot be null'],           // required
]);

// Happy path
test('create many servers', function () {
    Server::factory(30)->create();

    expect(Server::count())->toBe(30);
});

test('server name at its maximum length is accepted', function () {
    Server::factory()->create(['name' => Str::random(255)]);

    expect(Server::count())->toBe(1);
});

test('one server has many prints', function () {
    Server::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $server = Server::with('prints')->first();

    expect($server->prints)->toHaveCount(3);
});

test('one server controls many sites', function () {
    Server::factory()
        ->has(Site::factory(3), 'sites')
        ->create();

    $server = Server::with('sites')->first();

    expect($server->sites)->toHaveCount(3);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'foo']);

    expect($server_2->previous()->first()->id)->toBe($server_1->id)
    ->and($server_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'foo']);

    expect($server_1->next()->first()->id)->toBe($server_2->id)
    ->and($server_2->next()->first())->toBeNull();
});

test('returns the servers using the defined default sort scope', function () {
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
