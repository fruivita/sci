<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\PermissionLivewireIndex;
use App\Models\Permission;
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
test('não é possível listar as permissões sem estar autenticado', function () {
    logout();

    get(route('authorization.permission.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem das permissões', function () {
    get(route('authorization.permission.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem das permissões sem permissão específica', function () {
    Livewire::test(PermissionLivewireIndex::class)->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Livewire::test(PermissionLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de permissões esperada', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Permission::factory(120)->create();

    Livewire::test(PermissionLivewireIndex::class)
    ->assertCount('permissions', 10)
    ->set('per_page', 10)
    ->assertCount('permissions', 10)
    ->set('per_page', 25)
    ->assertCount('permissions', 25)
    ->set('per_page', 50)
    ->assertCount('permissions', 50)
    ->set('per_page', 100)
    ->assertCount('permissions', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Livewire::test(PermissionLivewireIndex::class)
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

test('é possível listar as permissões com permissão específica', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    get(route('authorization.permission.index'))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireIndex::class);
});
