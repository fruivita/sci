<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DocumentationPolicy;
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
test('user without permission cannot list documentation records', function () {
    expect((new DocumentationPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot create documentation record', function () {
    expect((new DocumentationPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a documentation record', function () {
    expect((new DocumentationPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a documentation record', function () {
    expect((new DocumentationPolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('permission to list documentation records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DocumentationPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DocumentationPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::DocumentationViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DocumentationPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a documentation record is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DocumentationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DocumentationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::DocumentationCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DocumentationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a documentation record is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DocumentationPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DocumentationPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::DocumentationUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DocumentationPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a documentation record is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DocumentationPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DocumentationPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::DocumentationDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DocumentationPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy())->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list documentation records', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    expect((new DocumentationPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can create documentation record', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    expect((new DocumentationPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a documentation record', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    expect((new DocumentationPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a documentation record', function () {
    grantPermission(PermissionType::DocumentationDelete->value);

    expect((new DocumentationPolicy())->delete($this->user))->toBeTrue();
});
