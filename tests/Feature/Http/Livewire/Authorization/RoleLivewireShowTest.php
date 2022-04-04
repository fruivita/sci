<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente um perfil sem estar autenticado', function () {
    logout();

    get(route('authorization.roles.show', $this->role))
    ->assertRedirect(route('login'));
});

test('não é possível executar a rota de visualização individual do perfil sem permissão específica', function () {
    get(route('authorization.roles.show', $this->role))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual do perfil sem permissão específica', function () {
    Livewire::test(RoleLivewireShow::class, ['role_id' => $this->role->id])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Role::VIEW);

    Livewire::test(RoleLivewireShow::class, ['role_id' => $this->role->id])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de visualização individual do perfil com permissão específica', function () {
    grantPermission(Role::VIEW);

    get(route('authorization.roles.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('paginação retorna a quantidade de permissões esperada', function () {
    grantPermission(Role::VIEW);

    Permission::factory(120)->create();
    $permissions = Permission::all();

    $this->role->permissions()->sync($permissions);

    Livewire::test(RoleLivewireShow::class, ['role_id' => $this->role->id])
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
    grantPermission(Role::VIEW);

    Livewire::test(RoleLivewireShow::class, ['role_id' => $this->role->id])
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

test('é possível visualizar individualmente um perfil com permissão específica', function () {
    grantPermission(Role::VIEW);

    get(route('authorization.roles.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('next e previous estão presentes no perfil durante a visualização individual dos perfis, inclusive em se tratando do primeiro ou último registros', function () {
    $this->role->delete();
    grantPermission(Role::VIEW);

    // possui anterior e próximo
    Livewire::test(RoleLivewireShow::class, ['role_id' => Role::INSTITUTIONALMANAGER])
    ->assertSet('role.previous', Role::ADMINISTRATOR)
    ->assertSet('role.next', Role::DEPARTMENTMANAGER);

    // possui apenas próximo
    Livewire::test(RoleLivewireShow::class, ['role_id' => Role::ADMINISTRATOR])
    ->assertSet('role.previous', null)
    ->assertSet('role.next', Role::INSTITUTIONALMANAGER);

    // possui apenas anterior
    Livewire::test(RoleLivewireShow::class, ['role_id' => Role::ORDINARY])
    ->assertSet('role.previous', Role::DEPARTMENTMANAGER)
    ->assertSet('role.next', null);
});
