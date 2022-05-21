<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrinterPolicy;
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
test('user without permission cannot generate report by printer', function () {
    expect((new PrinterPolicy())->report($this->user))->toBeFalse();
});

// Happy path
test('permission to generate report per printer is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PrinterReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new PrinterPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new PrinterPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::PrinterReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new PrinterPolicy())->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new PrinterPolicy())->report($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can generate report by printer', function () {
    grantPermission(PermissionType::PrinterReport->value);

    expect((new PrinterPolicy())->report($this->user))->toBeTrue();
});
