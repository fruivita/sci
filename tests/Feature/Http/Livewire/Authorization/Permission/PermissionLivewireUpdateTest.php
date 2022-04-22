<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->permission = Permission::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível editar a permissão sem estar autenticado', function () {
    logout();

    get(route('authorization.permission.edit', $this->permission))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de edição da permissão', function () {
    get(route('authorization.permission.edit', $this->permission))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição da permissão sem permissão específica', function () {
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->assertForbidden();
});

test('não é possível atualizar a permissão sem permissão específica', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar');

    // remove a permissão
    revokePermission(PermissionType::PermissionUpdate->value);

    // expira o cache das permissões em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome da permissão é obrigatório', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', '')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'required']);
});

test('nome da permissão deve ser uma string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['permission.name' => 'string']);
});

test('nome da permissão deve ter no máximo 50 caracteres', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['permission.name' => 'max']);
});

test('nome da permissão deve ser único', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission = Permission::factory()->create(['name' => 'another foo']);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('permission.name', 'foo')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'unique']);
});

test('descrição da permissão deve ser uma string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', ['baz'])
    ->call('update')
    ->assertHasErrors(['permission.description' => 'string']);
});

test('descrição da permissão deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['permission.description' => 'max']);
});

test('ids dos perfis que serão associadas à permissão deve ser um array', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids dos perfis que serão associadas à permissão devem existir previamente no banco de dados', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de edição da permissão com permissão específica', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    get(route('authorization.permission.edit', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireUpdate::class);
});

test('define os perfis que devem ser pre-selecionadas de acodo com os relacionamentos da permissão', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Role::factory(30)->create();
    $permission = Permission::factory()
            ->has(Role::factory(20), 'roles')
            ->create();

    $permission->load('roles');

    $selected = $permission
                ->roles
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', 20)
    ->assertSet('selected', $selected);
});

test('actions de manipulação do checkbox dos perfis funcionam como esperado', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    Role::factory(50)->create();
    $permission = Permission::factory()->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('selected', $count + 50)
    ->set('checkbox_action', CheckboxAction::UncheckAll->value)
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAllPage->value)
    ->assertCount('selected', 10)
    ->set('checkbox_action', CheckboxAction::UncheckAllPage->value)
    ->assertCount('selected', 0);
});

test('paginação retorna a quantidade de perfis esperada', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

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
    grantPermission(PermissionType::PermissionUpdate->value);

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
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Role::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount($count);

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty exibe os resultados esperados de acordo com o cache', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    testTime()->freeze();
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Role::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count);

    // expirará o cache
    testTime()->addSeconds(1);

    // contabiliza as novas inserções após expirado
    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count + 3);
});

test('emite evento de feedback ao atualizar uma permissão', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('descrição e perfis associados são opcionais na atualização da permissão', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

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

    expect($permission->roles)->toBeEmpty()
    ->and($permission->description)->toBeNull();
});

test('é possível atualizar uma permissão com permissão específica', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $this->permission->load('roles');

    expect($this->permission->name)->toBe('foo')
    ->and($this->permission->description)->toBe('bar')
    ->and($this->permission->roles)->toBeEmpty();

    $role = Role::first();

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

test('next e previous são registrados em cache com expiração de um minuto', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);
    $permission_3 = Permission::factory()->create(['id' => 3]);

    testTime()->freeze();
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($permission_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($permission_3->id);

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next e previous estão definidos durante a edição individual das permissões, inclusive em se tratando do primeiro ou último registros', function () {
    Permission::whereNotNull('id')->delete();

    $first = Permission::factory()->create(['id' => PermissionType::PermissionViewAny->value]);
    $second = Permission::factory()->create(['id' => PermissionType::PermissionView->value]);
    $last = Permission::factory()->create(['id' => PermissionType::PermissionUpdate->value]);

    grantPermission(PermissionType::PermissionUpdate->value);

    // possui anterior e próximo
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $second])
    ->assertSet('previous', PermissionType::PermissionViewAny->value)
    ->assertSet('next', PermissionType::PermissionUpdate->value);

    // possui apenas próximo
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', PermissionType::PermissionView->value);

    // possui apenas anterior
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $last])
    ->assertSet('previous', PermissionType::PermissionView->value)
    ->assertSet('next', null);
});
