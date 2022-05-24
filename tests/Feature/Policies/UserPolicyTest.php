<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('user without permission cannot list users', function () {
    expect((new UserPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot update a user', function () {
    expect((new UserPolicy())->update($this->user))->toBeFalse();
});

test("user cannot update user's role of higher level", function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::UserUpdate->value);

    $user_bar = User::factory()->create([
        'role_id' => Role::ADMINISTRATOR,
    ]);

    expect((new UserPolicy())->update($this->user, $user_bar))->toBeFalse();
});

// Happy path
test('users listing permission is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::UserViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new UserPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new UserPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::UserViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new UserPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new UserPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a user is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::UserUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new UserPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new UserPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::UserUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new UserPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new UserPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list users', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect((new UserPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually update a user', function () {
    grantPermission(PermissionType::UserUpdate->value);

    expect((new UserPolicy())->update($this->user))->toBeTrue();
});

test("user can update user's role of the same level", function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::UserUpdate->value);

    $user_bar = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    expect((new UserPolicy())->update($this->user, $user_bar))->toBeTrue();
});

test("user can update user's role of the lower level", function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::UserUpdate->value);

    $user_bar = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
    ]);

    expect((new UserPolicy())->update($this->user, $user_bar))->toBeTrue();
});
