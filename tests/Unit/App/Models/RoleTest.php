<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('throws exception when trying to create roles in duplicate, that is, with equal ids', function () {
    expect(
        fn () => Role::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Role::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create role with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Role::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(51),         'Data too long for column'], // maximum 50 characters
    ['name', null,                    'cannot be null'],           // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 50 characters
]);

// Failures
test('atomic method Save With Permissions rollback on role update failure', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => $role_name,
        'description' => $role_description,
    ]);

    $role->name = 'new foo';
    $role->description = 'new bar';

    // relationship with non-existent permissions
    $saved = $role->atomicSaveWithPermissions([1, 2]);

    $role->refresh()->load('permissions');

    expect($saved)->toBeFalse()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions)->toBeEmpty();
});

test('atomic method Save With Permissions creates log home of failed role update', function () {
    Log::spy();

    $role = Role::factory()->create();

    // relationship with non-existent permissions
    $role->atomicSaveWithPermissions([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(fn ($message) => $message === __('Role update failed'))
    ->once();
});

// Happy path
test('role ids are set', function () {
    expect(Role::ADMINISTRATOR)->toBe(1000)
    ->and(Role::INSTITUTIONALMANAGER)->toBe(1100)
    ->and(Role::DEPARTMENTMANAGER)->toBe(1200)
    ->and(Role::ORDINARY)->toBe(1300);
});

test('create many roles', function () {
    Role::factory(30)->create();

    expect(Role::count())->toBe(30);
});

test('optional role fields are accepted', function () {
    Role::factory()->create(['description' => null]);

    expect(Role::count())->toBe(1);
});

test('role fields at their maximum size are accepted', function () {
    Role::factory()->create([
        'name' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Role::count())->toBe(1);
});

test('one role has many permissions', function () {
    Role::factory()
    ->has(Permission::factory(3), 'permissions')
    ->create();

    $role = Role::with('permissions')->first();

    expect($role->permissions)->toHaveCount(3);
});

test('one role has many users', function () {
    Role::factory()
    ->has(User::factory(3), 'users')
    ->create();

    $role = Role::with('users')->first();

    expect($role->users)->toHaveCount(3);
});

test('atomicSaveWithPermissions method saves the new attributes and creates a relationship with the given permissions', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => 'baz',
        'description' => 'foo bar baz',
    ]);

    Permission::factory()->create(['id' => 1]);
    Permission::factory()->create(['id' => 2]);
    Permission::factory()->create(['id' => 3]);

    $role->name = $role_name;
    $role->description = $role_description;

    $saved = $role->atomicSaveWithPermissions([1, 3]);
    $role->refresh()->load('permissions');

    expect($saved)->toBeTrue()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions->modelKeys())->toBe([1, 3]);
});

test('admin role has all permissions', function ($permission) {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
        PermissionRoleSeeder::class,
    ]);

    $user = User::factory()->create(['role_id' => Role::ADMINISTRATOR]);

    expect($user->hasPermission($permission))->toBeTrue();
})->with([
    PermissionType::RoleViewAny,
    PermissionType::RoleView,
    PermissionType::RoleUpdate,
    PermissionType::PermissionViewAny,
    PermissionType::PermissionView,
    PermissionType::PermissionUpdate,
    PermissionType::UserViewAny,
    PermissionType::UserUpdate,
    PermissionType::SimulationCreate,
    PermissionType::ImportationCreate,
    PermissionType::DelegationViewAny,
    PermissionType::DelegationCreate,
    PermissionType::PrinterReport,
    PermissionType::PrintingReport,
    PermissionType::ServerViewAny,
    PermissionType::ServerView,
    PermissionType::ServerUpdate,
    PermissionType::ServerReport,
    PermissionType::DepartmentReport,
    PermissionType::ManagerialReport,
    PermissionType::InstitutionalReport,
    PermissionType::SiteViewAny,
    PermissionType::SiteView,
    PermissionType::SiteCreate,
    PermissionType::SiteUpdate,
    PermissionType::SiteDelete,
    PermissionType::ConfigurationView,
    PermissionType::ConfigurationUpdate,
    PermissionType::LogViewAny,
    PermissionType::LogDelete,
    PermissionType::LogDownload,
    PermissionType::DocumentationViewAny,
    PermissionType::DocumentationCreate,
    PermissionType::DocumentationUpdate,
    PermissionType::DocumentationDelete,
]);

test('initial institutional manager role permissions are set', function ($permission) {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
        PermissionRoleSeeder::class,
    ]);

    $user = User::factory()->create(['role_id' => Role::INSTITUTIONALMANAGER]);

    expect($user->hasPermission($permission))->toBeTrue();
})->with([
    PermissionType::DelegationViewAny,
    PermissionType::DelegationCreate,
    PermissionType::PrinterReport,
    PermissionType::PrintingReport,
    PermissionType::ServerReport,
    PermissionType::DepartmentReport,
    PermissionType::ManagerialReport,
    PermissionType::InstitutionalReport,
]);

test('initial department role permissions are set', function ($permission) {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
        PermissionRoleSeeder::class,
    ]);

    $user = User::factory()->create(['role_id' => Role::INSTITUTIONALMANAGER]);

    expect($user->hasPermission($permission))->toBeTrue();
})->with([
    PermissionType::DelegationViewAny,
    PermissionType::DelegationCreate,
    PermissionType::PrinterReport,
    PermissionType::PrintingReport,
    PermissionType::ServerReport,
    PermissionType::DepartmentReport,
    PermissionType::ManagerialReport,
]);

test('initial ordinary role permissions are set', function ($permission) {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
        PermissionRoleSeeder::class,
    ]);

    $user = User::factory()->create(['role_id' => Role::INSTITUTIONALMANAGER]);

    expect($user->hasPermission($permission))->toBeTrue();
})->with([
    PermissionType::PrintingReport,
    PermissionType::ServerReport,
]);

test('previous returns the correct previous record, even if it is the first', function () {
    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);

    expect($role_2->previous()->first()->id)->toBe($role_1->id)
    ->and($role_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);

    expect($role_1->next()->first()->id)->toBe($role_2->id)
    ->and($role_2->next()->first())->toBeNull();
});

test('returns roles using the defined default sort scope', function () {
    $first = 1;
    $second = 2;
    $third = 3;

    Role::factory()->create(['id' => $third]);
    Role::factory()->create(['id' => $first]);
    Role::factory()->create(['id' => $second]);

    $roles = Role::defaultOrder()->get();

    expect($roles->get(0)->id)->toBe($first)
    ->and($roles->get(1)->id)->toBe($second)
    ->and($roles->get(2)->id)->toBe($third);
});

test('roles are in the correct hierarchical order', function () {
    // role with lower id has higher functional hierarchy in the application
    expect(Role::ADMINISTRATOR)->toBeLessThan(Role::INSTITUTIONALMANAGER)
    ->and(Role::INSTITUTIONALMANAGER)->toBeLessThan(Role::DEPARTMENTMANAGER)
    ->and(Role::DEPARTMENTMANAGER)->toBeLessThan(Role::ORDINARY);
});
