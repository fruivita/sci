<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Http\Livewire\Authorization\PermissionLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
test('não é possível editar a permissão sem estar autenticado', function () {
    logout();

    get(route('authorization.permissions.edit', $this->permission))
    ->assertRedirect(route('login'));
});

test('não é possível executar a rota de edição da permissão sem permissão específica', function () {
    get(route('authorization.permissions.edit', $this->permission))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição da permissão sem permissão específica', function () {
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->assertForbidden();
});

test('não é possível atualizar a permissão sem permissão específica', function () {
    grantPermission(Permission::UPDATE);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar');

    // remove a permissão
    revokePermission(Permission::UPDATE);

    // expira o cache das permissões em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome da permissão é obrigatório', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', '')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'required']);
});

test('nome da permissão deve ser uma string', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['permission.name' => 'string']);
});

test('nome da permissão deve ter no máximo 50 caracteres', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['permission.name' => 'max']);
});

test('nome da permissão deve ser único', function () {
    grantPermission(Permission::UPDATE);

    $permission = Permission::factory()->create(['name' => 'another foo']);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('permission.name', 'foo')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'unique']);
});

test('descrição da permissão deve ser uma string', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', ['bar'])
    ->call('update')
    ->assertHasErrors(['permission.description' => 'string']);
});

test('descrição da permissão deve ter no máximo 255 caracteres', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['permission.description' => 'max']);
});

test('ids dos perfis que serão associadas à permissão deve ser um array', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids dos perfis que serão associadas à permissão devem existir previamente no banco de dados', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de edição da permissão com permissão específica', function () {
    grantPermission(Permission::UPDATE);

    get(route('authorization.permissions.edit', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireUpdate::class);
});

test('define os perfis que devem ser pre-selecionadas de acodo com os relacionamentos da entidade', function () {
    grantPermission(Permission::UPDATE);

    $related = 20;

    Role::factory(30)->create();
    $permission = Permission::factory()
            ->has(Role::factory($related), 'roles')
            ->create();

    $permission->load('roles');

    $selected = $permission
                    ->roles
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->toArray();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', $related)
    ->assertSet('selected', $selected);
});

test('actions de manipulação do checkbox dos perfis funcionam como esperado', function () {
    grantPermission(Permission::UPDATE);

    Role::factory(50)->create();
    $permission = Permission::factory()->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('selected', 51)
    ->set('checkbox_action', CheckboxAction::UncheckAll->value)
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAllPage->value)
    ->assertCount('selected', 10)
    ->set('checkbox_action', CheckboxAction::UncheckAllPage->value)
    ->assertCount('selected', 0);
});

test('paginação retorna a quantidade de perfis esperada', function () {
    grantPermission(Permission::UPDATE);

    Role::factory(120)->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
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
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
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

test('getCheckAllProperty é registrado em cache com expiração de um minuto', function () {
    grantPermission(Permission::UPDATE);

    Role::factory(5)->create();

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission]);

    expect(Cache::missing('all-checkable' . $livewire->id))->toBeTrue();

    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Role::factory(3)->create();

    expect(Cache::has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(Cache::get('all-checkable' . $livewire->id))->toHaveCount(6);

    // expirará o cache
    $this->travel(61)->seconds();
    expect(Cache::missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty exibe os resultados esperados de acordo com o cache', function () {
    grantPermission(Permission::UPDATE);

    Role::factory(5)->create();

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Role::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 6);

    // expirará o cache
    $this->travel(61)->seconds();

    // contabiliza as novas inserções após expirado
    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 9);
});

test('emite evento de feedback ao atualizar uma permissão', function () {
    grantPermission(Permission::UPDATE);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->call('update')
    ->assertEmitted('feedback', __('Success!'), FeedbackType::Success);
});

test('descrição e perfis associados são opcionais na atualização da permissão', function () {
    grantPermission(Permission::UPDATE);

    $permission = Permission::factory()
    ->has(Role::factory(1), 'roles')
    ->create(['description' => 'foo']);

    expect($permission->roles)->toHaveCount(1)
    ->and($permission->description)->toBe('foo');

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('permission.description', null)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $permission->refresh()->load('roles');

    expect($permission->roles)->toBeEmpty(1)
    ->and($permission->description)->toBeNull();
});

test('é possível atualizar uma permissão com permissão específica', function () {
    grantPermission(Permission::UPDATE);

    $this->permission->load('roles');

    $role = Role::first();

    expect($this->permission->name)->toBe('foo')
    ->and($this->permission->description)->toBe('bar')
    ->and($this->permission->roles)->toBeEmpty();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar')
    ->set('selected', [$role->id])
    ->call('update');

    $this->permission->refresh();

    expect($this->permission->name)->toBe('new foo')
    ->and($this->permission->description)->toBe('new bar')
    ->and($this->permission->roles->first()->id)->toBe($role->id);
});

test('next e previous são registrados em cache com expirarção de um minuto', function () {
    grantPermission(Permission::UPDATE);

    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);
    $permission_3 = Permission::factory()->create(['id' => 3]);

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_2]);

    expect(Cache::has('previous' . $livewire->id))->toBeTrue()
    ->and(Cache::get('previous' . $livewire->id))->toBe($permission_1->id)
    ->and(Cache::has('next' . $livewire->id))->toBeTrue()
    ->and(Cache::get('next' . $livewire->id))->toBe($permission_3->id);

    // expirará o cache
    $this->travel(61)->seconds();
    expect(Cache::missing('previous' . $livewire->id))->toBeTrue()
    ->and(Cache::missing('next' . $livewire->id))->toBeTrue();
});

test('next e previous estão definidos durante a edição individual das permissões, inclusive em se tratando do primeiro ou último registros', function () {
    $this->permission->delete();
    grantPermission(Permission::UPDATE);

    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);
    $permission_3 = Permission::factory()->create(['id' => 3]);
    $permission_4 = Permission::orderBy('id', 'desc')->first();

    // possui anterior e próximo
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_2])
    ->assertSet('previous', 1)
    ->assertSet('next', 3);

    // possui apenas próximo
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_1])
    ->assertSet('previous', null)
    ->assertSet('next', 2);

    // possui apenas anterior
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_4])
    ->assertSet('previous', 3)
    ->assertSet('next', null);
});
