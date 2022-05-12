<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Delegation\DelegationLivewireIndex;
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
test('cannot access delegation page without being authenticated', function () {
    logout();

    get(route('authorization.delegations.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access department delegations listing route', function () {
    get(route('authorization.permission.index'))
    ->assertForbidden();
});

test('cannot render department delegations listing component without specific permission', function () {
    Livewire::test(DelegationLivewireIndex::class)->assertForbidden();
});

test('user cannot delegate role, if delegated role is higher in application', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot delegate role to user from another department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::DEPARTMENTMANAGER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::DEPARTMENTMANAGER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot remove non-existent delegation', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::DEPARTMENTMANAGER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::DEPARTMENTMANAGER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot remove higher role user delegation', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id);
});

test('user cannot remove user delegation from another department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id);
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('searchable term must be a string', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('searchable term must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('searchable term is validated in real time', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('with specific permission it is possible to render the department delegations listing component', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    get(route('authorization.delegations.index'))
    ->assertOk()
    ->assertSeeLivewire(DelegationLivewireIndex::class);
});

test('pagination returns the expected amount of users', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

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

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

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

test('displays only the users available for delegation, that is, only those from the same department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    User::factory(30)->create();
    User::factory(5)
    ->for($this->department, 'department')
    ->create();

    Livewire::test(DelegationLivewireIndex::class)
    ->assertCount('users', 6);
});

test('user can delegate role within the same department if delegated role is lower in application', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ORDINARY,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_bar->role_granted_by)->toBe($this->user->id);
});

test('user can remove user delegation from the same department, with the same or lower role, even delegated by someone else', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
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

test('delegation assigns authenticated user role and revocation assigns ordinary role', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ORDINARY,
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

test("when removing a user's delegation, it also removes the delegations made by the user", function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

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

test('remove the delegation itself', function () {
    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);

    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->role_granted_by = $user_bar->id;
    $this->user->save();

    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    expect($this->user->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($this->user->role_granted_by)->toBe($user_bar->id);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $this->user)
    ->assertOk();

    expect($this->user->role_id)->toBe(Role::ORDINARY)
    ->and($this->user->role_granted_by)->toBeNull();
});

test('search returns expected results', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    User::factory()->create([
        'name' => 'fulano bar',
        'username' => 'bar baz',
        'department_id' => $this->department->id,
    ]);

    User::factory()->create([
        'name' => 'fulano foo bazz',
        'username' => 'taz',
        'department_id' => $this->department->id,
    ]);

    // will not be displayed, because from another department
    User::factory()
    ->for(Department::factory(), 'department')
    ->create([
        'name' => 'another department fulano foo bazz',
        'username' => 'another taz',
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('term', 'taz')
    ->assertCount('users', 1)
    ->set('term', 'fulano')
    ->assertCount('users', 2);
});
