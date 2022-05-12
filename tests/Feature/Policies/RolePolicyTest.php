<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\RolePolicy;
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
test('user without permission cannot list roles', function () {
    expect((new RolePolicy)->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a role', function () {
    expect((new RolePolicy)->view($this->user))->toBeFalse();
});

test('user without permission cannot update a role', function () {
    expect((new RolePolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('roles listing permission is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoleViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a role is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoleView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a role is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoleUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list roles', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    expect((new RolePolicy)->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a role', function () {
    grantPermission(PermissionType::RoleView->value);

    expect((new RolePolicy)->view($this->user))->toBeTrue();
});

test('user with permission can individually update a role', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    expect((new RolePolicy)->update($this->user))->toBeTrue();
});
