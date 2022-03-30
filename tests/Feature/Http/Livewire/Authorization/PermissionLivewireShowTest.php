<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\PermissionLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->permission = Permission::factory()->create(['name' => 'foo', 'description' => 'bar']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente uma permissão sem estar autenticado', function () {
    logout();

    get(route('authorization.permissions.show', $this->permission->id))
    ->assertRedirect(route('login'));
});

test('não é possível executar a rota de visualização individual da permissão sem permissão específica', function () {
    get(route('authorization.permissions.show', $this->permission->id))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual da permissão sem permissão específica', function () {
    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $this->permission->id])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Permission::VIEW);

    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $this->permission->id])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de visualização individual da permissão com permissão específica', function () {
    grantPermission(Permission::VIEW);

    get(route('authorization.permissions.show', $this->permission->id))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});

test('paginação retorna a quantidade de perfis esperada', function () {
    grantPermission(Permission::VIEW);

    $roles = Role::factory(120)->create();
    $this->permission->roles()->sync($roles);

    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $this->permission->id])
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
    grantPermission(Permission::VIEW);

    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $this->permission->id])
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

test('é possível visualizar individualmente uma permissão com permissão específica', function () {
    grantPermission(Permission::VIEW);

    get(route('authorization.permissions.show', $this->permission->id))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});

test('next e previous estão presentes na permissão durante a visualização individual das permissões, inclusive em se tratando do primeiro ou último registros', function () {
    $this->permission->delete();
    grantPermission(Permission::VIEW);

    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);
    $permission_3 = Permission::factory()->create(['id' => 3]);
    $permission_4 = Permission::orderBy('id', 'desc')->first();

    // possui anterior e próximo
    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $permission_2->id])
    ->assertSet('permission.previous', 1)
    ->assertSet('permission.next', 3);

    // possui apenas próximo
    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $permission_1->id])
    ->assertSet('permission.previous', null)
    ->assertSet('permission.next', 2);

    // possui apenas anterior
    Livewire::test(PermissionLivewireShow::class, ['permission_id' => $permission_4->id])
    ->assertSet('permission.previous', 3)
    ->assertSet('permission.next', null);
});
