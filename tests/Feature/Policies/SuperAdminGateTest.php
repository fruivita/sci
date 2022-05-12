<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permission;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([ConfigurationSeeder::class, DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('dumb user');
    $this->user->refresh();
});

afterEach(function () {
    logout();
});

// Happy path
test('super admin check gate is persisted in cache for 5 seconds', function () {
    testTime()->freeze();
    $key = "{$this->user->username}-is-super-admin";

    // no cache
    expect($this->user->role->permissions)->toBeEmpty()
    ->and(cache()->missing($key))->toBeTrue()

    // created the cache
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    // move time to expiration limit
    testTime()->addSeconds(5);

    // permission is still cached
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue();
});

test('super admin has all access even without any specific permission', function () {
    expect($this->user->role->permissions)->toBeEmpty()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue();
});
