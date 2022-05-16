<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Department;
use App\Models\Printing;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
});

// Exceptions
test('throws an exception when trying to create users in duplicate, that is, with the same username or guid', function () {
    expect(
        fn () => User::factory(2)->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => User::factory(2)->create(['guid' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create user with invalid field', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256),     'Data too long for column'], // maximum 255 characters
    ['username', Str::random(21),  'Data too long for column'], // maximum 20 characters
    ['username', null,             'cannot be null'],           // required
    ['password', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['guid', Str::random(256),     'Data too long for column'], // maximum 255 characters
    ['domain', Str::random(256),   'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['role_id',         10, 'Cannot add or update a child row'], // nonexistent
    ['role_granted_by', 10, 'Cannot add or update a child row'], // nonexistent
]);

// Happy path
test('create many users', function () {
    User::factory(30)->create();

    expect(User::count())->toBe(30);
});

test('optional user fields are accepted', function () {
    User::factory()->create(['name' => null]);

    expect(User::count())->toBe(1);
});

test('user fields at their maximum size are accepted', function () {
    User::factory()->create([
        'name' => Str::random(255),
        'username' => Str::random(20),
        'password' => Str::random(255),
        'guid' => Str::random(255),
        'domain' => Str::random(255),
    ]);

    expect(User::count())->toBe(1);
});

test('one user has one role', function () {
    $role = Role::factory()->create();

    $user = User::factory()
    ->for($role, 'role')
    ->create();

    $user->load(['role']);

    expect($user->role)->toBeInstanceOf(Role::class);
});

test('default user role is ordinary', function () {
    $user = User::create([
        'username' => 'foo',
    ]);

    $user->refresh();

    expect($user->role->id)->toBe(Role::ORDINARY);
});

test('if do not inform a department, the default department is "departmentless"', function () {
    $user = User::create([
        'username' => 'foo',
    ]);

    $user->refresh();

    expect($user->department->id)->toBe(Department::DEPARTMENTLESS);
});

test('user can delegate their role to several others, however the user can only receive a single delegation', function () {
    $delegated_amount = 3;

    $user_delegator = User::factory()->create();

    User::factory(3)->create(['role_granted_by' => $user_delegator->id]);

    $user_delegator->load(['delegatedUsers', 'delegator']);
    $user_delegated = User::with('delegator')
    ->where('role_granted_by', $user_delegator->id)
    ->get()
    ->random();

    expect($user_delegator->delegatedUsers)->toHaveCount($delegated_amount)
    ->and($user_delegator->delegator)->toBeNull()
    ->and($user_delegated->delegator->id)->toBe($user_delegator->id)
    ->and($user_delegated->delegatedUsers)->toHaveCount(0);
});

test('hasPermission tells whether or not the user has a certain permission', function () {
    login('foo');

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

    grantPermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeTrue();

    revokePermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

    logout();
});

test('hasAnyPermission tells if the user has one of the given permissions', function () {
    login('foo');

    expect(authenticatedUser()->hasAnyPermission([
        PermissionType::DelegationCreate,
        PermissionType::ServerReport,
        PermissionType::SimulationCreate,
    ]))->toBeFalse();

    grantPermission(PermissionType::ServerReport->value);

    expect(authenticatedUser()->hasAnyPermission([PermissionType::ServerReport]))->toBeTrue()
    ->and(authenticatedUser()->hasAnyPermission([PermissionType::DelegationCreate, PermissionType::SimulationCreate]))->toBeFalse();

    grantPermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasAnyPermission([PermissionType::ServerReport]))->toBeTrue()
    ->and(authenticatedUser()->hasAnyPermission([PermissionType::SimulationCreate]))->toBeTrue()
    ->and(authenticatedUser()->hasAnyPermission([PermissionType::ServerReport, PermissionType::SimulationCreate]))->toBeTrue()
    ->and(authenticatedUser()->hasAnyPermission([PermissionType::DelegationCreate]))->toBeFalse();

    revokePermission(PermissionType::ServerReport->value);
    revokePermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasAnyPermission([
        PermissionType::DelegationCreate,
        PermissionType::ServerReport,
        PermissionType::SimulationCreate,
    ]))->toBeFalse();

    expect(authenticatedUser()->hasAnyPermission([]))->toBeFalse();

    logout();
});

test('forHumans returns username formatted for display', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});

test('returns users using the defined default sort scope', function () {
    $first = ['name' => 'foo', 'username' => 'bar'];
    $second = ['name' => 'foo', 'username' => 'baz'];
    $third = ['name' => null, 'username' => 'barr'];
    $fourth = ['name' => null, 'username' => 'barz'];

    User::factory()->create($second);
    User::factory()->create($first);
    User::factory()->create($fourth);
    User::factory()->create($third);

    $users = User::defaultOrder()->get();

    expect($users->get(0)->username)->toBe($first['username'])
    ->and($users->get(1)->username)->toBe($second['username'])
    ->and($users->get(2)->username)->toBe($third['username'])
    ->and($users->get(3)->username)->toBe($fourth['username']);
});

test('search, with partial term or not, returns the expected values', function () {
    User::factory()->create(['username' => 'foo', 'name' => 'foo']);
    User::factory()->create(['username' => 'bar', 'name' => 'foo bar']);
    User::factory()->create(['username' => 'foo baz', 'name' => 'foo bar baz']);

    expect(User::search('fo')->get())->toHaveCount(3)
    ->and(User::search('bar')->get())->toHaveCount(2)
    ->and(User::search('az')->get())->toHaveCount(1)
    ->and(User::search('foo bar ba')->get())->toHaveCount(1)
    ->and(User::search('foo baz')->get())->toHaveCount(1);
});

test('revokeDelegation revokes the permission of the user and everyone he delegated by setting the default (ordinary) role for everyone', function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_loren = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_ipsen = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_bar->revokeDelegation();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();
    $user_taz->refresh();
    $user_loren->refresh();
    $user_ipsen->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull()
    ->and($user_loren->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_loren->role_granted_by)->toBe($user_foo->id)
    ->and($user_ipsen->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_ipsen->role_granted_by)->toBe($user_foo->id);
});

test('revokeDelegatedUsers removes delegations made by the user', function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_foo->revokeDelegatedUsers();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull();
});

test("updateAndRevokeDelegatedUsers updates the role and removes the user's delegations and the ones he made", function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_bar->role_id = Role::ADMINISTRATOR;
    $user_bar->updateAndRevokeDelegatedUsers();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();
    $user_taz->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull();
});

test('one user has many prints', function () {
    User::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $user = User::with('prints')->first();

    expect($user->prints)->toHaveCount(3);
});

test('isSuperAdmin correctly identifies a superadmin', function () {
    $this->seed(ConfigurationSeeder::class);

    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->isSuperAdmin())->toBeFalse();

    logout();

    $user_foo = login('dumb user');
    $user_foo->refresh();

    expect($user_foo->isSuperAdmin())->toBeTrue();
});

test('without configuration set, isSuperAdmin returns false for any user', function () {
    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->isSuperAdmin())->toBeFalse();

    logout();

    $user_foo = login('dumb user');
    $user_foo->refresh();

    expect($user_foo->isSuperAdmin())->toBeFalse();
});

test('permissions() returns the id of all user permissions', function () {
    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->permissions())->toBeEmpty();

    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::ServerReport->value);

    expect($user_bar->permissions())->toContain(
        PermissionType::LogViewAny->value,
        PermissionType::ServerReport->value
    );
});
