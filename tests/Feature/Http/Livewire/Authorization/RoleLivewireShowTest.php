<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
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
    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Role::VIEW);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
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

    $permissions = Permission::factory(120)->create();
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
    grantPermission(Role::VIEW);

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
    grantPermission(Role::VIEW);

    get(route('authorization.roles.show', ['role' => $this->role]))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});
