<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleIndex;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('renderização da página de listagem do perfis é restrito aos usuários autenticados', function () {
    logout();

    get(route('authorization.roles.index'))
    ->assertRedirect(route('login'))
    ->assertDontSee(route('authorization.roles.index'));
});

test('não é possível listar perfis sem permissão específica', function () {
    get(route('authorization.roles.index'))
    ->assertForbidden()
    ->assertDontSee(route('authorization.roles.index'));
});

test('exibir o modal de edição do perfil requer permissão específica', function () {
    $role = Role::factory()->create();

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->assertForbidden();
});

test('atualizar o perfil requer permissão específica', function () {
    $role = grantPermission(Role::UPDATE);

    // concede permissão para abrir o modal de edição
    $livewire = Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id);

    // remove a permissão
    revokePermission(Role::UPDATE);

    $livewire
    ->set('editing.name', 'foo')
    ->set('editing.description', 'foo')
    ->call('save')
    ->assertForbidden();
});

// Rules
test('nome do perfil é obrigatório', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('editing.name', '')
    ->call('save')
    ->assertHasErrors(['editing.name' => 'required']);
});

test('nome do perfil deve ser uma string', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('editing.name', ['bar'])
    ->call('save')
    ->assertHasErrors(['editing.name' => 'string']);
});

test('nome do perfil deve ter no máximo 50 caracteres', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('editing.name', Str::random(51))
    ->call('save')
    ->assertHasErrors(['editing.name' => 'max']);
});

test('descrição do perfil deve ser uma string', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('editing.description', ['bar'])
    ->call('save')
    ->assertHasErrors(['editing.description' => 'string']);
});

test('description do perfil deve ter no máximo 255 caracteres', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('editing.description', Str::random(256))
    ->call('save')
    ->assertHasErrors(['editing.description' => 'max']);
});

test('ids das permissões que serão associadas ao perfil deve ser um array', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('selected', 1)
    ->call('save')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids das permissões que serão associadas ao perfil devem existir previamente no banco de dados', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->set('selected', [-10])
    ->call('save')
    ->assertHasErrors(['selected' => 'exists']);
});

// Happy path
test('renderiza o componente livewire correto para listar os perfis', function () {
    grantPermission(Role::VIEWANY);

    get(route('authorization.roles.index'))->assertSeeLivewire(RoleIndex::class);
});

test('exibir o modal de edição do perfil é possível com permissão específica', function () {
    $role = grantPermission(Role::UPDATE);

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $role->id)
    ->assertOk();
});

test('é possível listar perfis com permissão específica', function () {
    grantPermission(Role::VIEWANY);

    get(route('authorization.roles.index'))
    ->assertOk()
    ->assertSee(route('authorization.roles.index'));
});

test('é possível atualizar um perfil com permissão específica', function () {
    grantPermission(Role::UPDATE);

    $editing = Role::factory()->create();
    $editing->load('permissions');

    $permission = Permission::first();

    expect($editing->name)->not()->toBe('foo')
    ->and($editing->description)->not()->toBe('bar')
    ->and($editing->permissions)->toBeEmpty();

    Livewire::test(RoleIndex::class)
    ->call('showEditModal', $editing->id)
    ->set('editing.name', 'foo')
    ->set('editing.description', 'bar')
    ->set('selected', [$permission->id])
    ->call('save');

    $editing->refresh();

    expect($editing->name)->toBe('foo')
    ->and($editing->description)->toBe('bar')
    ->and($editing->permissions->first()->id)->toBe($permission->id);
});
