<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SimulationPolicy;
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
test('user without permission cannot create a simulation', function () {
    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('user cannot simultaneously create two simulations in the same session', function () {
    grantPermission(PermissionType::SimulationCreate->value);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('user cannot undo a simulation if it does not exist in their session', function () {
    expect((new SimulationPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permission to create a simulation is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SimulationCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SimulationCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SimulationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can create a simulation', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    expect((new SimulationPolicy)->create($this->user))->toBeTrue();
});

test('user can undo a simulation if it exists in their session', function () {
    session()->put('simulator', 'bar');

    expect((new SimulationPolicy)->delete($this->user))->toBeTrue();
});
