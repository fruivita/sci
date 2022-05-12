<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Client;
use App\Models\Printing;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('throws exception when trying to create clients in duplicate, that is, with the same names', function () {
    expect(
        fn () => Client::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create client with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Client::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['name', null,             'cannot be null'],           // required
]);

// Happy path
test('create many clients', function () {
    Client::factory(30)->create();

    expect(Client::count())->toBe(30);
});

test('client name in its maximum size is accepted', function () {
    Client::factory()->create(['name' => Str::random(255)]);

    expect(Client::count())->toBe(1);
});

test('one client has many prints', function () {
    Client::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $client = Client::with('prints')->first();

    expect($client->prints)->toHaveCount(3);
});
