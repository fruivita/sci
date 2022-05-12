<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrintingPolicy;
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
test('user without permission cannot generate general print report', function () {
    expect((new PrintingPolicy)->report($this->user))->toBeFalse();
});

// Happy path
test('permission to generate general print report is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PrintingReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new PrintingPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new PrintingPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::PrintingReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new PrintingPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new PrintingPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can generate general print report', function () {
    grantPermission(PermissionType::PrintingReport->value);

    expect((new PrintingPolicy)->report($this->user))->toBeTrue();
});
