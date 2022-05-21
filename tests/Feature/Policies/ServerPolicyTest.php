<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ServerPolicy;
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
test('user without permission cannot list servers', function () {
    expect((new ServerPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a server', function () {
    expect((new ServerPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot update a server', function () {
    expect((new ServerPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot generate report per server', function () {
    expect((new ServerPolicy())->report($this->user))->toBeFalse();
});

// Happy path
test('server listing permission is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ServerViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ServerPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ServerPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ServerViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ServerPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ServerPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a server is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ServerView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ServerPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ServerPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ServerView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ServerPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ServerPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a server is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ServerUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ServerPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ServerPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ServerUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ServerPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ServerPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to generate the report per server is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ServerReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ServerPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ServerPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ServerReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ServerPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ServerPolicy())->report($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list servers', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    expect((new ServerPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a server', function () {
    grantPermission(PermissionType::ServerView->value);

    expect((new ServerPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can individually update a server', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    expect((new ServerPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can generate report by server', function () {
    grantPermission(PermissionType::ServerReport->value);

    expect((new ServerPolicy())->report($this->user))->toBeTrue();
});
