<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\LogPolicy;
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
test('user without permission cannot list application logs', function () {
    expect((new LogPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot delete application logs', function () {
    expect((new LogPolicy())->delete($this->user))->toBeFalse();
});

test('user without permission cannot download application logs', function () {
    expect((new LogPolicy())->download($this->user))->toBeFalse();
});

// Happy path
test('application log listing permission is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::LogViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new LogPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new LogPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::LogViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new LogPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new LogPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete application logs is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::LogDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new LogPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new LogPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::LogDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new LogPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new LogPolicy())->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to download individual application logs is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::LogDownload->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new LogPolicy())->download($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new LogPolicy())->download($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::LogDownload->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new LogPolicy())->download($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new LogPolicy())->download($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list application logs', function () {
    grantPermission(PermissionType::LogViewAny->value);

    expect((new LogPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually delete an application log', function () {
    grantPermission(PermissionType::LogDelete->value);

    expect((new LogPolicy())->delete($this->user))->toBeTrue();
});

test('user with permission can download individual application log', function () {
    grantPermission(PermissionType::LogDownload->value);

    expect((new LogPolicy())->download($this->user))->toBeTrue();
});
