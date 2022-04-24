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
test('lança exceção ao tentar cadastrar impressões em duplicidade, isto é, com com data, hora, cliente, impressora, usuário e servidor iguais', function () {
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

test('lança exceção ao tentar cadastrar impressão com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Printing::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['date',       '2000-02-31',     'Incorrect date value'],     // data inexistente
    ['date',       'foo',            'Incorrect date value'],     // não conversível em data
    ['date',       null,             'cannot be null'],           // obrigatório
    ['time',       '10:59:60',       'Incorrect time value'],     // hora inexistente
    ['time',       'foo',            'Incorrect time value'],     // não conversível em hora
    ['time',       null,             'cannot be null'],           // obrigatório
    ['filename',   Str::random(261), 'Data too long for column'], // máximo 260 caracteres
    ['file_size',  'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['file_size',  -1,               'Out of range value'],       // inteiro maior que zero
    ['pages',      'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['pages',      -1,               'Out of range value'],       // inteiro maior que zero
    ['pages',      null,             'cannot be null'],           // obrigatório
    ['copies',     'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['copies',     -1,               'Out of range value'],       // inteiro maior que zero
    ['copies',     null,             'cannot be null'],           // obrigatório
]);

test('lança exceção ao tentar definir relacionamento inválido', function ($field, $value, $message) {
    expect(
        fn () => Printing::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['client_id',     10, 'Cannot add or update a child row'], // inexistente
    ['department_id', 10, 'Cannot add or update a child row'], // inexistente
    ['printer_id',    10, 'Cannot add or update a child row'], // inexistente
    ['server_id',     10, 'Cannot add or update a child row'], // inexistente
    ['user_id',       10, 'Cannot add or update a child row'], // inexistente
]);

// Happy path
test('cadastra múltiplas impressões', function () {
    Printing::factory(30)->create();

    expect(Printing::count())->toBe(30);
});

test('nome do arquivo impresso em seu tamanho máximo é aceito', function () {
    Printing::factory()->create(['filename' => Str::random(260)]);

    expect(Printing::count())->toBe(1);
});

test('campos opcionais definidos', function ($field) {
    Printing::factory()
        ->create([$field => null]);

    expect(Printing::count())->toBe(1);
})->with([
    'filename',
    'file_size',
    'department_id',
]);

test('uma impressao pertente a um cliente, uma impressora, um usuário, uma lotação e um servidor', function () {
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
