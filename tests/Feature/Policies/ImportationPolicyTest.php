<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ImportationPolicy;
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
test('user without permission cannot perform an import', function () {
    expect((new ImportationPolicy())->create($this->user))->toBeFalse();
});

// Happy path
test('permission to perform an import is cached persisted', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ImportationCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ImportationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ImportationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ImportationCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ImportationPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ImportationPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can perform an import', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy())->create($this->user))->toBeTrue();
});
