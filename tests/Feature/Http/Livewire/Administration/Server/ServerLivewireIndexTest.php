<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Server\ServerLivewireIndex;
use App\Models\Server;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível listar os servidores sem estar autenticado', function () {
    logout();

    get(route('administration.server.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem dos servidores', function () {
    get(route('administration.server.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem dos servidores sem permissão específica', function () {
    Livewire::test(ServerLivewireIndex::class)->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Livewire::test(ServerLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de servidores esperada', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Server::factory(120)->create();

    Livewire::test(ServerLivewireIndex::class)
    ->assertCount('servers', 10)
    ->set('per_page', 10)
    ->assertCount('servers', 10)
    ->set('per_page', 25)
    ->assertCount('servers', 25)
    ->set('per_page', 50)
    ->assertCount('servers', 50)
    ->set('per_page', 100)
    ->assertCount('servers', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Livewire::test(ServerLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('é possível listar os servidores com permissão específica', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    get(route('administration.server.index'))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireIndex::class);
});
