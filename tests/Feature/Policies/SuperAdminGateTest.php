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

    $this->user = login('foo');
    $this->user->refresh();
});

afterEach(function () {
    logout();
});

// Happy path
test('gate de verificação de super admin é persistido em cache por 5 segundos', function () {
    $key = "is-super-admin-{$this->user->username}";

    testTime()->freeze();
    expect($this->user->role->permissions)->toBeEmpty()
    ->and(cache()->missing($key))->toBeTrue()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->addSeconds(5);

    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue();
});

test('super admin possui todos os acessos, mesmo sem nenhuma permissão específica', function () {
    expect($this->user->role->permissions)->toBeEmpty()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue();
});
