<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Models\Role;
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
test('não é possível listar os perfis sem estar autenticado', function () {
    logout();

    get(route('authorization.role.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem dos perfis', function () {
    get(route('authorization.role.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem dos perfis sem permissão específica', function () {
    Livewire::test(RoleLivewireIndex::class)->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Livewire::test(RoleLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de perfis esperada', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Role::factory(120)->create();

    Livewire::test(RoleLivewireIndex::class)
    ->assertCount('roles', 10)
    ->set('per_page', 10)
    ->assertCount('roles', 10)
    ->set('per_page', 25)
    ->assertCount('roles', 25)
    ->set('per_page', 50)
    ->assertCount('roles', 50)
    ->set('per_page', 100)
    ->assertCount('roles', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Livewire::test(RoleLivewireIndex::class)
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

test('é possível listar os perfis com permissão específica', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    get(route('authorization.role.index'))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireIndex::class);
});
