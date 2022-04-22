<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\UserLivewireIndex;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível listar os usuários sem estar autenticado', function () {
    logout();

    get(route('authorization.user.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem dos usuários sem permissão específica', function () {
    get(route('authorization.user.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem dos usuários sem permissão específica', function () {
    Livewire::test(UserLivewireIndex::class)->assertForbidden();
});

test('não é possível exibir o modal de edição de usuário sem permissão específica', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', false)
    ->assertForbidden();
});

test('não é possível atualizar um usuário sem permissão específica', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $livewire = Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', true);

    revokePermission(PermissionType::UserUpdate->value);

    // expira o cache das permissões em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

test('os perfis não estão disponíveis se o modal não puder ser carregadado', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect(Role::count())->toBeGreaterThan(1);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertSet('roles', null);

    expect(Role::count())->toBeGreaterThan(1);
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('id do perfil que será associado ao usuário deve ser um inteiro', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', 'foo')
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'integer']);
});

test('id do perfil que será associado ao usuário deve existir previamente no banco de dados', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', -1)
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'exists']);
});

test('termo pesquisável deve ser uma string', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('termo pesquisável deve ter no máximo 50 caracteres', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('termo pesquisável está sujeito à validação em tempo real', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('é possível renderizar o componente de listagem dos usuários com permissão específica', function () {
    grantPermission(PermissionType::UserViewAny->value);

    get(route('authorization.user.index'))
    ->assertOk()
    ->assertSeeLivewire(UserLivewireIndex::class);
});

test('paginação retorna a quantidade de usuários esperada', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory(120)->create();

    Livewire::test(UserLivewireIndex::class)
    ->assertCount('users', 10)
    ->set('per_page', 10)
    ->assertCount('users', 10)
    ->set('per_page', 25)
    ->assertCount('users', 25)
    ->set('per_page', 50)
    ->assertCount('users', 50)
    ->set('per_page', 100)
    ->assertCount('users', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
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

test('é possível exibir o modal de edição de usuário com permissão específica', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertOk()
    ->assertSet('show_edit_modal', true);
});

test('os perfis estão disponíveis se o modal puder ser carregadado', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    expect(Role::count())->toBe(4);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertCount('roles', 4);

    expect(Role::count())->toBe(4);
});

test('emite evento de feedback ao atualizar um usuário', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('é possível atualizar um usuário com permissão específica', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $user = User::where('username', 'foo')->first();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $user)
    ->assertSet('editing.role_id', Role::ORDINARY)
    ->set('editing.role_id', Role::ADMINISTRATOR)
    ->call('update')
    ->assertOk();

    $user->refresh();

    expect($user->role->id)->toBe(Role::ADMINISTRATOR);
});

test('atualização do perfil remove eventual delegação', function () {
    $department = Department::factory()->create();
    logout();

    $bar = login('bar');

    $bar->role_id = Role::ADMINISTRATOR;
    $bar->department_id = $department->id;
    $bar->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->department_id = $department->id;
    $this->user->role_granted_by = $bar->id;
    $this->user->save();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user)
    ->assertSet('editing.role_id', Role::ADMINISTRATOR)
    ->assertSet('editing.role_granted_by', $bar->id)
    ->set('editing.role_id', Role::INSTITUTIONALMANAGER)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($this->user->role_granted_by)->toBeNull();
});

test('pesquisa retorna os resultados esperados', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory()->create([
        'name' => 'fulano bar',
        'username' => 'bar baz',
    ]);

    User::factory()->create([
        'name' => 'fulano foo bazz',
        'username' => 'taz',
    ]);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', 'taz')
    ->assertCount('users', 1)
    ->set('term', 'fulano')
    ->assertCount('users', 2);
});
