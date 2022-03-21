<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível editar o perfil sem estar autenticado', function () {
    logout();

    get(route('authorization.roles.edit', $this->role))
    ->assertRedirect(route('login'));
});

test('não é possível executar a rota de edião do perfil sem permissão específica', function () {
    get(route('authorization.roles.edit', $this->role))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição do perfil sem permissão específica', function () {
    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->assertForbidden();
});

test('não é possível atualizar o perfil sem permissão específica', function () {
    grantPermission(Role::UPDATE);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', 'new foo')
    ->set('role.description', 'new bar');

    // remove a permissão
    revokePermission(Role::UPDATE);

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome do perfil é obrigatório', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', '')
    ->call('update')
    ->assertHasErrors(['role.name' => 'required']);
});

test('nome do perfil deve ser uma string', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['role.name' => 'string']);
});

test('nome do perfil deve ter no máximo 50 caracteres', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['role.name' => 'max']);
});

test('nome do perfil deve ser único', function () {
    grantPermission(Role::UPDATE);

    $role = Role::factory()->create(['name' => 'another foo']);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('role.name', 'foo')
    ->call('update')
    ->assertHasErrors(['role.name' => 'unique']);
});

test('descrição do perfil deve ser uma string', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.description', ['bar'])
    ->call('update')
    ->assertHasErrors(['role.description' => 'string']);
});

test('description do perfil deve ter no máximo 255 caracteres', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['role.description' => 'max']);
});

test('ids das permissões que serão associadas ao perfil deve ser um array', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids das permissões que serão associadas ao perfil devem existir previamente no banco de dados', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

// Happy path
test('é possível renderizar o componente de edição do perfil com permissão específica', function () {
    grantPermission(Role::UPDATE);

    get(route('authorization.roles.edit', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireUpdate::class);
});

test('é possível atualizar um perfil com permissão específica', function () {
    grantPermission(Role::UPDATE);

    $this->role->load('permissions');

    $permission = Permission::first();

    expect($this->role->name)->toBe('foo')
    ->and($this->role->description)->toBe('bar')
    ->and($this->role->permissions)->toBeEmpty();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', 'new foo')
    ->set('role.description', 'new bar')
    ->set('selected', [$permission->id])
    ->call('update');

    $this->role->refresh();

    expect($this->role->name)->toBe('new foo')
    ->and($this->role->description)->toBe('new bar')
    ->and($this->role->permissions->first()->id)->toBe($permission->id);
});
