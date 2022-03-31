<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\User;
use App\Policies\SimulationPolicy;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
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
    grantPermission(User::SIMULATION_CREATE);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('usuário não pode desfazer uma simulação se ela não existe em sua sessão', function () {
    expect((new SimulationPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permissão de criar uma simulação não é persistida em cache', function () {
    grantPermission(User::SIMULATION_CREATE);

    $key = authenticatedUser()->username . User::SIMULATION_CREATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(Cache::missing($key))->toBeTrue();

    revokePermission(User::SIMULATION_CREATE);

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($this->user))->toBeFalse()
    ->and(Cache::missing($key))->toBeTrue();
});

test('usuário com permissão pode criar uma simulação', function () {
    grantPermission(User::SIMULATION_CREATE);

    expect((new SimulationPolicy)->create($this->user))->toBeTrue();
});

test('usuário pode desfazer uma simulação se ela existe em sua sessão', function () {
    session()->put('simulator', 'bar');

    expect((new SimulationPolicy)->delete($this->user))->toBeTrue();
});
