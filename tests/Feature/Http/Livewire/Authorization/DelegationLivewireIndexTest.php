<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\DelegationLivewireIndex;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->department = Department::factory()->create();

    $this->user = login('foo');
    $this->user->department_id = $this->department->id;
    $this->user->role_id = Role::INSTITUTIONALMANAGER;
    $this->user->save();
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível acessar a página de delegação sem estar autenticado', function () {
    logout();

    get(route('authorization.delegations.index'))
    ->assertRedirect(route('login'));
});

test('usuário não pode delegar perfil, se o perfil do destinatário for superior na aplicação', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('usuário não pode delegar perfil para usuário de outra lotação', function () {
    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::DEPARTMENTMANAGER
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::DEPARTMENTMANAGER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('usuário não pode remover delegação inexistente', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::DEPARTMENTMANAGER
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::DEPARTMENTMANAGER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('usuário não pode remover delegação de perfil superior', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id);
});

test('usuário não pode remover delegação de usuário de outra lotação', function () {
    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id);
});

// Happy path
test('autenticado é possível renderizar o componente de delegação, mesmo com o perfil ordinário', function () {
    $this->user->role_id = Role::ORDINARY;
    $this->user->save();

    get(route('authorization.delegations.index'))
    ->assertOk()
    ->assertSeeLivewire(DelegationLivewireIndex::class);
});

test('paginação retorna a quantidade de usuários esperada', function () {
    User::factory(120)
    ->for($this->department, 'department')
    ->create();

    Livewire::test(DelegationLivewireIndex::class)
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
    Livewire::test(DelegationLivewireIndex::class)
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

test('exibe apenas os usuários disponíveis para delegação, isto é, apenas os de mesma lotação', function () {
    User::factory(30)->create();
    User::factory(5)
    ->for($this->department, 'department')
    ->create();

    Livewire::test(DelegationLivewireIndex::class)
    ->assertCount('users', 6);
});

test('usuário pode delegar perfil dentro da mesma lotação, se o perfil do destinatário for inferior na aplicação', function () {

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ORDINARY
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_bar->role_granted_by)->toBe($this->user->id);
});

test('usuário pode remover delegação de usuário da mesma lotação, com perfil igual ou inferior, mesmo delegado por outrem', function () {

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER
    ]);

    $user_baz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_baz)
    ->assertOk()
    ->call('destroy', $user_taz)
    ->assertOk();

    expect($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull();
});

test('delegação atribui perfil do usuário autenticado e revogação atribui o perfil ordinário', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ORDINARY
    ]);

    $livewire = Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_bar->role_granted_by)->toBe($this->user->id);

    $livewire
    ->call('destroy', $user_bar)
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('ao remover delegação de um usuário, remove também as delegações feitas por ele', function () {

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $this->user->id,
    ]);

    $user_baz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_loren = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $this->user->id,
    ]);

    $user_ipsen = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $this->user->id,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_bar)
    ->assertOk();

    $this->user->refresh();
    $user_bar->refresh();
    $user_baz->refresh();
    $user_taz->refresh();
    $user_loren->refresh();
    $user_ipsen->refresh();

    expect($this->user->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($this->user->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull()
    ->and($user_loren->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_loren->role_granted_by)->toBe($this->user->id)
    ->and($user_ipsen->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_ipsen->role_granted_by)->toBe($this->user->id);
});

test('é possível remover a própria delegação', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR
    ]);

    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->role_granted_by = $user_bar->id;
    $this->user->save();

    expect($this->user->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($this->user->role_granted_by)->toBe($user_bar->id);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $this->user)
    ->assertOk();

    expect($this->user->role_id)->toBe(Role::ORDINARY)
    ->and($this->user->role_granted_by)->toBeNull();
});
