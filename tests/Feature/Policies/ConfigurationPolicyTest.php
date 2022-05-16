<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ConfigurationPolicy;
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
test('user without permission cannot individually view a configuration', function () {
    expect((new ConfigurationPolicy)->view($this->user))->toBeFalse();
});

test('user without permission cannot update a configuration', function () {
    expect((new ConfigurationPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permission to individually view a configuration is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ConfigurationView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ConfigurationView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ConfigurationPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a configuration is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ConfigurationUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ConfigurationUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ConfigurationPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can individually view a configuration', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue();
});

test('user with permission can individually update a configuration', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue();
});
