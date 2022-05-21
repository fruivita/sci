<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DepartmentPolicy;
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
test('user without permission cannot generate report by department', function () {
    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeFalse();
});

test('user without permission cannot generate report by department (Managerial)', function () {
    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeFalse();
});

test('user without permission cannot generate report by department (Institutional)', function () {
    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeFalse();
});

test('user without any permission cannot generate any reports by department', function () {
    expect((new DepartmentPolicy())->reportAny($this->user))->toBeFalse();
});

// Happy path
test('permission to generate the report by department is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DepartmentReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::DepartmentReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to generate the report by department (Managerial) is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ManagerialReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ManagerialReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to generate the report by department (Institutional) is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::InstitutionalReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can generate report by department', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    expect((new DepartmentPolicy())->departmentReport($this->user))->toBeTrue();
});

test('user with permission can generate the report by department (Managerial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    expect((new DepartmentPolicy())->managerialReport($this->user))->toBeTrue();
});

test('user with permission can generate the report by department (Institutional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    expect((new DepartmentPolicy())->institutionalReport($this->user))->toBeTrue();
});

test('permission to generate some report by department is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new DepartmentPolicy())->reportAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new DepartmentPolicy())->reportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::InstitutionalReport->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new DepartmentPolicy())->reportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy())->reportAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user has any of the permissions to generate the report by department', function ($permssion) {
    grantPermission($permssion);

    expect((new DepartmentPolicy())->reportAny($this->user))->toBeTrue();

    revokePermission($permssion);
    $this->travel(6)->seconds();

    expect((new DepartmentPolicy())->reportAny($this->user))->toBeFalse();
})->with([
    PermissionType::DepartmentReport->value,
    PermissionType::ManagerialReport->value,
    PermissionType::InstitutionalReport->value,
]);
