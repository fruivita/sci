<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\User;
use App\Policies\SimulationPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode criar uma simulação', function () {
    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('usuário não pode criar, simultaneamente, duas simulações na mesma sessão', function () {
    grantPermission(PermissionType::SimulationCreate->value);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('usuário não pode desfazer uma simulação se ela não existe em sua sessão', function () {
    expect((new SimulationPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permissão de criar uma simulação não é persistida em cache', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    $key = authenticatedUser()->username . PermissionType::SimulationCreate->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(Cache::missing($key))->toBeTrue();

    revokePermission(PermissionType::SimulationCreate->value);

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($this->user))->toBeFalse()
    ->and(Cache::missing($key))->toBeTrue();
});

test('usuário com permissão pode criar uma simulação', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    expect((new SimulationPolicy)->create($this->user))->toBeTrue();
});

test('usuário pode desfazer uma simulação se ela existe em sua sessão', function () {
    session()->put('simulator', 'bar');

    expect((new SimulationPolicy)->delete($this->user))->toBeTrue();
});
