<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Client;
use App\Models\Department;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('throws an exception when trying to create duplicate prints, that is, with the same date, time, client, printer, user and server', function () {
    expect(
        fn () => Printing::factory()
            ->for(Client::factory(), 'client')
            ->for(User::factory(), 'user')
            ->for(Printer::factory(), 'printer')
            ->for(Server::factory(), 'server')
            ->count(2)
            ->create([
                'date' => '2000-10-30',
                'time' => '13:30:59',
            ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create print with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Printing::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['date',       '2000-02-31',     'Incorrect date value'],     // non-existent date
    ['date',       'foo',            'Incorrect date value'],     // not convertible to date
    ['date',       null,             'cannot be null'],           // required
    ['time',       '10:59:60',       'Incorrect time value'],     // non-existent time
    ['time',       'foo',            'Incorrect time value'],     // not convertible to hour
    ['time',       null,             'cannot be null'],           // required
    ['filename',   Str::random(261), 'Data too long for column'], // maximum 260 characters
    ['file_size',  'foo',            'Incorrect integer value'],  // not convertible to integer
    ['file_size',  -1,               'Out of range value'],       // integer greater than zero
    ['pages',      'foo',            'Incorrect integer value'],  // not convertible to integer
    ['pages',      -1,               'Out of range value'],       // integer greater than zero
    ['pages',      null,             'cannot be null'],           // required
    ['copies',     'foo',            'Incorrect integer value'],  // not convertible to integer
    ['copies',     -1,               'Out of range value'],       // integer greater than zero
    ['copies',     null,             'cannot be null'],           // required
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Printing::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['client_id',     10, 'Cannot add or update a child row'], // nonexistent
    ['department_id', 10, 'Cannot add or update a child row'], // nonexistent
    ['printer_id',    10, 'Cannot add or update a child row'], // nonexistent
    ['server_id',     10, 'Cannot add or update a child row'], // nonexistent
    ['user_id',       10, 'Cannot add or update a child row'], // nonexistent
]);

// Happy path
test('create many prints', function () {
    Printing::factory(30)->create();

    expect(Printing::count())->toBe(30);
});

test('filename printed at its maximum size is accepted', function () {
    Printing::factory()->create(['filename' => Str::random(260)]);

    expect(Printing::count())->toBe(1);
});

test('optional fields defined', function ($field) {
    Printing::factory()
        ->create([$field => null]);

    expect(Printing::count())->toBe(1);
})->with([
    'filename',
    'file_size',
    'department_id',
]);

test('a print belongs to one client, one printer, one user, one department and one server', function () {
    $client = Client::factory()->create();
    $department = Department::factory()->create();
    $user = User::factory()->create();
    $printer = Printer::factory()->create();
    $server = Server::factory()->create();

    $print = Printing::factory()
        ->for($client, 'client')
        ->for($department, 'department')
        ->for($user, 'user')
        ->for($printer, 'printer')
        ->for($server, 'server')
        ->create();

    $print->load(['client', 'department', 'printer', 'server', 'user']);

    expect($print->client)->toBeInstanceOf(Client::class)
    ->and($print->department)->toBeInstanceOf(Department::class)
    ->and($print->user)->toBeInstanceOf(User::class)
    ->and($print->printer)->toBeInstanceOf(Printer::class)
    ->and($print->server)->toBeInstanceOf(Server::class);
});
