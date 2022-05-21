<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PermissionPolicy;
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
test('user without permission cannot list permissions', function () {
    expect((new PermissionPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a permission', function () {
    expect((new PermissionPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot update a permission', function () {
    expect((new PermissionPolicy())->update($this->user))->toBeFalse();
});

// Happy path
test('permission listing permissions is cached persisted for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new PermissionPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new PermissionPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::PermissionViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new PermissionPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a permission is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new PermissionPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new PermissionPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::PermissionView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new PermissionPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a permission is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new PermissionPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new PermissionPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::PermissionUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new PermissionPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list permissions', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    expect((new PermissionPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    expect((new PermissionPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can individually update a permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    expect((new PermissionPolicy())->update($this->user))->toBeTrue();
});
