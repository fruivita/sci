<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Http\Livewire\Authorization\RoleLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
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

test('não é possível executar a rota de edição do perfil sem permissão específica', function () {
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

    // expira o cache das permissões em 5 segundos
    $this->travel(6)->seconds();

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

test('descrição do perfil deve ter no máximo 255 caracteres', function () {
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

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de edição do perfil com permissão específica', function () {
    grantPermission(Role::UPDATE);

    get(route('authorization.roles.edit', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireUpdate::class);
});

test('define as permissões que devem ser pre-selecionadas de acodo com os relacionamentos da entidade', function () {
    grantPermission(Role::UPDATE);

    $related = 20;

    Permission::factory(30)->create();
    $role = Role::factory()
            ->has(Permission::factory($related), 'permissions')
            ->create();

    $role->load('permissions');

    $selected = $role
                    ->permissions
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->toArray();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->assertCount('selected', $related)
    ->assertSet('selected', $selected);
});

test('actions de manipulação do checkbox das permissões funcionam como esperado', function () {
    grantPermission(Role::UPDATE);

    Permission::factory(50)->create();
    $role = Role::factory()->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
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

test('paginação retorna a quantidade de permissões esperada', function () {
    grantPermission(Role::UPDATE);

    Permission::factory(120)->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
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
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
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
    grantPermission(Role::UPDATE);

    Permission::factory(5)->create();

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role]);

    expect(Cache::missing('all-checkable' . $livewire->id))->toBeTrue();

    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Permission::factory(3)->create();

    expect(Cache::has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(Cache::get('all-checkable' . $livewire->id))->toHaveCount(6);

    // expirará o cache
    $this->travel(61)->seconds();
    expect(Cache::missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty exibe os resultados esperados de acordo com o cache', function () {
    grantPermission(Role::UPDATE);

    Permission::factory(5)->create();

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Permission::factory(3)->create();

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

test('emite evento de feedback ao atualizar um perfil', function () {
    grantPermission(Role::UPDATE);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->call('update')
    ->assertEmitted('feedback', __('Success!'), FeedbackType::Success);
});

test('descrição e permissões associados são opcionais na atualização do perfil', function () {
    grantPermission(Role::UPDATE);

    $role = Role::factory()
    ->has(Permission::factory(1), 'permissions')
    ->create(['description' => 'foo']);

    expect($role->permissions)->toHaveCount(1)
    ->and($role->description)->toBe('foo');

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('role.description', null)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $role->refresh()->load('permissions');

    expect($role->permissions)->toBeEmpty(1)
    ->and($role->description)->toBeNull();
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

test('next e previous são registrados em cache com expirarção de um minuto', function () {
    grantPermission(Role::UPDATE);

    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);
    $role_3 = Role::factory()->create(['id' => 3]);

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $role_2]);

    expect(Cache::has('previous' . $livewire->id))->toBeTrue()
    ->and(Cache::get('previous' . $livewire->id))->toBe($role_1->id)
    ->and(Cache::has('next' . $livewire->id))->toBeTrue()
    ->and(Cache::get('next' . $livewire->id))->toBe($role_3->id);

    // expirará o cache
    $this->travel(61)->seconds();
    expect(Cache::missing('previous' . $livewire->id))->toBeTrue()
    ->and(Cache::missing('next' . $livewire->id))->toBeTrue();
});

test('next e previous estão definidos durante a edição individual dos perfis, inclusive em se tratando do primeiro ou último registros', function () {
    $this->role->delete();
    grantPermission(Role::UPDATE);

    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);
    $role_3 = Role::factory()->create(['id' => 3]);
    $role_4 = Role::orderBy('id', 'desc')->first();

    // possui anterior e próximo
    Livewire::test(RoleLivewireUpdate::class, ['role' => $role_2])
    ->assertSet('previous', 1)
    ->assertSet('next', 3);

    // possui apenas próximo
    Livewire::test(RoleLivewireUpdate::class, ['role' => $role_1])
    ->assertSet('previous', null)
    ->assertSet('next', 2);

    // possui apenas anterior
    Livewire::test(RoleLivewireUpdate::class, ['role' => $role_4])
    ->assertSet('previous', 3)
    ->assertSet('next', null);
});
