<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente um perfil sem estar autenticado', function () {
    logout();

    get(route('authorization.role.show', $this->role))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de visualização individual do perfil', function () {
    get(route('authorization.role.show', $this->role))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual do perfil sem permissão específica', function () {
    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::RoleView->value);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de visualização individual do perfil com permissão específica', function () {
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('paginação retorna a quantidade de permissões esperada', function () {
    grantPermission(PermissionType::RoleView->value);

    Permission::factory(120)->create();
    $permissions = Permission::all();

    $this->role->permissions()->sync($permissions);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
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
    grantPermission(PermissionType::RoleView->value);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
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
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('next e previous estão disponíveis durante a visualização individual dos perfis, inclusive em se tratando do primeiro ou último registros', function () {
    $this->role->delete();
    grantPermission(PermissionType::RoleView->value);

    $first = Role::find(Role::ADMINISTRATOR);
    $second = Role::find(Role::INSTITUTIONALMANAGER);
    $last = Role::find(Role::ORDINARY);

    // possui anterior e próximo
    Livewire::test(RoleLivewireShow::class, ['role' => $second])
    ->assertSet('previous', Role::ADMINISTRATOR)
    ->assertSet('next', Role::DEPARTMENTMANAGER);

    // possui apenas próximo
    Livewire::test(RoleLivewireShow::class, ['role' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', Role::INSTITUTIONALMANAGER);

    // possui apenas anterior
    Livewire::test(RoleLivewireShow::class, ['role' => $last])
    ->assertSet('previous', Role::DEPARTMENTMANAGER)
    ->assertSet('next', null);
});